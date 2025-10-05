<?php
// /config/db_config.php
// Database Connection Configuration

define('DB_SERVER', 'localhost');
define('DB_NAME', 'db_mini_hrms');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Base URL Configuration - CRITICAL for correct paths
// Change this based on your installation directory
define('BASE_URL', '/mini-hrms'); // For http://localhost/mini-hrms/
// define('BASE_URL', ''); // Use this if installed at root: http://localhost/

// SMTP Configuration for Gmail
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'traxintertrade.hrss@gmail.com');
define('SMTP_PASS', ''); // Set your app password here
define('SMTP_FROM', 'traxintertrade.hrss@gmail.com');
define('SMTP_FROM_NAME', 'Mini HRMS System');

// System Constants
define('DEFAULT_THEME_COLOR', '#3B82F6'); // Blue
define('DEFAULT_LANGUAGE', 'en');
define('SUPPORTED_LANGUAGES', ['th', 'en', 'my']);

// Super Admin Security Code for DB Management
define('SUPER_ADMIN_CODE', 'HRMS2025'); // Change this in production

// File Upload Settings
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/');
define('PROFILE_PIC_DIR', UPLOAD_DIR . 'profiles/');
define('DOCUMENT_DIR', UPLOAD_DIR . 'documents/');
define('MAX_FILE_SIZE', 5242880); // 5MB

// Session Settings
define('SESSION_LIFETIME', 3600); // 1 hour
define('SESSION_NAME', 'HRMS_SESSION');
?>