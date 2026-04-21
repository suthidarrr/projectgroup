<?php
include 'condb.php'; 

$data = json_decode(file_get_contents("php://input"), true);

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (!$username || !$password) {
    echo json_encode(["success" => false, "message" => "กรอกข้อมูลไม่ครบ"]);
    exit;
}

try {
    // ✅ ค้นหาจากตาราง employee
    $stmt = $conn->prepare("SELECT * FROM employee WHERE e_username = :username");
    $stmt->bindParam(":username", $username);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['e_password'])) {
        echo json_encode([
            "success" => true,
            "message" => "Staff Login Success",
            "user" => [
                "id" => $user['emp_id'],
                "name" => $user['efull_name'],
                "username" => $user['e_username'],
                "role" => "employee" // ✅ ส่ง role ไปให้ Vue เช็คสิทธิ์
            ]
        ]);
    } else {
        echo json_encode([
            "success" => false, 
            "message" => "ชื่อผู้ใช้หรือรหัสผ่านพนักงานไม่ถูกต้อง"
        ]);
    }

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>