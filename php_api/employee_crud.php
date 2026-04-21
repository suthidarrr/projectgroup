<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit; }

include 'condb.php';

try {
    $method = $_SERVER['REQUEST_METHOD'];

    // ✅ 1. ดึงข้อมูล (GET) - ดึง e_username มาโชว์
    if ($method === "GET") {
        $stmt = $conn->prepare("SELECT emp_id, efull_name, department, salary, e_username, active FROM employee ORDER BY emp_id DESC");
        $stmt->execute();
        echo json_encode(["success" => true, "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    // ✅ 2. เพิ่มพนักงาน (POST) - ใช้ e_username และ e_password
    elseif ($method === "POST") {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data["efull_name"]) || empty($data["e_username"]) || empty($data["e_password"])) {
            echo json_encode(["success" => false, "message" => "กรุณากรอก Username และ Password ให้ครบ"]); exit;
        }

        $password_hashed = password_hash($data["e_password"], PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO employee (efull_name, department, salary, e_username, e_password, active) 
                VALUES (:name, :dept, :salary, :user, :pass, :active)";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            ":name"   => $data["efull_name"],
            ":dept"   => $data["department"],
            ":salary" => $data["salary"],
            ":user"   => $data["e_username"],
            ":pass"   => $password_hashed,
            ":active" => isset($data["active"]) ? $data["active"] : 1
        ]);

        echo json_encode(["success" => $result, "message" => $result ? "เพิ่มสำเร็จ" : "ล้มเหลว"]);
    }

    // ✅ 3. แก้ไขข้อมูล (PUT) - ใช้ e_username และ e_password
    elseif ($method === "PUT") {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!empty($data["e_password"])) {
            // กรณีเปลี่ยนรหัสผ่าน
            $password_hashed = password_hash($data["e_password"], PASSWORD_DEFAULT);
            $sql = "UPDATE employee SET efull_name=:name, department=:dept, salary=:salary, e_username=:user, e_password=:pass, active=:active WHERE emp_id=:id";
            $params = [
                ":name" => $data["efull_name"], ":dept" => $data["department"], 
                ":salary" => $data["salary"], ":user" => $data["e_username"], 
                ":pass" => $password_hashed, ":active" => $data["active"], ":id" => $data["emp_id"]
            ];
        } else {
            // กรณีไม่เปลี่ยนรหัสผ่าน
            $sql = "UPDATE employee SET efull_name=:name, department=:dept, salary=:salary, e_username=:user, active=:active WHERE emp_id=:id";
            $params = [
                ":name" => $data["efull_name"], ":dept" => $data["department"], 
                ":salary" => $data["salary"], ":user" => $data["e_username"], 
                ":active" => $data["active"], ":id" => $data["emp_id"]
            ];
        }
                
        $stmt = $conn->prepare($sql);
        $res = $stmt->execute($params);
        echo json_encode(["success" => $res, "message" => $res ? "แก้ไขสำเร็จ" : "ล้มเหลว"]);
    }

    // ✅ 4. ลบข้อมูล (DELETE)
    elseif ($method === "DELETE") {
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $conn->prepare("DELETE FROM employee WHERE emp_id = :id");
        $res = $stmt->execute([":id" => $data["emp_id"]]);
        echo json_encode(["success" => $res, "message" => $res ? "ลบสำเร็จ" : "ล้มเหลว"]);
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>