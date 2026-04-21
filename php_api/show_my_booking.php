<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include 'condb.php';

try {
    if (!isset($_GET['cust_id'])) {
        echo json_encode(["success" => false, "message" => "ไม่พบรหัสลูกค้า"]);
        exit;
    }

    $cust_id = $_GET['cust_id'];

    // SQL ดึงข้อมูลการจอง JOIN กับตารางสถานที่ท่องเที่ยว
    $sql = "SELECT b.*, a.att_name, a.image, a.price as unit_price
            FROM bookings b
            JOIN attraction a ON b.att_id = a.att_id
            WHERE b.cust_id = :cust_id
            ORDER BY b.booking_date DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([':cust_id' => $cust_id]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "data" => $data]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>