<?php
session_start();
require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_pic'])) {
    $user_id = $_SESSION['user_id'];
    $file = $_FILES['profile_pic'];

    // تحديد مكان الحفظ
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true); // إنشاء الفولدر لو مش موجود
    }

    $file_name = time() . '_' . $file['name']; // اسم فريد للصورة
    $target_path = $upload_dir . $file_name;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        // تحديث قاعدة البيانات
        $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
        if ($stmt->execute([$file_name, $user_id])) {
            header("Location: user_home.php?success=1");
            exit();
        }
    }
}
echo "فشل تحميل الصورة يا قائدة ندى!";
?>