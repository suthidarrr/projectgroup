<?php
include 'condb.php';

try {
    $stmt = $conn->query("SELECT * FROM attraction order by att_id DESC lIMIT 3");
    $datas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($datas);
    
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
