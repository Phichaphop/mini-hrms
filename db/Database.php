<?php
// /db/Database.php
// Database Connection and Core CRUD Operations

require_once __DIR__ . '/../config/db_config.php';

class Database {
    private $conn;
    private static $instance = null;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_SERVER . ";charset=" . DB_CHARSET;
            $this->conn = new PDO($dsn, DB_USER, DB_PASS);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Try to select database if it exists
            if ($this->databaseExists()) {
                $this->conn->exec("USE `" . DB_NAME . "`");
            }
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function databaseExists() {
        try {
            $stmt = $this->conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
            return $stmt->rowCount() > 0;
        } catch(PDOException $e) {
            return false;
        }
    }
    
    public function createDatabase() {
        try {
            $sql = "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
            $this->conn->exec($sql);
            $this->conn->exec("USE `" . DB_NAME . "`");
            return ['success' => true, 'message' => 'Database created successfully'];
        } catch(PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function dropDatabase() {
        try {
            $sql = "DROP DATABASE IF EXISTS `" . DB_NAME . "`";
            $this->conn->exec($sql);
            return ['success' => true, 'message' => 'Database dropped successfully'];
        } catch(PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function createAllTables() {
        try {
            $this->conn->exec("USE `" . DB_NAME . "`");
            
            $tables = $this->getTableDefinitions();
            
            foreach ($tables as $tableSql) {
                $this->conn->exec($tableSql);
            }
            
            // Seed initial data
            $this->seedInitialData();
            
            return ['success' => true, 'message' => 'All tables created and seeded successfully'];
        } catch(PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function dropAllTables() {
        try {
            $this->conn->exec("USE `" . DB_NAME . "`");
            $this->conn->exec("SET FOREIGN_KEY_CHECKS = 0");
            
            $stmt = $this->conn->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($tables as $table) {
                $this->conn->exec("DROP TABLE IF EXISTS `$table`");
            }
            
            $this->conn->exec("SET FOREIGN_KEY_CHECKS = 1");
            return ['success' => true, 'message' => 'All tables dropped successfully'];
        } catch(PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private function getTableDefinitions() {
        return [
            // Roles table
            "CREATE TABLE IF NOT EXISTS `roles` (
                `role_id` INT AUTO_INCREMENT PRIMARY KEY,
                `role_name` VARCHAR(50) NOT NULL UNIQUE,
                `role_description` VARCHAR(255),
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Localization Master
            "CREATE TABLE IF NOT EXISTS `localization_master` (
                `key_id` VARCHAR(100) PRIMARY KEY,
                `th_text` TEXT,
                `en_text` TEXT,
                `my_text` TEXT,
                `category` VARCHAR(50),
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Prefix Master
            "CREATE TABLE IF NOT EXISTS `prefix_master` (
                `prefix_id` INT AUTO_INCREMENT PRIMARY KEY,
                `prefix_name_th` VARCHAR(50),
                `prefix_name_en` VARCHAR(50),
                `prefix_name_my` VARCHAR(50),
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Function Master
            "CREATE TABLE IF NOT EXISTS `function_master` (
                `function_id` INT AUTO_INCREMENT PRIMARY KEY,
                `function_name` VARCHAR(100) NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Division Master
            "CREATE TABLE IF NOT EXISTS `division_master` (
                `division_id` INT AUTO_INCREMENT PRIMARY KEY,
                `division_name` VARCHAR(100) NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Department Master
            "CREATE TABLE IF NOT EXISTS `department_master` (
                `department_id` INT AUTO_INCREMENT PRIMARY KEY,
                `department_name` VARCHAR(100) NOT NULL,
                `division_id` INT,
                FOREIGN KEY (`division_id`) REFERENCES `division_master`(`division_id`) ON DELETE SET NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Section Master
            "CREATE TABLE IF NOT EXISTS `section_master` (
                `section_id` INT AUTO_INCREMENT PRIMARY KEY,
                `section_name` VARCHAR(100) NOT NULL,
                `department_id` INT,
                FOREIGN KEY (`department_id`) REFERENCES `department_master`(`department_id`) ON DELETE SET NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Position Master
            "CREATE TABLE IF NOT EXISTS `position_master` (
                `position_id` INT AUTO_INCREMENT PRIMARY KEY,
                `position_name` VARCHAR(100) NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Service Category Master
            "CREATE TABLE IF NOT EXISTS `service_category_master` (
                `category_id` INT AUTO_INCREMENT PRIMARY KEY,
                `category_name_th` VARCHAR(100),
                `category_name_en` VARCHAR(100),
                `category_name_my` VARCHAR(100),
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Service Type Master
            "CREATE TABLE IF NOT EXISTS `service_type_master` (
                `type_id` INT AUTO_INCREMENT PRIMARY KEY,
                `type_name_th` VARCHAR(100),
                `type_name_en` VARCHAR(100),
                `type_name_my` VARCHAR(100),
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Document Type Master
            "CREATE TABLE IF NOT EXISTS `doc_type_master` (
                `doc_type_id` INT AUTO_INCREMENT PRIMARY KEY,
                `doc_type_name` VARCHAR(100) NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Employees table
            "CREATE TABLE IF NOT EXISTS `employees` (
                `employee_id` VARCHAR(6) PRIMARY KEY,
                `prefix_id` INT,
                `full_name_th` VARCHAR(200),
                `full_name_en` VARCHAR(200),
                `function_id` INT,
                `division_id` INT,
                `department_id` INT,
                `section_id` INT,
                `operation` VARCHAR(100),
                `position_id` INT,
                `position_level` VARCHAR(50),
                `labour_cost` DECIMAL(10,2),
                `hiring_type` VARCHAR(50),
                `customer_zone` VARCHAR(100),
                `contribution_level` VARCHAR(50),
                `sex` ENUM('Male','Female','Other'),
                `nationality` VARCHAR(50),
                `birthday` DATE,
                `age` INT,
                `education_level` VARCHAR(100),
                `phone_no` VARCHAR(20),
                `address_village` VARCHAR(200),
                `address_subdistrict` VARCHAR(100),
                `address_district` VARCHAR(100),
                `address_province` VARCHAR(100),
                `date_of_hire` DATE,
                `year_of_service` INT,
                `date_of_termination` DATE,
                `month_of_termination` INT,
                `status` ENUM('Active','Inactive','Terminated') DEFAULT 'Active',
                `reason_for_termination` TEXT,
                `suggestion` TEXT,
                `remark` TEXT,
                `username` VARCHAR(100) UNIQUE NOT NULL,
                `password` VARCHAR(255) NOT NULL,
                `role_id` INT NOT NULL,
                `profile_pic_path` VARCHAR(255),
                `theme_color_preference` VARCHAR(7) DEFAULT '#3B82F6',
                `language_preference` VARCHAR(2) DEFAULT 'en',
                FOREIGN KEY (`role_id`) REFERENCES `roles`(`role_id`),
                FOREIGN KEY (`prefix_id`) REFERENCES `prefix_master`(`prefix_id`) ON DELETE SET NULL,
                FOREIGN KEY (`function_id`) REFERENCES `function_master`(`function_id`) ON DELETE SET NULL,
                FOREIGN KEY (`division_id`) REFERENCES `division_master`(`division_id`) ON DELETE SET NULL,
                FOREIGN KEY (`department_id`) REFERENCES `department_master`(`department_id`) ON DELETE SET NULL,
                FOREIGN KEY (`section_id`) REFERENCES `section_master`(`section_id`) ON DELETE SET NULL,
                FOREIGN KEY (`position_id`) REFERENCES `position_master`(`position_id`) ON DELETE SET NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Company Information
            "CREATE TABLE IF NOT EXISTS `company_info` (
                `company_id` INT AUTO_INCREMENT PRIMARY KEY,
                `company_name` VARCHAR(255) NOT NULL,
                `phone` VARCHAR(20),
                `fax` VARCHAR(20),
                `address` TEXT,
                `representative_name` VARCHAR(200),
                `company_logo_path` VARCHAR(255),
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Locker Master
            "CREATE TABLE IF NOT EXISTS `locker_master` (
                `locker_id` INT AUTO_INCREMENT PRIMARY KEY,
                `locker_number` VARCHAR(20) UNIQUE NOT NULL,
                `status` ENUM('Available','Occupied','Maintenance') DEFAULT 'Available',
                `location` VARCHAR(100),
                `current_owner_id` VARCHAR(6),
                FOREIGN KEY (`current_owner_id`) REFERENCES `employees`(`employee_id`) ON DELETE SET NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Locker Usage History
            "CREATE TABLE IF NOT EXISTS `locker_usage_history` (
                `history_id` INT AUTO_INCREMENT PRIMARY KEY,
                `locker_id` INT NOT NULL,
                `employee_id` VARCHAR(6) NOT NULL,
                `start_date` DATE NOT NULL,
                `end_date` DATE,
                FOREIGN KEY (`locker_id`) REFERENCES `locker_master`(`locker_id`) ON DELETE CASCADE,
                FOREIGN KEY (`employee_id`) REFERENCES `employees`(`employee_id`) ON DELETE CASCADE,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Documents Storage
            "CREATE TABLE IF NOT EXISTS `documents` (
                `doc_id` INT AUTO_INCREMENT PRIMARY KEY,
                `file_name_custom` VARCHAR(255) NOT NULL,
                `file_path` VARCHAR(500) NOT NULL,
                `doc_type_id` INT NOT NULL,
                `uploaded_by` VARCHAR(6) NOT NULL,
                `upload_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`doc_type_id`) REFERENCES `doc_type_master`(`doc_type_id`) ON DELETE CASCADE,
                FOREIGN KEY (`uploaded_by`) REFERENCES `employees`(`employee_id`) ON DELETE CASCADE,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Leave Requests
            "CREATE TABLE IF NOT EXISTS `leave_requests` (
                `request_id` INT AUTO_INCREMENT PRIMARY KEY,
                `employee_id` VARCHAR(6) NOT NULL,
                `leave_type` VARCHAR(50) NOT NULL,
                `start_date` DATE NOT NULL,
                `end_date` DATE NOT NULL,
                `total_days` INT NOT NULL,
                `reason` TEXT,
                `status` ENUM('New','In Progress','Complete','Cancelled') DEFAULT 'New',
                `handler_id` VARCHAR(6),
                `handler_remarks` TEXT,
                `satisfaction_score` INT CHECK (`satisfaction_score` BETWEEN 1 AND 5),
                `satisfaction_feedback` TEXT,
                FOREIGN KEY (`employee_id`) REFERENCES `employees`(`employee_id`) ON DELETE CASCADE,
                FOREIGN KEY (`handler_id`) REFERENCES `employees`(`employee_id`) ON DELETE SET NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Certificate Requests
            "CREATE TABLE IF NOT EXISTS `certificate_requests` (
                `request_id` INT AUTO_INCREMENT PRIMARY KEY,
                `employee_id` VARCHAR(6) NOT NULL,
                `certificate_no` VARCHAR(50),
                `purpose` TEXT,
                `status` ENUM('New','In Progress','Complete','Cancelled') DEFAULT 'New',
                `handler_id` VARCHAR(6),
                `handler_remarks` TEXT,
                `satisfaction_score` INT CHECK (`satisfaction_score` BETWEEN 1 AND 5),
                `satisfaction_feedback` TEXT,
                FOREIGN KEY (`employee_id`) REFERENCES `employees`(`employee_id`) ON DELETE CASCADE,
                FOREIGN KEY (`handler_id`) REFERENCES `employees`(`employee_id`) ON DELETE SET NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // ID Card Requests
            "CREATE TABLE IF NOT EXISTS `id_card_requests` (
                `request_id` INT AUTO_INCREMENT PRIMARY KEY,
                `employee_id` VARCHAR(6) NOT NULL,
                `reason` VARCHAR(255),
                `status` ENUM('New','In Progress','Complete','Cancelled') DEFAULT 'New',
                `handler_id` VARCHAR(6),
                `handler_remarks` TEXT,
                `satisfaction_score` INT CHECK (`satisfaction_score` BETWEEN 1 AND 5),
                `satisfaction_feedback` TEXT,
                FOREIGN KEY (`employee_id`) REFERENCES `employees`(`employee_id`) ON DELETE CASCADE,
                FOREIGN KEY (`handler_id`) REFERENCES `employees`(`employee_id`) ON DELETE SET NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Shuttle Bus Requests
            "CREATE TABLE IF NOT EXISTS `shuttle_bus_requests` (
                `request_id` INT AUTO_INCREMENT PRIMARY KEY,
                `employee_id` VARCHAR(6) NOT NULL,
                `route` VARCHAR(255),
                `pickup_location` VARCHAR(255),
                `request_date` DATE NOT NULL,
                `status` ENUM('New','In Progress','Complete','Cancelled') DEFAULT 'New',
                `handler_id` VARCHAR(6),
                `handler_remarks` TEXT,
                `satisfaction_score` INT CHECK (`satisfaction_score` BETWEEN 1 AND 5),
                `satisfaction_feedback` TEXT,
                FOREIGN KEY (`employee_id`) REFERENCES `employees`(`employee_id`) ON DELETE CASCADE,
                FOREIGN KEY (`handler_id`) REFERENCES `employees`(`employee_id`) ON DELETE SET NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Locker Usage Requests
            "CREATE TABLE IF NOT EXISTS `locker_usage_requests` (
                `request_id` INT AUTO_INCREMENT PRIMARY KEY,
                `employee_id` VARCHAR(6) NOT NULL,
                `preferred_location` VARCHAR(100),
                `reason` TEXT,
                `assigned_locker_id` INT,
                `status` ENUM('New','In Progress','Complete','Cancelled') DEFAULT 'New',
                `handler_id` VARCHAR(6),
                `handler_remarks` TEXT,
                `satisfaction_score` INT CHECK (`satisfaction_score` BETWEEN 1 AND 5),
                `satisfaction_feedback` TEXT,
                FOREIGN KEY (`employee_id`) REFERENCES `employees`(`employee_id`) ON DELETE CASCADE,
                FOREIGN KEY (`handler_id`) REFERENCES `employees`(`employee_id`) ON DELETE SET NULL,
                FOREIGN KEY (`assigned_locker_id`) REFERENCES `locker_master`(`locker_id`) ON DELETE SET NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Supplies/Uniform Requests
            "CREATE TABLE IF NOT EXISTS `supplies_requests` (
                `request_id` INT AUTO_INCREMENT PRIMARY KEY,
                `employee_id` VARCHAR(6) NOT NULL,
                `request_type` ENUM('Office Supplies','Work Equipment','Uniform','Safety Equipment') NOT NULL,
                `items_list` TEXT NOT NULL,
                `status` ENUM('New','In Progress','Complete','Cancelled') DEFAULT 'New',
                `handler_id` VARCHAR(6),
                `handler_remarks` TEXT,
                `satisfaction_score` INT CHECK (`satisfaction_score` BETWEEN 1 AND 5),
                `satisfaction_feedback` TEXT,
                FOREIGN KEY (`employee_id`) REFERENCES `employees`(`employee_id`) ON DELETE CASCADE,
                FOREIGN KEY (`handler_id`) REFERENCES `employees`(`employee_id`) ON DELETE SET NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Skill Test Requests
            "CREATE TABLE IF NOT EXISTS `skill_test_requests` (
                `request_id` INT AUTO_INCREMENT PRIMARY KEY,
                `employee_id` VARCHAR(6) NOT NULL,
                `skill_area` VARCHAR(100) NOT NULL,
                `preferred_date` DATE,
                `status` ENUM('New','In Progress','Complete','Cancelled') DEFAULT 'New',
                `handler_id` VARCHAR(6),
                `handler_remarks` TEXT,
                `satisfaction_score` INT CHECK (`satisfaction_score` BETWEEN 1 AND 5),
                `satisfaction_feedback` TEXT,
                FOREIGN KEY (`employee_id`) REFERENCES `employees`(`employee_id`) ON DELETE CASCADE,
                FOREIGN KEY (`handler_id`) REFERENCES `employees`(`employee_id`) ON DELETE SET NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // QR Code Document Submissions
            "CREATE TABLE IF NOT EXISTS `qr_document_submissions` (
                `submission_id` INT AUTO_INCREMENT PRIMARY KEY,
                `employee_id` VARCHAR(6) NOT NULL,
                `service_category_id` INT NOT NULL,
                `service_type_id` INT NOT NULL,
                `document_path` VARCHAR(500),
                `notes` TEXT,
                `status` ENUM('New','In Progress','Complete','Cancelled') DEFAULT 'New',
                `handler_id` VARCHAR(6),
                `handler_remarks` TEXT,
                `satisfaction_score` INT CHECK (`satisfaction_score` BETWEEN 1 AND 5),
                `satisfaction_feedback` TEXT,
                FOREIGN KEY (`employee_id`) REFERENCES `employees`(`employee_id`) ON DELETE CASCADE,
                FOREIGN KEY (`service_category_id`) REFERENCES `service_category_master`(`category_id`) ON DELETE CASCADE,
                FOREIGN KEY (`service_type_id`) REFERENCES `service_type_master`(`type_id`) ON DELETE CASCADE,
                FOREIGN KEY (`handler_id`) REFERENCES `employees`(`employee_id`) ON DELETE SET NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        ];
    }
    
    private function seedInitialData() {
        // Seed Roles
        $this->conn->exec("INSERT INTO `roles` (`role_name`, `role_description`) VALUES
            ('Admin', 'Full access to all system functions'),
            ('Officer', 'Can view and edit operational data'),
            ('Employee', 'Can view own data and submit requests')
        ");
        
        // Seed Prefix Master
        $this->conn->exec("INSERT INTO `prefix_master` (`prefix_name_th`, `prefix_name_en`, `prefix_name_my`) VALUES
            ('นาย', 'Mr.', 'ကို'),
            ('นาง', 'Mrs.', 'ဒေါ်'),
            ('นางสาว', 'Miss', 'မ'),
            ('ดร.', 'Dr.', 'ဒေါက်တာ')
        ");
        
        // Seed Function Master
        $this->conn->exec("INSERT INTO `function_master` (`function_name`) VALUES
            ('Administration'),
            ('Operations'),
            ('Finance'),
            ('Human Resources'),
            ('IT Support')
        ");
        
        // Seed Division Master
        $this->conn->exec("INSERT INTO `division_master` (`division_name`) VALUES
            ('Corporate Office'),
            ('Manufacturing'),
            ('Sales & Marketing'),
            ('Support Services')
        ");
        
        // Seed Department Master
        $this->conn->exec("INSERT INTO `department_master` (`department_name`, `division_id`) VALUES
            ('Executive Management', 1),
            ('Production', 2),
            ('Quality Control', 2),
            ('Sales', 3),
            ('HR Department', 1)
        ");
        
        // Seed Section Master
        $this->conn->exec("INSERT INTO `section_master` (`section_name`, `department_id`) VALUES
            ('General Affairs', 1),
            ('Production Line 1', 2),
            ('QA Lab', 3),
            ('Domestic Sales', 4),
            ('Recruitment', 5)
        ");
        
        // Seed Position Master
        $this->conn->exec("INSERT INTO `position_master` (`position_name`) VALUES
            ('Manager'),
            ('Supervisor'),
            ('Officer'),
            ('Technician'),
            ('Operator')
        ");
        
        // Seed Service Category Master
        $this->conn->exec("INSERT INTO `service_category_master` (`category_name_th`, `category_name_en`, `category_name_my`) VALUES
            ('แบบฟอร์มการลา', 'Leave Form', 'ခွင့်လွှာ'),
            ('ปัญหาสแกนนิ้ว', 'Finger Scan Issue', 'လက်ဗွေစကင်ပြဿနာ'),
            ('เอกสารบัญชีธนาคาร', 'Bank Account Document', 'ဘဏ်အကောင့်စာရွက်'),
            ('บัตรประชาชน', 'ID Card', 'မှတ်ပုံတင်'),
            ('ใบอนุญาตออกจากสถานที่', 'Company Premises Exit Permit', 'ကုမ္ပဏီမှထွက်ခွင့်ပါမစ်'),
            ('ใบรับรอง', 'Certificate', 'လက်မှတ်')
        ");
        
        // Seed Service Type Master
        $this->conn->exec("INSERT INTO `service_type_master` (`type_name_th`, `type_name_en`, `type_name_my`) VALUES
            ('บุคคล', 'Individual/Person', 'တစ်ဦးချင်း'),
            ('แผนก/กลุ่ม', 'Department/Group', 'ဌာန/အဖွဲ့')
        ");
        
        // Seed Document Type Master
        $this->conn->exec("INSERT INTO `doc_type_master` (`doc_type_name`) VALUES
            ('Company Policy'),
            ('Employee Handbook'),
            ('Training Materials'),
            ('Safety Procedures'),
            ('Forms & Templates')
        ");
        
        // Seed Locker Master
        $this->conn->exec("INSERT INTO `locker_master` (`locker_number`, `status`, `location`) VALUES
            ('L001', 'Available', 'Building A - Floor 1'),
            ('L002', 'Available', 'Building A - Floor 1'),
            ('L003', 'Available', 'Building A - Floor 2'),
            ('L004', 'Available', 'Building B - Floor 1'),
            ('L005', 'Available', 'Building B - Floor 2')
        ");
        
        // Seed Company Info
        $this->conn->exec("INSERT INTO `company_info` (`company_name`, `phone`, `fax`, `address`, `representative_name`) VALUES
            ('Trax Inter Trade Co., Ltd.', '+66-2-xxx-xxxx', '+66-2-xxx-xxxx', '123 Business Street, Bangkok, Thailand', 'John Doe')
        ");
        
        // Seed Sample Employees (including Admin)
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $officerPassword = password_hash('officer123', PASSWORD_DEFAULT);
        $employeePassword = password_hash('emp123', PASSWORD_DEFAULT);
        
        $this->conn->exec("INSERT INTO `employees` 
            (`employee_id`, `prefix_id`, `full_name_th`, `full_name_en`, `function_id`, `division_id`, `department_id`, `section_id`, 
            `position_id`, `sex`, `nationality`, `birthday`, `age`, `education_level`, `phone_no`, `date_of_hire`, `year_of_service`, 
            `status`, `username`, `password`, `role_id`, `theme_color_preference`, `language_preference`) VALUES
            ('000001', 1, 'สมชาย ใจดี', 'Somchai Jaidee', 1, 1, 1, 1, 1, 'Male', 'Thai', '1985-03-15', 39, 'Bachelor Degree', '081-111-1111', '2010-01-15', 15, 'Active', 'admin', '$adminPassword', 1, '#3B82F6', 'en'),
            ('000002', 2, 'สมหญิง รักงาน', 'Somying Rakngaan', 4, 1, 5, 5, 2, 'Female', 'Thai', '1990-07-20', 34, 'Master Degree', '081-222-2222', '2015-06-01', 9, 'Active', 'officer1', '$officerPassword', 2, '#10B981', 'th'),
            ('000003', 1, 'จอห์น สมิธ', 'John Smith', 2, 2, 2, 2, 3, 'Male', 'American', '1992-11-10', 32, 'Bachelor Degree', '081-333-3333', '2018-03-20', 6, 'Active', 'emp001', '$employeePassword', 3, '#F59E0B', 'en'),
            ('000004', 3, 'มารี ต้น', 'Mary Htun', 2, 2, 3, 3, 4, 'Female', 'Myanmar', '1995-05-25', 29, 'Diploma', '081-444-4444', '2020-08-15', 4, 'Active', 'emp002', '$employeePassword', 3, '#EF4444', 'my'),
            ('000005', 1, 'ประยุทธ์ ขยัน', 'Prayut Kayan', 3, 3, 4, 4, 5, 'Male', 'Thai', '1988-12-05', 36, 'High School', '081-555-5555', '2012-10-01', 12, 'Active', 'emp003', '$employeePassword', 3, '#8B5CF6', 'th')
        ");
        
        // Seed Localization Master (Sample UI texts)
        $localizationData = [
            // Login & Auth
            ['login', 'เข้าสู่ระบบ', 'Login', 'ဝင်ရောက်ရန်', 'auth'],
            ['username', 'ชื่อผู้ใช้', 'Username', 'အသုံးပြုသူအမည်', 'auth'],
            ['password', 'รหัสผ่าน', 'Password', 'လျှို့ဝှက်နံပါတ်', 'auth'],
            ['logout', 'ออกจากระบบ', 'Logout', 'ထွက်ရန်', 'auth'],
            ['forgot_password', 'ลืมรหัสผ่าน', 'Forgot Password', 'စကားဝှက်မေ့နေပါသလား', 'auth'],
            
            // Dashboard
            ['dashboard', 'หน้าหลัก', 'Dashboard', 'ပင်မစာမျက်နှာ', 'menu'],
            ['welcome', 'ยินดีต้อนรับ', 'Welcome', 'ကြိုဆိုပါတယ်', 'general'],
            ['profile', 'ข้อมูลส่วนตัว', 'Profile', 'ကိုယ်ရေးအချက်အလက်', 'menu'],
            
            // Employee Management
            ['employees', 'พนักงาน', 'Employees', 'ဝန်ထမ်းများ', 'menu'],
            ['employee_list', 'รายชื่อพนักงาน', 'Employee List', 'ဝန်ထမ်းစာရင်း', 'menu'],
            ['add_employee', 'เพิ่มพนักงาน', 'Add Employee', 'ဝန်ထမ်းသစ်ထည့်ရန်', 'action'],
            ['edit_employee', 'แก้ไขข้อมูลพนักงาน', 'Edit Employee', 'ဝန်ထမ်းအချက်อლက်ပြင်ဆင်ရန်', 'action'],
            ['employee_id', 'รหัสพนักงาน', 'Employee ID', 'ဝန်ထမ်းနံပါတ်', 'field'],
            ['full_name', 'ชื่อ-นามสกุล', 'Full Name', 'အမည်အပြည့်အစုံ', 'field'],
            
            // Requests
            ['requests', 'คำขอ', 'Requests', 'တောင်းဆိုချက်များ', 'menu'],
            ['my_requests', 'คำขอของฉัน', 'My Requests', 'ကျွန်ုပ်၏တောင်းဆိုချက်များ', 'menu'],
            ['new_request', 'สร้างคำขอใหม่', 'New Request', 'တောင်းဆိုချက်သစ်', 'action'],
            ['leave_request', 'ขอลา', 'Leave Request', 'ခွင့်တောင်းခြင်း', 'request'],
            ['certificate_request', 'ขอใบรับรอง', 'Certificate Request', 'လက်မှတ်တောင်းခြင်း', 'request'],
            ['id_card_request', 'ขอบัตรพนักงาน', 'ID Card Request', 'မှတ်ပုံတင်တောင်းခြင်း', 'request'],
            ['shuttle_bus_request', 'ขอรถรับส่ง', 'Shuttle Bus Request', 'ကားတောင်းခြင်း', 'request'],
            ['locker_request', 'ขอตู้ล็อกเกอร์', 'Locker Request', 'သော့ခတ်သေတ္တာတောင်းခြင်း', 'request'],
            ['supplies_request', 'ขอเครื่องมือ/ยูนิฟอร์ม', 'Supplies/Uniform Request', 'ပစ္စည်း/ယူနီဖောင်းတောင်းခြင်း', 'request'],
            ['skill_test_request', 'ขอทดสอบทักษะ', 'Skill Test Request', 'ကျွမ်းကျင်မှုစစ်ဆေးခြင်း', 'request'],
            
            // Status
            ['status', 'สถานะ', 'Status', 'အခြေအနေ', 'field'],
            ['status_new', 'ใหม่', 'New', 'အသစ်', 'status'],
            ['status_in_progress', 'กำลังดำเนินการ', 'In Progress', 'လုပ်ဆောင်နေသည်', 'status'],
            ['status_complete', 'เสร็จสิ้น', 'Complete', 'ပြီးစီးပြီ', 'status'],
            ['status_cancelled', 'ยกเลิก', 'Cancelled', 'ပယ်ဖျက်ပြီး', 'status'],
            
            // Actions
            ['submit', 'ส่ง', 'Submit', 'တင်သွင်းရန်', 'action'],
            ['cancel', 'ยกเลิก', 'Cancel', 'ပယ်ဖျက်ရန်', 'action'],
            ['edit', 'แก้ไข', 'Edit', 'ပြင်ဆင်ရန်', 'action'],
            ['delete', 'ลบ', 'Delete', 'ဖျက်ရန်', 'action'],
            ['view', 'ดู', 'View', 'ကြည့်ရှုရန်', 'action'],
            ['save', 'บันทึก', 'Save', 'သိမ်းဆည်းရန်', 'action'],
            ['update', 'อัปเดต', 'Update', 'မွမ်းမံရန်', 'action'],
            ['search', 'ค้นหา', 'Search', 'ရှာဖွေရန်', 'action'],
            ['export', 'ส่งออก', 'Export', 'ပို့ကုန်', 'action'],
            ['import', 'นำเข้า', 'Import', 'သွင်းကုန်', 'action'],
            
            // Master Data
            ['master_data', 'ข้อมูลหลัก', 'Master Data', 'အဓိကအချက်အလက်', 'menu'],
            ['manage_master_data', 'จัดการข้อมูลหลัก', 'Manage Master Data', 'အဓိကအချက်အလက်စီမံခန့်ခွဲရန်', 'menu'],
            
            // Documents
            ['documents', 'เอกสาร', 'Documents', 'စာရွက်စာတမ်းများ', 'menu'],
            ['upload_document', 'อัปโหลดเอกสาร', 'Upload Document', 'စာရွက်တင်ရန်', 'action'],
            
            // Settings
            ['settings', 'ตั้งค่า', 'Settings', 'ချိန်ညှိမှုများ', 'menu'],
            ['theme_color', 'สีธีม', 'Theme Color', 'အရောင်ပုံစံ', 'setting'],
            ['language', 'ภาษา', 'Language', 'ဘာသာစကား', 'setting'],
            
            // Messages
            ['success', 'สำเร็จ', 'Success', 'အောင်မြင်ပါသည်', 'message'],
            ['error', 'ข้อผิดพลาด', 'Error', 'အမှား', 'message'],
            ['confirm_delete', 'คุณแน่ใจหรือไม่ที่จะลบข้อมูลนี้', 'Are you sure you want to delete this?', 'ဤအချက်အလက်ကိုဖျက်လိုပါသလား', 'message'],
            ['no_data', 'ไม่มีข้อมูล', 'No data available', 'အချက်အလက်မရှိပါ', 'message']
        ];
        
        $stmt = $this->conn->prepare("INSERT INTO `localization_master` (`key_id`, `th_text`, `en_text`, `my_text`, `category`) VALUES (?, ?, ?, ?, ?)");
        foreach ($localizationData as $data) {
            $stmt->execute($data);
        }
    }
    
    public function query($sql, $params = []) {
        try {
            // Ensure we're using the correct database before any query
            if ($this->databaseExists()) {
                $this->conn->exec("USE `" . DB_NAME . "`");
            }
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }
    
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
}
?>