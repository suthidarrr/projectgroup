<?php
// ✅ 1. เพิ่ม Header เพื่ออนุญาตให้ Vue ติดต่อกับ PHP ได้ (แก้ปัญหา Failed to fetch)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");
header("Content-Type: application/json; charset=UTF-8");

include 'condb.php';

$data = json_decode(file_get_contents("php://input"), true);

if (
    empty($data['cfull_name']) ||
    empty($data['phone']) ||
    empty($data['email']) ||
    empty($data['c_username']) ||
    empty($data['c_password'])
) {
    echo json_encode([
        "success" => false,
        "message" => "กรุณากรอกข้อมูลให้ครบถ้วน"
    ]);
    exit;
}

try {
    // ✅ 2. ตรวจสอบชื่อตารางใน DB ของคุณ (ในที่นี้เปลี่ยนจาก cutomers เป็น customers)
    // ⚠️ ถ้าใน DB คุณสะกดว่า cutomers จริงๆ ให้เปลี่ยนกลับเป็นแบบเดิมนะคะ
    $tableName = "customers"; 

    // ตรวจสอบ Username ซ้ำ
    $checkUser = $conn->prepare("SELECT c_username FROM $tableName WHERE c_username = :u");
    $checkUser->execute([':u' => $data['c_username']]);
    
    if ($checkUser->fetch()) {
        echo json_encode([
            "success" => false,
            "message" => "ชื่อผู้ใช้งานนี้ถูกใช้ไปแล้ว"
        ]);
        exit;
    }

    // เตรียมคำสั่ง SQL
    $sql = "INSERT INTO $tableName 
            (cfull_name, phone, email, c_username, c_password) 
            VALUES 
            (:cfull_name, :phone, :email, :c_username, :c_password)";

    $stmt = $conn->prepare($sql);
    
    // เข้ารหัสผ่านและประมวลผล
    $stmt->execute([
        ':cfull_name' => $data['cfull_name'],
        ':phone'      => $data['phone'],
        ':email'      => $data['email'],
        ':c_username' => $data['c_username'],
        ':c_password' => password_hash($data['c_password'], PASSWORD_DEFAULT)
    ]);

    echo json_encode([
        "success" => true,
        "message" => "ลงทะเบียนเรียบร้อยแล้ว"
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "เกิดข้อผิดพลาดทางเทคนิค: " . $e->getMessage()
    ]);
}
?>