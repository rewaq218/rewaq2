<?php
// تسجيل دخول المشرف
require_once 'db.php';

// التحقق من طريقة الطلب
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'طريقة الطلب غير مسموح بها']);
    exit;
}

// التحقق من البيانات المطلوبة
if (!isset($_POST['username']) || empty($_POST['username']) || !isset($_POST['password']) || empty($_POST['password'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'اسم المستخدم وكلمة المرور مطلوبان']);
    exit;
}

// تنظيف المدخلات
$username = sanitizeInput($_POST['username']);
$password = sanitizeInput($_POST['password']);

// التحقق من بيانات تسجيل الدخول
$result = verifyLogin($username, $password);

// إرجاع النتيجة
header('Content-Type: application/json');
echo json_encode($result);
