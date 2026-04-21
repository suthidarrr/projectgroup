<?php
include 'condb.php';

$data = json_decode(file_get_contents("php://input"), true);

// ✅ ตรวจสอบข้อมูลให้ครบตามโครงสร้างใหม่
if (
    !isset($data['efull_name']) ||
    !isset($data['department']) ||
    !isset($data['salary']) ||
    !isset($data['e_username']) ||
    !isset($data['e_password'])
) {
    echo json_encode([
        "success" => false,
        "message" => "ข้อมูลไม่ครบถ้วน (กรุณาระบุชื่อ, แผนก, เงินเดือน, Username และ Password)"
    ]);
    exit;
}

try {
    // ✅ 1. เข้ารหัสผ่านก่อนบันทึก (Security Standard)
    $hashed_password = password_hash($data['e_password'], PASSWORD_DEFAULT);

    // ✅ 2. ปรับ SQL ให้ตรงกับชื่อคอลัมน์ในฐานข้อมูลจริง
    $sql = "INSERT INTO employee 
            (efull_name, department, salary, active, e_username, e_password) 
            VALUES 
            (:efull_name, :department, :salary, :active, :e_username, :e_password)";

    $stmt = $conn->prepare($sql);
    
    // ✅ 3. ผูกค่าตัวแปร (Bind Values)
    $stmt->execute([
        ':efull_name'  => $data['efull_name'],
        ':department'  => $data['department'],
        ':salary'      => $data['salary'],
        ':active'      => $data['active'] ?? 1, // ถ้าไม่ส่งมา ให้เป็น 1 (Active)
        ':e_username'  => $data['e_username'],
        ':e_password'  => $hashed_password      // ใช้รหัสที่เข้ารหัสแล้ว
    ]);

    echo json_encode([
        "success" => true,
        "message" => "เพิ่มข้อมูลพนักงานเรียบร้อยแล้ว"
    ]);

} catch (PDOException $e) {
    // กรณี Username ซ้ำ หรือ Error อื่นๆ
    $msg = $e->getMessage();
    if (str_contains($msg, 'Duplicate entry')) {
        $msg = "ชื่อผู้ใช้งานนี้มีอยู่ในระบบแล้ว";
    }
    
    echo json_encode([
        "success" => false,
        "message" => "เกิดข้อผิดพลาด: " . $msg
    ]);
}
?>