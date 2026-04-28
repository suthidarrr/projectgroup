<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit; }

include 'condb.php';

$action = $_POST['action'] ?? $_GET['action'] ?? 'fetch';

try {
    if ($action == 'fetch') {
        $sql = "SELECT b.booking_id, b.booking_date, b.travel_date, b.num_people, b.total_price, 
                       c.cfull_name as cust_name, a.att_name, a.price as unit_price 
                FROM bookings b
                LEFT JOIN customers c ON b.cust_id = c.cust_id 
                LEFT JOIN attraction a ON b.att_id = a.att_id
                ORDER BY b.booking_id DESC";
                
        $stmt = $conn->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // ส่งข้อมูลออกไป (ถ้าไม่มีข้อมูล data จะเป็น [] แต่ success จะเป็น true)
        echo json_encode(["success" => true, "data" => $data]);

    } elseif ($action == 'update') {
        $booking_id = $_POST['booking_id'];
        $num_people = $_POST['num_people'];
        $travel_date = $_POST['travel_date'];
        $total_price = $_POST['total_price'];

        $stmt = $conn->prepare("SELECT att_id, num_people FROM bookings WHERE booking_id = ?");
        $stmt->execute([$booking_id]);
        $old_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($old_data) {
            $diff = $num_people - $old_data['num_people'];
            // อัปเดต Seat ในตาราง attraction
            $upSeat = $conn->prepare("UPDATE attraction SET Seat = Seat - ? WHERE att_id = ?");
            $upSeat->execute([$diff, $old_data['att_id']]);

            $sql = "UPDATE bookings SET num_people = ?, travel_date = ?, total_price = ? WHERE booking_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$num_people, $travel_date, $total_price, $booking_id]);
            
            echo json_encode(["success" => true, "message" => "แก้ไขเรียบร้อย"]);
        }
    } elseif ($action == 'delete') {
        $booking_id = $_POST['booking_id'];
        $stmt = $conn->prepare("SELECT att_id, num_people FROM bookings WHERE booking_id = ?");
        $stmt->execute([$booking_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $upSeat = $conn->prepare("UPDATE attraction SET Seat = Seat + ? WHERE att_id = ?");
            $upSeat->execute([$data['num_people'], $data['att_id']]);
            $stmt = $conn->prepare("DELETE FROM bookings WHERE booking_id = ?");
            $stmt->execute([$booking_id]);
            echo json_encode(["success" => true, "message" => "ลบสำเร็จ"]);
        }
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>