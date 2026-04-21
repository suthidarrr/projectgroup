<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include 'condb.php';

try {
    // ✅ SQL ใหม่: คำนวณหาที่ว่างที่เหลือจริง (Total Seat - Sum of num_people)
    $sql = "SELECT 
                a.*, 
                c.category_name,
                -- คำนวณหาที่ว่างที่เหลือ: ถ้าไม่มีคนจองเลยให้ถือเป็น 0 (IFNULL)
                (a.seat - IFNULL((SELECT SUM(num_people) FROM bookings WHERE att_id = a.att_id), 0)) AS remaining_seats
            FROM attraction a
            LEFT JOIN categories c ON a.category_id = c.category_id
            ORDER BY a.att_id DESC";

    $stmt = $conn->query($sql);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
