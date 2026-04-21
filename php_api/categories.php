<?php
// 1. ตั้งค่า Headers เพื่อรองรับการเชื่อมต่อจาก Vue
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// รองรับ Preflight Request (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

include 'condb.php';

$method = $_SERVER['REQUEST_METHOD'];
// รับข้อมูล JSON จากหน้าบ้าน
$data = json_decode(file_get_contents("php://input"), true);

try {
    switch($method) {
        case 'GET':
            // ✅ ดึงข้อมูลทั้งหมด เรียงจากใหม่ไปเก่า
            $stmt = $conn->query("SELECT * FROM categories ORDER BY category_id DESC");
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(["success" => true, "data" => $result]);
            break;

        case 'POST':
            // ✅ ตรวจสอบว่ามีข้อมูลส่งมาไหม
            if (empty($data['category_name'])) {
                echo json_encode(["success" => false, "message" => "กรุณากรอกชื่อหมวดหมู่"]);
                exit;
            }
            $sql = "INSERT INTO categories (category_name) VALUES (:name)";
            $stmt = $conn->prepare($sql);
            if($stmt->execute([':name' => $data['category_name']])) {
                echo json_encode(["success" => true, "message" => "เพิ่มหมวดหมู่สำเร็จ"]);
            } else {
                echo json_encode(["success" => false, "message" => "ไม่สามารถเพิ่มข้อมูลได้"]);
            }
            break;

        case 'PUT':
            // ✅ ตรวจสอบข้อมูลก่อนแก้ไข
            if (empty($data['category_id']) || empty($data['category_name'])) {
                echo json_encode(["success" => false, "message" => "ข้อมูลไม่ครบถ้วน"]);
                exit;
            }
            $sql = "UPDATE categories SET category_name = :name WHERE category_id = :id";
            $stmt = $conn->prepare($sql);
            if($stmt->execute([':name' => $data['category_name'], ':id' => $data['category_id']])) {
                echo json_encode(["success" => true, "message" => "แก้ไขข้อมูลสำเร็จ"]);
            } else {
                echo json_encode(["success" => false, "message" => "ไม่สามารถแก้ไขข้อมูลได้"]);
            }
            break;

        case 'DELETE':
            // ✅ ตรวจสอบว่าส่ง ID มาลบไหม
            if (empty($data['category_id'])) {
                echo json_encode(["success" => false, "message" => "ไม่พบรหัสที่ต้องการลบ"]);
                exit;
            }
            $sql = "DELETE FROM categories WHERE category_id = :id";
            $stmt = $conn->prepare($sql);
            if($stmt->execute([':id' => $data['category_id']])) {
                echo json_encode(["success" => true, "message" => "ลบข้อมูลสำเร็จ"]);
            } else {
                echo json_encode(["success" => false, "message" => "ไม่สามารถลบข้อมูลได้"]);
            }
            break;

        default:
            echo json_encode(["success" => false, "message" => "Method not allowed"]);
            break;
    }
} catch (PDOException $e) {
    // ✅ ถ้าเกิด Error ที่ฐานข้อมูล จะส่งกลับเป็น JSON แทน HTML Error
    echo json_encode([
        "success" => false, 
        "message" => "Database Error: " . $e->getMessage()
    ]);
}
?>