<?php
// ملف الإعدادات

// معلومات الاتصال بقاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_NAME', 'rewaq_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// مجلد التحميل
define('UPLOAD_DIR', '../uploads/');

// بيانات تسجيل الدخول للمشرف
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123');

// إعدادات الجلسة
session_start();

// تعيين منطقة التوقيت
date_default_timezone_set('Africa/Cairo');

// تعيين ترميز الاتصال
ini_set('default_charset', 'UTF-8');

// تعيين الحد الأقصى لحجم الملف (10 ميجابايت)
define('MAX_FILE_SIZE', 10 * 1024 * 1024);

// الامتدادات المسموح بها للملفات
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);

// رسائل الخطأ
$errors = [
    'db_connection' => 'فشل الاتصال بقاعدة البيانات',
    'db_query' => 'حدث خطأ أثناء تنفيذ الاستعلام',
    'file_size' => 'حجم الملف أكبر من الحد المسموح به',
    'file_type' => 'نوع الملف غير مسموح به',
    'file_upload' => 'فشل في رفع الملف',
    'invalid_credentials' => 'اسم المستخدم أو كلمة المرور غير صحيحة',
    'access_denied' => 'غير مصرح لك بالوصول إلى هذه الصفحة',
    'reference_exists' => 'الرقم المرجعي مستخدم بالفعل',
    'missing_data' => 'بيانات غير مكتملة'
];

// رسائل النجاح
$success = [
    'student_saved' => 'تم حفظ بيانات الطالب بنجاح',
    'login_success' => 'تم تسجيل الدخول بنجاح',
    'file_uploaded' => 'تم رفع الملف بنجاح'
];

// دالة للتحقق من تسجيل دخول المشرف
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// دالة للتحقق من الوصول
function checkAdminAccess() {
    global $errors;
    if (!isAdminLoggedIn()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $errors['access_denied']]);
        exit;
    }
}

// دالة لتنظيف المدخلات
function sanitizeInput($input) {
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $input[$key] = sanitizeInput($value);
        }
    } else {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
    return $input;
}

// دالة للتحقق من امتداد الملف
function isAllowedExtension($filename) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($extension, ALLOWED_EXTENSIONS);
}

// دالة لإنشاء اسم ملف فريد
function generateUniqueFilename($filename) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return uniqid() . '_' . time() . '.' . $extension;
}
