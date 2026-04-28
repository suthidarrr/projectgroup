<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit; }

include 'condb.php';
$method = $_SERVER['REQUEST_METHOD'];

try {
    // ✅ 1. ส่วนการดึงข้อมูล (GET)
    if ($method == 'GET') {
        if (isset($_GET['type']) && $_GET['type'] == 'categories') {
            // ดึงหมวดหมู่
            $stmt = $conn->query("SELECT * FROM categories ORDER BY category_name ASC");
            echo json_encode(["success" => true, "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        } 
        elseif (isset($_GET['id'])) {
            // ดึงข้อมูลตัวเดียว (สำหรับหน้า Detail)
            $id = $_GET['id'];
            // 🚩 ใช้ a.Seat as seat เพื่อให้ Vue อ่านค่าได้แน่นอน
            $sql = "SELECT a.*, a.Seat as seat, c.category_name 
                    FROM attraction a 
                    LEFT JOIN categories c ON a.category_id = c.category_id 
                    WHERE a.att_id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($data) {
                echo json_encode(["success" => true, "data" => $data]);
            } else {
                echo json_encode(["success" => false, "message" => "ไม่พบข้อมูลสถานที่นี้"]);
            }
        }
        else {
            // ดึงข้อมูลทั้งหมด (สำหรับหน้า List)
            // 🚩 เพิ่ม a.Seat as seat ป้องกันปัญหาตัวพิมพ์เล็ก-ใหญ่
            $sql = "SELECT a.*, a.Seat as seat, c.category_name 
                    FROM attraction a 
                    LEFT JOIN categories c ON a.category_id = c.category_id 
                    ORDER BY a.att_id DESC";
            $stmt = $conn->query($sql);
            echo json_encode(["success" => true, "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        }
        exit;
    }

    // ✅ 2. ส่วนการจัดการข้อมูล (POST)
    $action = $_POST['action'] ?? '';
    
    if ($action == 'add' || $action == 'update') {
        $name = $_POST['att_name']; 
        $cat_id = $_POST['category_id']; 
        $desc = $_POST['description'];
        $price = $_POST['price']; 
        $seat = $_POST['seat']; 
        $image = "";

        // จัดการเรื่องรูปภาพ
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image = time() . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }
            move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $image);
        }

        if ($action == 'add') {
            // 🚩 เปลี่ยนชื่อคอลัมน์เป็น Seat (S ตัวใหญ่) ตามมาตรฐาน DB ของคุณ
            $sql = "INSERT INTO attraction (att_name, category_id, description, price, Seat, image) 
                    VALUES (:name, :cat_id, :desc, :price, :seat, :img)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':name'=>$name, ':cat_id'=>$cat_id, ':desc'=>$desc, ':price'=>$price, ':seat'=>$seat, ':img'=>$image]);
            echo json_encode(["success" => true, "message" => "เพิ่มข้อมูลสำเร็จแล้วค่ะ"]);
        } else {
            $id = $_POST['att_id'];
            $img_sql = ($image != "") ? ", image=:img" : "";
            // 🚩 เปลี่ยน seat เป็น Seat
            $sql = "UPDATE attraction SET att_name=:name, category_id=:cat_id, description=:desc, price=:price, Seat=:seat $img_sql WHERE att_id=:id";
            $params = [':name'=>$name, ':cat_id'=>$cat_id, ':desc'=>$desc, ':price'=>$price, ':seat'=>$seat, ':id'=>$id];
            if ($image != "") $params[':img'] = $image;
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            echo json_encode(["success" => true, "message" => "แก้ไขข้อมูลเรียบร้อยแล้ว"]);
        }
    }
    
    if ($action == 'delete') {
        $stmt = $conn->prepare("DELETE FROM attraction WHERE att_id = :id");
        $stmt->execute([':id' => $_POST['att_id']]);
        echo json_encode(["success" => true, "message" => "ลบข้อมูลสำเร็จ"]);
    }

} catch (Exception $e) { 
    echo json_encode(["success" => false, "message" => "SQL Error: " . $e->getMessage()]); 
}
?>