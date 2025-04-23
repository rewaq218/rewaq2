<?php
// الحصول على جميع الطلاب
require_once 'db.php';

// التحقق من تسجيل دخول المشرف
checkAdminAccess();

// الحصول على جميع الطلاب
$result = getStudents();

// إرجاع النتيجة
header('Content-Type: application/json');
echo json_encode($result);
