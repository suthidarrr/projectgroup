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
    $total_price = $input['total_price'];
    $travel_date = $input['travel_date']; // รับค่าวันเดินทาง

    // ✅ เพิ่ม travel_date ลงในคำสั่ง SQL (ตามตารางในฐานข้อมูลของคุณ)
    $sql = "INSERT INTO bookings (cust_id, att_id, booking_date, travel_date, num_people, total_price) 
            VALUES (:cust_id, :att_id, NOW(), :travel_date, :num_people, :total_price)";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':cust_id' => $cust_id,
        ':att_id' => $att_id,
        ':travel_date' => $travel_date,
        ':num_people' => $num_people,
        ':total_price' => $total_price
    ]);

    // อัปเดตจำนวนที่นั่งที่เหลือ
    $updateSql = "UPDATE attraction SET Seat = Seat - :count WHERE att_id = :id";
    $upStmt = $conn->prepare($updateSql);
    $upStmt->execute([':count' => $num_people, ':id' => $att_id]);

    echo json_encode(["success" => true, "message" => "จองสำเร็จแล้วค่ะ"]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>