<?php
// الحصول على طالب بواسطة المعرف
require_once 'db.php';

// التحقق من تسجيل دخول المشرف
checkAdminAccess();

// التحقق من وجود المعرف
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'معرف الطالب مطلوب']);
    exit;
}

// تنظيف المدخلات
$id = sanitizeInput($_GET['id']);

// الحصول على الطالب
$result = getStudentById($id);

// إرجاع النتيجة
header('Content-Type: application/json');
echo json_encode($result);
