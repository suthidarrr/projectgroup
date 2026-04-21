<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit; }

include 'condb.php';

try {
    $input = json_decode(file_get_contents("php://input"), true);
    $booking_id = $input['booking_id'];

    if (!$booking_id) { throw new Exception("ไม่พบรหัสการจอง"); }

    $conn->beginTransaction();

    // 1. ดึงข้อมูลก่อนลบเพื่อดูว่าจองไปกี่คน (จะเอาที่นั่งไปคืนตาราง attraction)
    $stmt = $conn->prepare("SELECT att_id, num_people FROM bookings WHERE booking_id = :id");
    $stmt->execute([':id' => $booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($booking) {
        // 2. คืนที่นั่งว่างกลับไปในตาราง attraction (Seat = Seat + จำนวนที่ลบ)
        $upSeat = $conn->prepare("UPDATE attraction SET Seat = Seat + :num WHERE att_id = :att_id");
        $upSeat->execute([
            ':num' => $booking['num_people'],
            ':att_id' => $booking['att_id']
        ]);

        // 3. ลบแถวข้อมูลออกจากตาราง bookings ทันที
        $delStmt = $conn->prepare("DELETE FROM bookings WHERE booking_id = :id");
        $delStmt->execute([':id' => $booking_id]);
    }

    $conn->commit();
    echo json_encode(["success" => true, "message" => "ลบรายการจองเรียบร้อย"]);

} catch (Exception $e) {
    if ($conn->inTransaction()) { $conn->rollBack(); }
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>