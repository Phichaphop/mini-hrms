<?php
// /config/db_config.php

// Database Configuration
define('DB_SERVER', 'localhost');
define('DB_NAME', 'db_mini_hrms');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Base URL Configuration
define('BASE_URL', '/mini-hrms'); 

// SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'traxintertrade.hrss@gmail.com');
define('SMTP_PASS', '');
define('SMTP_FROM', 'traxintertrade.hrss@gmail.com');
define('SMTP_FROM_NAME', 'Mini HRMS System');

// System Constants
define('DEFAULT_THEME_COLOR', '#3B82F6');
define('DEFAULT_LANGUAGE', 'en');
define('SUPPORTED_LANGUAGES', ['th', 'en', 'my']);

// Super Admin Security Code
define('SUPER_ADMIN_CODE', 'HRMS2025');

// File Upload Settings
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/');
define('PROFILE_PIC_DIR', UPLOAD_DIR . 'profiles/');
define('DOCUMENT_DIR', UPLOAD_DIR . 'documents/');
define('MAX_FILE_SIZE', 5242880); // 5MB

// Session Settings - CRITICAL FIX
define('SESSION_LIFETIME', 3600); // 1 hour
define('SESSION_NAME', 'HRMS_SESSION');

// Session Configuration - เพิ่มส่วนนี้
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
ini_set('session.cookie_lifetime', SESSION_LIFETIME);

// สำหรับ AJAX requests
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}
?>