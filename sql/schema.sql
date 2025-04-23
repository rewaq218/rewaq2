-- إنشاء قاعدة البيانات
CREATE DATABASE IF NOT EXISTS rewaq_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- استخدام قاعدة البيانات
USE rewaq_db;

-- إنشاء جدول الطلاب
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reference_number VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    passport_number VARCHAR(50) NOT NULL,
    whatsapp_number VARCHAR(50) NOT NULL,
    gender ENUM('male', 'female') NOT NULL,
    age INT NOT NULL,
    governorate VARCHAR(100) NOT NULL,
    residence TEXT NOT NULL,
    qualification VARCHAR(255) NOT NULL,
    education_type ENUM('general', 'azhar', 'other') NOT NULL,
    job VARCHAR(255) NULL,
    academic_year VARCHAR(50) NULL,
    level ENUM('preparatory', 'intermediate', 'specialized') NOT NULL,
    specialization VARCHAR(100) NULL,
    school ENUM('maliki', 'hanafi', 'shafii') NOT NULL,
    attendance_system ENUM('inPerson', 'remote') NOT NULL,
    special_needs ENUM('yes', 'no') NOT NULL,
    id_card_url VARCHAR(255) NULL,
    qualification_url VARCHAR(255) NULL,
    payment_receipt_url VARCHAR(255) NULL,
    created_at DATETIME NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_reference_number (reference_number),
    INDEX idx_level (level),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- إنشاء جدول المشرفين
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NULL,
    last_login DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- إدخال بيانات المشرف الافتراضي
INSERT INTO admins (username, password, full_name, created_at)
VALUES ('admin', 'admin123', 'مشرف النظام', NOW())
ON DUPLICATE KEY UPDATE username = 'admin';
