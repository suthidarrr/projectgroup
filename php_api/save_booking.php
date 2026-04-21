<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit; }

include 'condb.php';

try {
    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input) { throw new Exception("ไม่มีข้อมูลส่งมา"); }

    $att_id = $input['att_id'];
    $cust_id = $input['cust_id'];
    $num_people = $input['num_people'];
    $total_price = $input['total_price']; // ✅ เปลี่ยนจาก total_ptice เป็น total_price แล้วค่ะ

    // บันทึกข้อมูลลงตาราง bookings
    $sql = "INSERT INTO bookings (cust_id, att_id, booking_date, num_people, total_price) 
            VALUES (:cust_id, :att_id, NOW(), :num_people, :total_price)";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':cust_id' => $cust_id,
        ':att_id' => $att_id,
        ':num_people' => $num_people,
        ':total_price' => $total_price
    ]);

    // อัปเดตที่นั่งที่เหลือในตาราง attraction
    $updateSql = "UPDATE attraction SET Seat = Seat - :count WHERE att_id = :id";
    $upStmt = $conn->prepare($updateSql);
    $upStmt->execute([':count' => $num_people, ':id' => $att_id]);

    echo json_encode(["success" => true, "message" => "จองสำเร็จแล้วค่ะ"]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>