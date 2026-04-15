<?php
// تأكدي إن ده أول سطر في الملف
require 'db_config.php';
session_start();

// تحديد إن الرد هيكون JSON
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($data['email'])) {
    $email = $data['email'];
    $password = $data['password'];

    // لو الطلب فيه "الاسم" يبقى ده إنشاء حساب (Register)
    if (isset($data['name'])) {
        $name = $data['name'];
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $hashed_password]);
            echo json_encode(["success" => true]);
        } catch (PDOException $e) {
            echo json_encode(["success" => false, "error" => "الإيميل موجود بالفعل"]);
        }
    } 
    // لو الطلب إيميل وباسورد بس يبقى ده تسجيل دخول (Login)
    else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "الإيميل أو الباسورد خطأ"]);
        }
    }
} else {
    echo json_encode(["success" => false, "error" => "طلب غير مكتمل"]);
}
exit(); // مهم جداً عشان ميبعتش أي حاجة تانية