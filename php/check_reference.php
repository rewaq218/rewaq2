<?php
// التحقق من وجود الرقم المرجعي
require_once 'db.php';

// التحقق من طريقة الطلب
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.1 405 Method Not Allowed');
    header('Content-Type: application/json');
    echo json_encode(['error' => true, 'message' => 'طريقة الطلب غير مسموح بها']);
    exit;
}

// التحقق من وجود الرقم المرجعي
if (!isset($_GET['reference']) || empty($_GET['reference'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => true, 'message' => 'الرقم المرجعي مطلوب']);
    exit;
}

// تنظيف المدخلات
$referenceNumber = sanitizeInput($_GET['reference']);

// التحقق من وجود الرقم المرجعي
$result = checkReferenceNumber($referenceNumber);

// إرجاع النتيجة
header('Content-Type: application/json');
echo json_encode($result);
