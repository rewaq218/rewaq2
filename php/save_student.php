<?php
// حفظ بيانات الطالب
require_once 'db.php';

// التحقق من طريقة الطلب
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'طريقة الطلب غير مسموح بها']);
    exit;
}

// التحقق من البيانات المطلوبة
$requiredFields = [
    'referenceNumber', 'fullName', 'passportNumber', 'whatsappNumber',
    'gender', 'age', 'governorate', 'residence', 'qualification',
    'educationType', 'level', 'school', 'attendanceSystem', 'specialNeeds'
];

$missingFields = [];
foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        $missingFields[] = $field;
    }
}

if (!empty($missingFields)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $errors['missing_data'],
        'missing_fields' => $missingFields
    ]);
    exit;
}

// تنظيف المدخلات
$data = [];
foreach ($_POST as $key => $value) {
    $data[$key] = sanitizeInput($value);
}

// حفظ بيانات الطالب
$result = saveStudent($data, $_FILES);

// إرجاع النتيجة
header('Content-Type: application/json');
echo json_encode($result);
