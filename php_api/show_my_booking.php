<?php
/* ✅ ตั้งค่า Header เพื่อรองรับการเรียกใช้งานจาก Vue (CORS) */
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit; }

include 'condb.php';

try {
    /* ✅ 1. ตรวจสอบว่ามีการส่ง cust_id มาทาง Query String หรือไม่ */
    if (!isset($_GET['cust_id']) || empty($_GET['cust_id'])) {
        echo json_encode([
            "success" => false,
            "message" => "กรุณาระบุรหัสลูกค้า (Customer ID)"
        ]);
        exit;
    }

    $cust_id = $_GET['cust_id'];

    /* ✅ 2. Query ข้อมูลจากตาราง bookings  
       โดย JOIN กับตาราง attraction เพื่อเอาชื่อ (att_name) และรูปภาพ (image) */
    $sql = "SELECT b.booking_id, b.booking_date, b.travel_date, b.num_people, b.total_price, 
                   a.att_name, a.image 
            FROM bookings b
            JOIN attraction a ON b.att_id = a.att_id
            WHERE b.cust_id = :cust_id
            ORDER BY b.booking_date DESC"; // เรียงจากรายการล่าสุดขึ้นก่อน

    $stmt = $conn->prepare($sql);
    $stmt->execute([':cust_id' => $cust_id]);
    
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* ✅ 3. ส่งข้อมูลกลับไปยัง Vue */
    echo json_encode([
        "success" => true,
        "data" => $bookings
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "เกิดข้อผิดพลาดทางฐานข้อมูล",
        "error" => $e->getMessage()
    ]);
}
?>