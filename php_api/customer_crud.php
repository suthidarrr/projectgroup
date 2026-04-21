<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit; }

include 'condb.php';

try {
    $method = $_SERVER['REQUEST_METHOD'];

    // ✅ 1. ดึงข้อมูลลูกค้า (GET)
    if ($method === "GET") {
        // ไม่ดึง c_password ออกไปแสดงเพื่อความปลอดภัย
        $stmt = $conn->prepare("SELECT cust_id, cfull_name, phone, email, c_username FROM customers ORDER BY cust_id DESC");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "data" => $result]);
    }

    // ✅ 2. เพิ่มข้อมูลลูกค้า (POST)
    elseif ($method === "POST") {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data["cfull_name"]) || empty($data["c_username"]) || empty($data["c_password"])) {
            echo json_encode(["success" => false, "message" => "กรุณากรอกข้อมูลให้ครบถ้วน"]); exit;
        }

        $password_hashed = password_hash($data["c_password"], PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO customers (cfull_name, phone, email, c_username, c_password) 
                VALUES (:name, :phone, :email, :user, :pass)";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            ":name"  => $data["cfull_name"],
            ":phone"  => $data["phone"],
            ":email" => $data["email"],
            ":user"  => $data["c_username"],
            ":pass"  => $password_hashed
        ]);
        echo json_encode(["success" => $result, "message" => $result ? "เพิ่มข้อมูลลูกค้าสำเร็จ" : "ล้มเหลว"]);
    }

    // ✅ 3. แก้ไขข้อมูลลูกค้า (PUT)
    elseif ($method === "PUT") {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!empty($data["c_password"])) {
            // กรณีเปลี่ยนรหัสผ่านใหม่
            $password_hashed = password_hash($data["c_password"], PASSWORD_DEFAULT);
            $sql = "UPDATE customers SET cfull_name=:n, phone=:p, email=:e, c_username=:u, c_password=:pass WHERE cust_id=:id";
            $params = [
                ":n"    => $data["cfull_name"], ":p"    => $data["phone"], 
                ":e"    => $data["email"],     ":u"    => $data["c_username"], 
                ":pass" => $password_hashed,   ":id"   => $data["cust_id"]
            ];
        } else {
            // กรณีไม่เปลี่ยนรหัสผ่าน
            $sql = "UPDATE customers SET cfull_name=:n, phone=:p, email=:e, c_username=:u WHERE cust_id=:id";
            $params = [
                ":n"  => $data["cfull_name"], ":p"  => $data["phone"], 
                ":e"  => $data["email"],     ":u"  => $data["c_username"], 
                ":id" => $data["cust_id"]
            ];
        }
                
        $stmt = $conn->prepare($sql);
        $res = $stmt->execute($params);
        echo json_encode(["success" => $res, "message" => $res ? "แก้ไขข้อมูลสำเร็จ" : "แก้ไขล้มเหลว"]);
    }

    // ✅ 4. ลบข้อมูล (DELETE)
    elseif ($method === "DELETE") {
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $conn->prepare("DELETE FROM customers WHERE cust_id = :id");
        $res = $stmt->execute([":id" => $data["cust_id"]]);
        echo json_encode(["success" => $res, "message" => $res ? "ลบข้อมูลสำเร็จ" : "ลบล้มเหลว"]);
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>