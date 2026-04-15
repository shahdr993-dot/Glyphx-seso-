<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'غير مصرح لك']);
    exit();
}

require_once 'db_config.php';

 $place_id = $_POST['place_id'] ?? '';
if (empty($place_id) || !isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'بيانات غير صالحة']);
    exit();
}

 $uploadDir = 'uploads/photos/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

 $file = $_FILES['photo'];
 $fileName = time() . '_' . basename($file['name']);
 $targetPath = $uploadDir . $fileName;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    echo json_encode(['success' => false, 'error' => 'فشل في رفع الصورة']);
    exit();
}

try {
    $stmt = $pdo->prepare("INSERT INTO visits (user_id, place_id, photo_path, visit_time) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$_SESSION['user_id'], $place_id, $targetPath]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'فشل في تسجيل الزيارة']);
}
?>