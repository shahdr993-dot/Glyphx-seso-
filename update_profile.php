<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'غير مصرح لك']);
    exit();
}

require_once 'db_config.php';

 $name = $_POST['name'] ?? '';
 $email = $_POST['email'] ?? '';

if (empty($name) || empty($email)) {
    echo json_encode(['success' => false, 'error' => 'جميع الحقول مطلوبة']);
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    $stmt->execute([$name, $email, $_SESSION['user_id']]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    // Check for unique constraint violation (duplicate email)
    if ($e->getCode() == 23000) {
        echo json_encode(['success' => false, 'error' => 'البريد الإلكتروني مستخدم بالفعل']);
    } else {
        echo json_encode(['success' => false, 'error' => 'فشل في تحديث البروفايل']);
    }
}
?>