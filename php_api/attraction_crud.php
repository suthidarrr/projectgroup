<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit; }

include 'condb.php';
$method = $_SERVER['REQUEST_METHOD'];

// ✅ 1. ส่วนการดึงข้อมูล (GET)
if ($method == 'GET') {
    if (isset($_GET['type']) && $_GET['type'] == 'categories') {
        $stmt = $conn->query("SELECT * FROM categories ORDER BY category_name ASC");
        echo json_encode(["success" => true, "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } 
    // 🔍 จุดแก้สำคัญ: ดึงข้อมูลตัวเดียวสำหรับหน้า Detail
    elseif (isset($_GET['id'])) {
        $id = $_GET['id'];
        $sql = "SELECT a.*, c.category_name 
                FROM attraction a 
                LEFT JOIN categories c ON a.category_id = c.category_id 
                WHERE a.att_id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC); // ใช้ fetch() เพื่อเอา Object ตัวเดียว
        
        if ($data) {
            echo json_encode(["success" => true, "data" => $data]);
        } else {
            echo json_encode(["success" => false, "message" => "ไม่พบข้อมูลสถานที่นี้"]);
        }
    }
    else {
        // ดึงข้อมูลทั้งหมดสำหรับหน้า List
        $sql = "SELECT a.*, c.category_name FROM attraction a LEFT JOIN categories c ON a.category_id = c.category_id ORDER BY a.att_id DESC";
        $stmt = $conn->query($sql);
        echo json_encode(["success" => true, "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }
    exit;
}

// ✅ 2. ส่วนการจัดการข้อมูล (POST) - ตรงนี้โค้ดคุณดีอยู่แล้วค่ะ
$action = $_POST['action'] ?? '';
try {
    if ($action == 'add' || $action == 'update') {
        $name = $_POST['att_name']; $cat_id = $_POST['category_id']; $desc = $_POST['description'];
        $price = $_POST['price']; $seat = $_POST['seat']; $image = "";

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image = time() . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $image);
        }

        if ($action == 'add') {
            $sql = "INSERT INTO attraction (att_name, category_id, description, price, seat, image) VALUES (:name, :cat_id, :desc, :price, :seat, :img)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':name'=>$name, ':cat_id'=>$cat_id, ':desc'=>$desc, ':price'=>$price, ':seat'=>$seat, ':img'=>$image]);
            echo json_encode(["success" => true, "message" => "เพิ่มข้อมูลสำเร็จ"]);
        } else {
            $id = $_POST['att_id'];
            $img_sql = ($image != "") ? ", image=:img" : "";
            $sql = "UPDATE attraction SET att_name=:name, category_id=:cat_id, description=:desc, price=:price, seat=:seat $img_sql WHERE att_id=:id";
            $params = [':name'=>$name, ':cat_id'=>$cat_id, ':desc'=>$desc, ':price'=>$price, ':seat'=>$seat, ':id'=>$id];
            if ($image != "") $params[':img'] = $image;
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            echo json_encode(["success" => true, "message" => "แก้ไขข้อมูลสำเร็จ"]);
        }
    }
    if ($action == 'delete') {
        $stmt = $conn->prepare("DELETE FROM attraction WHERE att_id = :id");
        $stmt->execute([':id' => $_POST['att_id']]);
        echo json_encode(["success" => true, "message" => "ลบสำเร็จ"]);
    }
} catch (Exception $e) { echo json_encode(["success" => false, "message" => $e->getMessage()]); }
?>