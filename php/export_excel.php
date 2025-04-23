<?php
// تصدير البيانات إلى Excel
require_once 'db.php';
require_once 'vendor/autoload.php'; // يجب تثبيت مكتبة PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// التحقق من تسجيل دخول المشرف
checkAdminAccess();

try {
    // الحصول على جميع الطلاب
    $result = getStudents();
    
    if (!$result['success']) {
        throw new Exception($result['message']);
    }
    
    $students = $result['data'];
    
    // إنشاء ملف Excel جديد
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // تعيين اتجاه الورقة من اليمين إلى اليسار
    $sheet->setRightToLeft(true);
    
    // إضافة رأس الجدول
    $headers = [
        'الرقم المرجعي',
        'الاسم',
        'المحافظة',
        'رقم جواز السفر',
        'رقم الواتس',
        'النوع',
        'السن',
        'محل الإقامة',
        'المؤهل الدراسي',
        'نوع التعليم',
        'الوظيفة',
        'السنة الدراسية',
        'المرحلة',
        'التخصص',
        'المذهب',
        'نظام الحضور',
        'من ذوي الهمم',
        'تاريخ التسجيل'
    ];
    
    // كتابة رأس الجدول
    $column = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($column . '1', $header);
        $column++;
    }
    
    // تنسيق رأس الجدول
    $sheet->getStyle('A1:' . $column . '1')->getFont()->setBold(true);
    
    // كتابة بيانات الطلاب
    $row = 2;
    foreach ($students as $student) {
        // تحويل المستويات إلى العربية
        $levelInArabic = $student['level'];
        if ($student['level'] === 'preparatory') $levelInArabic = 'تمهيدية';
        else if ($student['level'] === 'intermediate') $levelInArabic = 'متوسطة';
        else if ($student['level'] === 'specialized') $levelInArabic = 'تخصصية';
        
        // تحويل نظام الحضور إلى العربية
        $attendanceInArabic = $student['attendance_system'];
        if ($student['attendance_system'] === 'inPerson') $attendanceInArabic = 'مباشر';
        else if ($student['attendance_system'] === 'remote') $attendanceInArabic = 'عن بعد';
        
        // تحويل النوع إلى العربية
        $genderInArabic = $student['gender'];
        if ($student['gender'] === 'male') $genderInArabic = 'ذكر';
        else if ($student['gender'] === 'female') $genderInArabic = 'أنثى';
        
        // تحويل نوع التعليم إلى العربية
        $educationTypeInArabic = $student['education_type'];
        if ($student['education_type'] === 'general') $educationTypeInArabic = 'عام';
        else if ($student['education_type'] === 'azhar') $educationTypeInArabic = 'أزهري';
        else if ($student['education_type'] === 'other') $educationTypeInArabic = 'أخرى';
        
        // تحويل المذهب إلى العربية
        $schoolInArabic = $student['school'];
        if ($student['school'] === 'maliki') $schoolInArabic = 'مالكي';
        else if ($student['school'] === 'hanafi') $schoolInArabic = 'حنفي';
        else if ($student['school'] === 'shafii') $schoolInArabic = 'شافعي';
        
        // تحويل ذوي الهمم إلى العربية
        $specialNeedsInArabic = $student['special_needs'];
        if ($student['special_needs'] === 'yes') $specialNeedsInArabic = 'نعم';
        else if ($student['special_needs'] === 'no') $specialNeedsInArabic = 'لا';
        
        // تنسيق التاريخ
        $createdAt = date('Y-m-d', strtotime($student['created_at']));
        
        // كتابة بيانات الطالب
        $sheet->setCellValue('A' . $row, $student['reference_number']);
        $sheet->setCellValue('B' . $row, $student['full_name']);
        $sheet->setCellValue('C' . $row, $student['governorate']);
        $sheet->setCellValue('D' . $row, $student['passport_number']);
        $sheet->setCellValue('E' . $row, $student['whatsapp_number']);
        $sheet->setCellValue('F' . $row, $genderInArabic);
        $sheet->setCellValue('G' . $row, $student['age']);
        $sheet->setCellValue('H' . $row, $student['residence']);
        $sheet->setCellValue('I' . $row, $student['qualification']);
        $sheet->setCellValue('J' . $row, $educationTypeInArabic);
        $sheet->setCellValue('K' . $row, $student['job'] ?? '');
        $sheet->setCellValue('L' . $row, $student['academic_year'] ?? '');
        $sheet->setCellValue('M' . $row, $levelInArabic);
        $sheet->setCellValue('N' . $row, $student['specialization'] ?? '');
        $sheet->setCellValue('O' . $row, $schoolInArabic);
        $sheet->setCellValue('P' . $row, $attendanceInArabic);
        $sheet->setCellValue('Q' . $row, $specialNeedsInArabic);
        $sheet->setCellValue('R' . $row, $createdAt);
        
        $row++;
    }
    
    // تنسيق عرض الأعمدة
    foreach (range('A', $column) as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }
    
    // إنشاء ملف Excel
    $writer = new Xlsx($spreadsheet);
    
    // تعيين رأس HTTP
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="بيانات_الطلاب.xlsx"');
    header('Cache-Control: max-age=0');
    
    // حفظ الملف إلى مخرج PHP
    $writer->save('php://output');
    exit;
} catch (Exception $e) {
    // إرجاع رسالة الخطأ
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
