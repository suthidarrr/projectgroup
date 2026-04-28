<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include 'condb.php';

try {
    
    $sql = "SELECT a.*, c.category_name, 
                   COUNT(b.booking_id) as total_bookings 
            FROM attraction a 
            LEFT JOIN categories c ON a.category_id = c.category_id 
            LEFT JOIN bookings b ON a.att_id = b.att_id
            GROUP BY a.att_id
            ORDER BY total_bookings DESC, a.att_id DESC";
            
    $stmt = $conn->query($sql);
    $datas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(["success" => true, "data" => $datas]);
    
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>