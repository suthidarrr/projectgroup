<?php
// ✅ 1. เพิ่ม Header เพื่ออนุญาตให้ Vue ติดต่อกับ PHP ได้ (แก้ปัญหา Connection Error)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");
header("Content-Type: application/json; charset=UTF-8");

include 'condb.php'; 

$data = json_decode(file_get_contents("php://input"), true);

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (!$username || !$password) {
    echo json_encode(["success" => false, "message" => "กรุณากรอกชื่อผู้ใช้และรหัสผ่าน"]);
    exit;
}

try {
    // ✅ 2. ปรับชื่อตารางจาก cutomers เป็น customers (ตามที่ Error ฟ้องว่าหา cutomers ไม่เจอ)
    // ⚠️ ตรวจสอบใน phpMyAdmin อีกครั้งนะคะว่าชื่อตารางสะกดอย่างไร
    $tableName = "customers"; 

    $stmt = $conn->prepare("SELECT * FROM $tableName WHERE c_username = :username");
    $stmt->bindParam(":username", $username);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // ✅ 3. ตรวจสอบรหัสผ่าน (ต้องเป็นรหัสที่ผ่านการ password_hash มาแล้วในฐานข้อมูล)
    if ($user && password_verify($password, $user['c_password'])) {
        echo json_encode([
            "success" => true,
            "message" => "Customer Login Success",
            "user" => [
                "id" => $user['cust_id'],
                "name" => $user['cfull_name'],
                "username" => $user['c_username'],
                "role" => "customer" 
            ]
        ]);
    } else {
        echo json_encode([
            "success" => false, 
            "message" => "ชื่อผู้ใช้หรือรหัสผ่านลูกค้าไม่ถูกต้อง"
        ]);
    }

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "เกิดข้อผิดพลาด: " . $e->getMessage()]);
}
?>