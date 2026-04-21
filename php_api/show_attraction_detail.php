<?php
/* ✅ ตั้งค่า Header สำหรับ API */
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit; }

include 'condb.php';

try {
    /* ✅ 1. ตรวจสอบว่ามีการส่ง id มาหรือไม่ */
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo json_encode([
            "success" => false,
            "message" => "ไม่พบ Attraction ID"
        ]);
        exit;
    }

    /* ✅ 2. รับค่า id */
    $id = $_GET['id'];  

    /* ✅ 3. Query ข้อมูลสถานที่ท่องเที่ยว (JOIN กับหมวดหมู่เพื่อเอาชื่อประเภทมาโชว์) */
    $sql = "SELECT a.*, c.category_name 
            FROM attraction a 
            LEFT JOIN categories c ON a.category_id = c.category_id 
            WHERE a.att_id = :id";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id]);

    $attraction = $stmt->fetch(PDO::FETCH_ASSOC);

    /* ✅ 4. ตรวจสอบว่าพบข้อมูลหรือไม่ */
    if (!$attraction) {
        echo json_encode([
            "success" => false,
            "message" => "ไม่พบข้อมูลสถานที่ท่องเที่ยวนี้"
        ]);
        exit;
    }

    /* ✅ 5. ส่งข้อมูลกลับ (ส่งกลับไปเป็นก้อน Object ตัวเดียว) */
    echo json_encode([
        "success" => true,
        "data" => $attraction
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database Error",
        "error" => $e->getMessage() 
    ]);
}
?>