<?php
header("Content-Type: application/json");
require 'db_config.php'; // استدعاء ملف الربط

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['name']) && !empty($data['email']) && !empty($data['password'])) {
    // تشفير الباسورد للأمان
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    try {
        if ($stmt->execute([$data['name'], $data['email'], $hashedPassword])) {
            echo json_encode(["success" => true]);
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "error" => "الإيميل مسجل مسبقاً!"]);
    }
} else {
    echo json_encode(["success" => false, "error" => "برجاء ملء جميع البيانات"]);
}
?>