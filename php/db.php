<?php
// ملف الاتصال بقاعدة البيانات
require_once 'config.php';

// دالة للاتصال بقاعدة البيانات
function connectDB() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // تسجيل الخطأ
        error_log("Database connection error: " . $e->getMessage());
        return null;
    }
}

// دالة للتحقق من وجود الرقم المرجعي
function checkReferenceNumber($referenceNumber) {
    try {
        $db = connectDB();
        if (!$db) {
            return ['error' => true, 'message' => $GLOBALS['errors']['db_connection']];
        }
        
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM students WHERE reference_number = :reference");
        $stmt->bindParam(':reference', $referenceNumber);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return ['exists' => $result['count'] > 0];
    } catch (PDOException $e) {
        error_log("Error checking reference number: " . $e->getMessage());
        return ['error' => true, 'message' => $GLOBALS['errors']['db_query']];
    }
}

// دالة لحفظ بيانات الطالب
function saveStudent($data, $files) {
    try {
        $db = connectDB();
        if (!$db) {
            return ['success' => false, 'message' => $GLOBALS['errors']['db_connection']];
        }
        
        // التحقق من وجود الرقم المرجعي
        $referenceCheck = checkReferenceNumber($data['referenceNumber']);
        if (isset($referenceCheck['error']) && $referenceCheck['error']) {
            return ['success' => false, 'message' => $referenceCheck['message']];
        }
        
        if ($referenceCheck['exists']) {
            return ['success' => false, 'message' => $GLOBALS['errors']['reference_exists']];
        }
        
        // رفع الملفات
        $uploadResults = uploadStudentFiles($data['referenceNumber'], $files);
        
        // إعداد بيانات الإدخال
        $studentData = [
            'reference_number' => $data['referenceNumber'],
            'full_name' => $data['fullName'],
            'passport_number' => $data['passportNumber'],
            'whatsapp_number' => $data['whatsappNumber'],
            'gender' => $data['gender'],
            'age' => (int)$data['age'],
            'governorate' => $data['governorate'],
            'residence' => $data['residence'],
            'qualification' => $data['qualification'],
            'education_type' => $data['educationType'],
            'job' => !empty($data['job']) ? $data['job'] : null,
            'academic_year' => !empty($data['academicYear']) ? $data['academicYear'] : null,
            'level' => $data['level'],
            'specialization' => ($data['level'] === 'specialized' && !empty($data['specialization'])) ? $data['specialization'] : null,
            'school' => $data['school'],
            'attendance_system' => $data['attendanceSystem'],
            'special_needs' => $data['specialNeeds'],
            'id_card_url' => isset($uploadResults['idCardImage']) ? $uploadResults['idCardImage'] : null,
            'qualification_url' => isset($uploadResults['qualificationImage']) ? $uploadResults['qualificationImage'] : null,
            'payment_receipt_url' => isset($uploadResults['paymentReceiptImage']) ? $uploadResults['paymentReceiptImage'] : null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // إنشاء استعلام الإدخال
        $columns = implode(', ', array_keys($studentData));
        $placeholders = ':' . implode(', :', array_keys($studentData));
        
        $stmt = $db->prepare("INSERT INTO students ($columns) VALUES ($placeholders)");
        
        // ربط القيم
        foreach ($studentData as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        // تنفيذ الاستعلام
        $stmt->execute();
        
        return [
            'success' => true,
            'message' => $GLOBALS['success']['student_saved'],
            'id' => $db->lastInsertId()
        ];
    } catch (PDOException $e) {
        error_log("Error saving student: " . $e->getMessage());
        return ['success' => false, 'message' => $GLOBALS['errors']['db_query']];
    }
}

// دالة لرفع ملفات الطالب
function uploadStudentFiles($referenceNumber, $files) {
    $uploadResults = [];
    
    // إنشاء مجلد للطالب
    $studentDir = UPLOAD_DIR . $referenceNumber . '/';
    if (!file_exists($studentDir)) {
        mkdir($studentDir, 0777, true);
    }
    
    // رفع صورة البطاقة
    if (isset($files['idCardImage']) && $files['idCardImage']['error'] === UPLOAD_ERR_OK) {
        $uploadResults['idCardImage'] = uploadFile($files['idCardImage'], $studentDir, 'id_card');
    }
    
    // رفع صورة المؤهل
    if (isset($files['qualificationImage']) && $files['qualificationImage']['error'] === UPLOAD_ERR_OK) {
        $uploadResults['qualificationImage'] = uploadFile($files['qualificationImage'], $studentDir, 'qualification');
    }
    
    // رفع صورة إيصال الدفع
    if (isset($files['paymentReceiptImage']) && $files['paymentReceiptImage']['error'] === UPLOAD_ERR_OK) {
        $uploadResults['paymentReceiptImage'] = uploadFile($files['paymentReceiptImage'], $studentDir, 'payment_receipt');
    }
    
    return $uploadResults;
}

// دالة لرفع ملف
function uploadFile($file, $directory, $prefix) {
    // التحقق من حجم الملف
    if ($file['size'] > MAX_FILE_SIZE) {
        throw new Exception($GLOBALS['errors']['file_size']);
    }
    
    // التحقق من نوع الملف
    if (!isAllowedExtension($file['name'])) {
        throw new Exception($GLOBALS['errors']['file_type']);
    }
    
    // إنشاء اسم ملف فريد
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = $prefix . '_' . uniqid() . '.' . $extension;
    $filepath = $directory . $filename;
    
    // رفع الملف
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception($GLOBALS['errors']['file_upload']);
    }
    
    // إرجاع مسار الملف النسبي
    return str_replace('../', '', $filepath);
}

// دالة للحصول على جميع الطلاب
function getStudents() {
    try {
        $db = connectDB();
        if (!$db) {
            return ['success' => false, 'message' => $GLOBALS['errors']['db_connection']];
        }
        
        $stmt = $db->query("SELECT * FROM students ORDER BY created_at DESC");
        $students = $stmt->fetchAll();
        
        return ['success' => true, 'data' => $students];
    } catch (PDOException $e) {
        error_log("Error getting students: " . $e->getMessage());
        return ['success' => false, 'message' => $GLOBALS['errors']['db_query']];
    }
}

// دالة للحصول على طالب بواسطة المعرف
function getStudentById($id) {
    try {
        $db = connectDB();
        if (!$db) {
            return ['success' => false, 'message' => $GLOBALS['errors']['db_connection']];
        }
        
        $stmt = $db->prepare("SELECT * FROM students WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $student = $stmt->fetch();
        
        if (!$student) {
            return ['success' => false, 'message' => 'الطالب غير موجود'];
        }
        
        return ['success' => true, 'data' => $student];
    } catch (PDOException $e) {
        error_log("Error getting student by ID: " . $e->getMessage());
        return ['success' => false, 'message' => $GLOBALS['errors']['db_query']];
    }
}

// دالة للتحقق من بيانات تسجيل الدخول
function verifyLogin($username, $password) {
    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        return ['success' => true, 'message' => $GLOBALS['success']['login_success']];
    } else {
        return ['success' => false, 'message' => $GLOBALS['errors']['invalid_credentials']];
    }
}
