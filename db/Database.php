<?php
// /db/Database.php
// Database Connection and Core CRUD Operations - UPDATED

require_once __DIR__ . '/../config/db_config.php';

class Database {
    private $conn;
    private static $instance = null;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_SERVER . ";charset=" . DB_CHARSET;
            $this->conn = new PDO($dsn, DB_USER, DB_PASS);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
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
            "CREATE TABLE IF NOT EXISTS `roles` (
                `role_id` INT AUTO_INCREMENT PRIMARY KEY,
                `role_name` VARCHAR(50) NOT NULL UNIQUE,
                `role_description` VARCHAR(255),
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS `localization_master` (
                `key_id` VARCHAR(100) PRIMARY KEY,
                `th_text` TEXT,
                `en_text` TEXT,
                `my_text` TEXT,
                `category` VARCHAR(50),
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS `prefix_master` (
                `prefix_id` INT AUTO_INCREMENT PRIMARY KEY,
                `prefix_name_th` VARCHAR(50),
                `prefix_name_en` VARCHAR(50),
                `prefix_name_my` VARCHAR(50),
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS `function_master` (
                `function_id` INT AUTO_INCREMENT PRIMARY KEY,
                `function_name` VARCHAR(100) NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS `division_master` (
                `division_id` INT AUTO_INCREMENT PRIMARY KEY,
                `division_name` VARCHAR(100) NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS `department_master` (
                `department_id` INT AUTO_INCREMENT PRIMARY KEY,
                `department_name` VARCHAR(100) NOT NULL,
                `division_id` INT,
                FOREIGN KEY (`division_id`) REFERENCES `division_master`(`division_id`) ON DELETE SET NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS `section_master` (
                `section_id` INT AUTO_INCREMENT PRIMARY KEY,
                `section_name` VARCHAR(100) NOT NULL,
                `department_id` INT,
                FOREIGN KEY (`department_id`) REFERENCES `department_master`(`department_id`) ON DELETE SET NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS `position_master` (
                `position_id` INT AUTO_INCREMENT PRIMARY KEY,
                `position_name` VARCHAR(100) NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS `position_level_master` (
                `level_id` INT AUTO_INCREMENT PRIMARY KEY,
                `level_name` VARCHAR(100) NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS `education_level_master` (
                `education_id` INT AUTO_INCREMENT PRIMARY KEY,
                `education_name_th` VARCHAR(100),
                `education_name_en` VARCHAR(100),
                `education_name_my` VARCHAR(100),
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS `service_category_master` (
                `category_id` INT AUTO_INCREMENT PRIMARY KEY,
                `category_name_th` VARCHAR(100),
                `category_name_en` VARCHAR(100),
                `category_name_my` VARCHAR(100),
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS `service_type_master` (
                `type_id` INT AUTO_INCREMENT PRIMARY KEY,
                `type_name_th` VARCHAR(100),
                `type_name_en` VARCHAR(100),
                `type_name_my` VARCHAR(100),
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS `doc_type_master` (
                `doc_type_id` INT AUTO_INCREMENT PRIMARY KEY,
                `doc_type_name` VARCHAR(100) NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS `operation_master` (
                `operation_id` INT AUTO_INCREMENT PRIMARY KEY,
                `operation_name` VARCHAR(100) NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS `labour_cost_master` (
                `cost_id` INT AUTO_INCREMENT PRIMARY KEY,
                `cost_name_th` VARCHAR(100),
                `cost_name_en` VARCHAR(100),
                `cost_name_my` VARCHAR(100),
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS `hiring_type_master` (
                `hiring_type_id` INT AUTO_INCREMENT PRIMARY KEY,
                `hiring_type_name_th` VARCHAR(100),
                `hiring_type_name_en` VARCHAR(100),
                `hiring_type_name_my` VARCHAR(100),
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS `customer_zone_master` (
                `zone_id` INT AUTO_INCREMENT PRIMARY KEY,
                `zone_name` VARCHAR(100) NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS `contribution_level_master` (
                `level_id` INT AUTO_INCREMENT PRIMARY KEY,
                `level_name` VARCHAR(100) NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS `leave_type_master` (
                `leave_type_id` INT AUTO_INCREMENT PRIMARY KEY,
                `leave_type_name_th` VARCHAR(100),
                `leave_type_name_en` VARCHAR(100),
                `leave_type_name_my` VARCHAR(100),
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS `employees` (
                `employee_id` VARCHAR(8) PRIMARY KEY,
                `prefix_id` INT,
                `full_name_th` VARCHAR(200),
                `full_name_en` VARCHAR(200),
                `function_id` INT,
                `division_id` INT,
                `department_id` INT,
                `section_id` INT,
                `operation_id` INT,
                `position_id` INT,
                `position_level` VARCHAR(50),
                `labour_cost_id` INT,
                `hiring_type_id` INT,
                `customer_zone_id` INT,
                `contribution_level_id` INT,
                `sex` ENUM('Male','Female','Other'),
                `nationality` VARCHAR(50),
                `birthday` DATE,
                `age` INT,
                `education_level_id` INT,
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
                `theme_mode` VARCHAR(10) DEFAULT 'light',
                `theme_color_preference` VARCHAR(7) DEFAULT '#3B82F6',
                `language_preference` VARCHAR(2) DEFAULT 'en',
                FOREIGN KEY (`role_id`) REFERENCES `roles`(`role_id`),
                FOREIGN KEY (`prefix_id`) REFERENCES `prefix_master`(`prefix_id`) ON DELETE SET NULL,
                FOREIGN KEY (`function_id`) REFERENCES `function_master`(`function_id`) ON DELETE SET NULL,
                FOREIGN KEY (`division_id`) REFERENCES `division_master`(`division_id`) ON DELETE SET NULL,
                FOREIGN KEY (`department_id`) REFERENCES `department_master`(`department_id`) ON DELETE SET NULL,
                FOREIGN KEY (`section_id`) REFERENCES `section_master`(`section_id`) ON DELETE SET NULL,
                FOREIGN KEY (`position_id`) REFERENCES `position_master`(`position_id`) ON DELETE SET NULL,
                FOREIGN KEY (`operation_id`) REFERENCES `operation_master`(`operation_id`) ON DELETE SET NULL,
                FOREIGN KEY (`labour_cost_id`) REFERENCES `labour_cost_master`(`cost_id`) ON DELETE SET NULL,
                FOREIGN KEY (`hiring_type_id`) REFERENCES `hiring_type_master`(`hiring_type_id`) ON DELETE SET NULL,
                FOREIGN KEY (`customer_zone_id`) REFERENCES `customer_zone_master`(`zone_id`) ON DELETE SET NULL,
                FOREIGN KEY (`contribution_level_id`) REFERENCES `contribution_level_master`(`level_id`) ON DELETE SET NULL,
                FOREIGN KEY (`education_level_id`) REFERENCES `education_level_master`(`education_id`) ON DELETE SET NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
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
            
            "CREATE TABLE IF NOT EXISTS `locker_master` (
                `locker_id` INT AUTO_INCREMENT PRIMARY KEY,
                `locker_number` VARCHAR(20) UNIQUE NOT NULL,
                `status` ENUM('Available','Occupied','Maintenance') DEFAULT 'Available',
                `location` VARCHAR(100),
                `current_owner_id` VARCHAR(8),
                FOREIGN KEY (`current_owner_id`) REFERENCES `employees`(`employee_id`) ON DELETE SET NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS `locker_usage_history` (
                `history_id` INT AUTO_INCREMENT PRIMARY KEY,
                `locker_id` INT NOT NULL,
                `employee_id` VARCHAR(8) NOT NULL,
                `start_date` DATE NOT NULL,
                `end_date` DATE,
                FOREIGN KEY (`locker_id`) REFERENCES `locker_master`(`locker_id`) ON DELETE CASCADE,
                FOREIGN KEY (`employee_id`) REFERENCES `employees`(`employee_id`) ON DELETE CASCADE,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS `document_management` (
                `doc_id` INT AUTO_INCREMENT PRIMARY KEY,
                `file_name_custom` VARCHAR(255) NOT NULL,
                `file_path` VARCHAR(500) NOT NULL,
                `doc_type_id` INT NOT NULL,
                `uploaded_by` VARCHAR(8) NOT NULL,
                `upload_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`doc_type_id`) REFERENCES `doc_type_master`(`doc_type_id`) ON DELETE CASCADE,
                FOREIGN KEY (`uploaded_by`) REFERENCES `employees`(`employee_id`) ON DELETE CASCADE,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS `document_submissions` (
                `submission_id` INT AUTO_INCREMENT PRIMARY KEY,
                `employee_id` VARCHAR(8) NOT NULL,
                `service_category_id` INT NOT NULL,
                `service_type_id` INT NOT NULL,
                `document_path` VARCHAR(500),
                `notes` TEXT,
                `status` ENUM('New','In Progress','Complete','Cancelled') DEFAULT 'New',
                `handler_id` VARCHAR(8),
                `handler_remarks` TEXT,
                FOREIGN KEY (`employee_id`) REFERENCES `employees`(`employee_id`) ON DELETE CASCADE,
                FOREIGN KEY (`service_category_id`) REFERENCES `service_category_master`(`category_id`) ON DELETE CASCADE,
                FOREIGN KEY (`service_type_id`) REFERENCES `service_type_master`(`type_id`) ON DELETE CASCADE,
                FOREIGN KEY (`handler_id`) REFERENCES `employees`(`employee_id`) ON DELETE SET NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS `leave_requests` (
                `request_id` INT AUTO_INCREMENT PRIMARY KEY,
                `employee_id` VARCHAR(8) NOT NULL,
                `leave_type_id` INT,
                `start_date` DATE NOT NULL,
                `end_date` DATE NOT NULL,
                `total_days` INT NOT NULL,
                `reason` TEXT,
                `status` ENUM('New','In Progress','Complete','Cancelled') DEFAULT 'New',
                `handler_id` VARCHAR(8),
                `handler_remarks` TEXT,
                `satisfaction_score` INT CHECK (`satisfaction_score` BETWEEN 1 AND 5),
                `satisfaction_feedback` TEXT,
                FOREIGN KEY (`employee_id`) REFERENCES `employees`(`employee_id`) ON DELETE CASCADE,
                FOREIGN KEY (`leave_type_id`) REFERENCES `leave_type_master`(`leave_type_id`) ON DELETE SET NULL,
                FOREIGN KEY (`handler_id`) REFERENCES `employees`(`employee_id`) ON DELETE SET NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS `certificate_requests` (
                `request_id` INT AUTO_INCREMENT PRIMARY KEY,
                `employee_id` VARCHAR(8) NOT NULL,
                `certificate_types` TEXT,
                `certificate_no` VARCHAR(100),
                `purpose` TEXT,
                `status` ENUM('New','In Progress','Complete','Cancelled') DEFAULT 'New',
                `handler_id` VARCHAR(8),
                `handler_remarks` TEXT,
                `satisfaction_score` INT CHECK (`satisfaction_score` BETWEEN 1 AND 5),
                `satisfaction_feedback` TEXT,
                FOREIGN KEY (`employee_id`) REFERENCES `employees`(`employee_id`) ON DELETE CASCADE,
                FOREIGN KEY (`handler_id`) REFERENCES `employees`(`employee_id`) ON DELETE SET NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS `id_card_requests` (
                `request_id` INT AUTO_INCREMENT PRIMARY KEY,
                `employee_id` VARCHAR(8) NOT NULL,
                `reason` VARCHAR(255),
                `status` ENUM('New','In Progress','Complete','Cancelled') DEFAULT 'New',
                `handler_id` VARCHAR(8),
                `handler_remarks` TEXT,
                `satisfaction_score` INT CHECK (`satisfaction_score` BETWEEN 1 AND 5),
                `satisfaction_feedback` TEXT,
                FOREIGN KEY (`employee_id`) REFERENCES `employees`(`employee_id`) ON DELETE CASCADE,
                FOREIGN KEY (`handler_id`) REFERENCES `employees`(`employee_id`) ON DELETE SET NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS `shuttle_bus_requests` (
                `request_id` INT AUTO_INCREMENT PRIMARY KEY,
                `employee_id` VARCHAR(8) NOT NULL,
                `route` VARCHAR(255),
                `pickup_location` VARCHAR(255),
                `request_date` DATE NOT NULL,
                `status` ENUM('New','In Progress','Complete','Cancelled') DEFAULT 'New',
                `handler_id` VARCHAR(8),
                `handler_remarks` TEXT,
                `satisfaction_score` INT CHECK (`satisfaction_score` BETWEEN 1 AND 5),
                `satisfaction_feedback` TEXT,
                FOREIGN KEY (`employee_id`) REFERENCES `employees`(`employee_id`) ON DELETE CASCADE,
                FOREIGN KEY (`handler_id`) REFERENCES `employees`(`employee_id`) ON DELETE SET NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS `locker_usage_requests` (
                `request_id` INT AUTO_INCREMENT PRIMARY KEY,
                `employee_id` VARCHAR(8) NOT NULL,
                `preferred_location` VARCHAR(100),
                `reason` TEXT,
                `assigned_locker_id` INT,
                `status` ENUM('New','In Progress','Complete','Cancelled') DEFAULT 'New',
                `handler_id` VARCHAR(8),
                `handler_remarks` TEXT,
                `satisfaction_score` INT CHECK (`satisfaction_score` BETWEEN 1 AND 5),
                `satisfaction_feedback` TEXT,
                FOREIGN KEY (`employee_id`) REFERENCES `employees`(`employee_id`) ON DELETE CASCADE,
                FOREIGN KEY (`handler_id`) REFERENCES `employees`(`employee_id`) ON DELETE SET NULL,
                FOREIGN KEY (`assigned_locker_id`) REFERENCES `locker_master`(`locker_id`) ON DELETE SET NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS `supplies_requests` (
                `request_id` INT AUTO_INCREMENT PRIMARY KEY,
                `employee_id` VARCHAR(8) NOT NULL,
                `request_type` ENUM('Office Supplies','Work Equipment','Uniform','Safety Equipment') NOT NULL,
                `items_list` TEXT NOT NULL,
                `status` ENUM('New','In Progress','Complete','Cancelled') DEFAULT 'New',
                `handler_id` VARCHAR(8),
                `handler_remarks` TEXT,
                `satisfaction_score` INT CHECK (`satisfaction_score` BETWEEN 1 AND 5),
                `satisfaction_feedback` TEXT,
                FOREIGN KEY (`employee_id`) REFERENCES `employees`(`employee_id`) ON DELETE CASCADE,
                FOREIGN KEY (`handler_id`) REFERENCES `employees`(`employee_id`) ON DELETE SET NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            "CREATE TABLE IF NOT EXISTS `skill_test_requests` (
                `request_id` INT AUTO_INCREMENT PRIMARY KEY,
                `employee_id` VARCHAR(8) NOT NULL,
                `skill_area` VARCHAR(100) NOT NULL,
                `preferred_date` DATE,
                `status` ENUM('New','In Progress','Complete','Cancelled') DEFAULT 'New',
                `handler_id` VARCHAR(8),
                `handler_remarks` TEXT,
                `satisfaction_score` INT CHECK (`satisfaction_score` BETWEEN 1 AND 5),
                `satisfaction_feedback` TEXT,
                FOREIGN KEY (`employee_id`) REFERENCES `employees`(`employee_id`) ON DELETE CASCADE,
                FOREIGN KEY (`handler_id`) REFERENCES `employees`(`employee_id`) ON DELETE SET NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        ];
    }
    
    private function seedInitialData() {
        // Roles
        $this->conn->exec("INSERT INTO `roles` (`role_name`, `role_description`) VALUES
            ('Admin', 'Full access to all system functions'),
            ('Officer', 'Can view and edit operational data'),
            ('Employee', 'Can view own data and submit requests')
        ");
        
        // Prefix Master
        $this->conn->exec("INSERT INTO `prefix_master` (`prefix_name_th`, `prefix_name_en`, `prefix_name_my`) VALUES
            ('นาย', 'Mr.', 'ကို'),
            ('นาง', 'Mrs.', 'ဒေါ်'),
            ('นางสาว', 'Miss', 'မ'),
            ('ดร.', 'Dr.', 'ဒေါက်တာ')
        ");
        
        // Operation Master
        $this->conn->exec("INSERT INTO `operation_master` (`operation_name`) VALUES
            ('Production'), ('Quality Control'), ('Logistics'), ('Maintenance'), ('Administration')
        ");
        
        // Labour Cost Master - แบ่งกลุ่ม Cost
        $this->conn->exec("INSERT INTO `labour_cost_master` (`cost_name_th`, `cost_name_en`, `cost_name_my`) VALUES
            ('ตรง', 'Direct', 'တိုက်ရိုက်'),
            ('ทางอ้อม', 'Indirect', 'သွယ်ဝိုက်'),
            ('ฝ่ายบริหาร', 'Admin', 'စီမံခန့်ခွဲရေး'),
            ('ฝ่ายสนับสนุน', 'Support', 'ပံ့ပိုးကူညီရေး'),
            ('ฝ่ายการตลาด', 'Marketing', 'စျေးကွက်ရှာဖွေရေး')
        ");
        
        // Hiring Type Master
        $this->conn->exec("INSERT INTO `hiring_type_master` (`hiring_type_name_th`, `hiring_type_name_en`, `hiring_type_name_my`) VALUES
            ('พนักงานประจำ', 'Permanent', 'အမြဲတမ်း'),
            ('พนักงานชั่วคราว', 'Temporary', 'ယာယီ'),
            ('พนักงานสัญญาจ้าง', 'Contract', 'စာချုပ်'),
            ('พนักงานพาร์ทไทม์', 'Part-Time', 'အချိန်ပိုင်း')
        ");
        
        // Customer Zone Master
        $this->conn->exec("INSERT INTO `customer_zone_master` (`zone_name`) VALUES
            ('Zone A'), ('Zone B'), ('Zone C'), ('Zone D'), ('International')
        ");
        
        // Contribution Level Master
        $this->conn->exec("INSERT INTO `contribution_level_master` (`level_name`) VALUES
            ('High'), ('Medium'), ('Low'), ('Standard')
        ");
        
        // Leave Type Master
        $this->conn->exec("INSERT INTO `leave_type_master` (`leave_type_name_th`, `leave_type_name_en`, `leave_type_name_my`) VALUES
            ('ลาป่วย', 'Sick Leave', 'နာမကျန်းခွင့်'),
            ('ลากิจ', 'Personal Leave', 'ကိုယ်ရေးကိုယ်တာခွင့်'),
            ('ลาพักร้อน', 'Annual Leave', 'နှစ်ပတ်လည်ခွင့်'),
            ('ลาคลอด', 'Maternity Leave', 'မီးဖွားခွင့်'),
            ('ลาบวช', 'Ordination Leave', 'သံဃာဝင်ခွင့်'),
            ('ลาทหาร', 'Military Leave', 'စစ်မှုထမ်းခွင့်')
        ");
        
        // Education Level Master - 3 ภาษา
        $this->conn->exec("INSERT INTO `education_level_master` (`education_name_th`, `education_name_en`, `education_name_my`) VALUES
            ('ปริญญาเอก', 'Doctoral Degree (Ph.D.)', 'ပါရဂူဘွဲ့'),
            ('ปริญญาโท', 'Master Degree', 'မဟာဘွဲ့'),
            ('ปริญญาตรี', 'Bachelor Degree', 'ဘွဲ့ကြီး'),
            ('ปวส.', 'Diploma / Associate Degree', 'ဒီပလိုမာ'),
            ('ม.6 / ปวช.', 'High School / Vocational', 'အထက်တန်း'),
            ('ม.3', 'Secondary School', 'အလယ်တန်း'),
            ('ป.6', 'Primary School', 'မူလတန်း'),
            ('อื่นๆ', 'Other', 'အခြား')
        ");
        
        // Function, Division, Department, Section, Position Masters
        $this->conn->exec("INSERT INTO `function_master` (`function_name`) VALUES
            ('Administration'), ('Operations'), ('Finance'), ('Human Resources'), ('IT Support')
        ");
        
        $this->conn->exec("INSERT INTO `division_master` (`division_name`) VALUES
            ('Corporate Office'), ('Manufacturing'), ('Sales & Marketing'), ('Support Services')
        ");
        
        $this->conn->exec("INSERT INTO `department_master` (`department_name`, `division_id`) VALUES
            ('Executive Management', 1), ('Production', 2), ('Quality Control', 2), ('Sales', 3), ('HR Department', 1)
        ");
        
        $this->conn->exec("INSERT INTO `section_master` (`section_name`, `department_id`) VALUES
            ('General Affairs', 1), ('Production Line 1', 2), ('QA Lab', 3), ('Domestic Sales', 4), ('Recruitment', 5)
        ");
        
        $this->conn->exec("INSERT INTO `position_master` (`position_name`) VALUES
            ('Manager'), ('Supervisor'), ('Officer'), ('Technician'), ('Operator')
        ");
        
        $this->conn->exec("INSERT INTO `position_level_master` (`level_name`) VALUES
            ('C-Level'), ('Senior Management'), ('Middle Management'), ('Junior Management'), ('Staff Level'), ('Entry Level')
        ");
        
        // Service Categories and Types
        $this->conn->exec("INSERT INTO `service_category_master` (`category_name_th`, `category_name_en`, `category_name_my`) VALUES
            ('แบบฟอร์มการลา', 'Leave Form', 'ခွင့်လွှာ'),
            ('ปัญหาสแกนนิ้ว', 'Finger Scan Issue', 'လက်ဗွေစကင်ပြဿနာ'),
            ('เอกสารบัญชีธนาคาร', 'Bank Account Document', 'ဘဏ်အကောင့်စာရွက်'),
            ('บัตรประชาชน', 'ID Card', 'မှတ်ပုံတင်'),
            ('ใบอนุญาตออกจากสถานที่', 'Company Premises Exit Permit', 'ကုမ္ပဏီမှထွက်ခွင့်ပါမစ်'),
            ('ใบรับรอง', 'Certificate', 'လက်မှတ်')
        ");
        
        $this->conn->exec("INSERT INTO `service_type_master` (`type_name_th`, `type_name_en`, `type_name_my`) VALUES
            ('บุคคล', 'Individual/Person', 'တစ်ဦးချင်း'),
            ('แผนก/กลุ่ม', 'Department/Group', 'ဌာန/အဖွဲ့')
        ");
        
        // Document Types
        $this->conn->exec("INSERT INTO `doc_type_master` (`doc_type_name`) VALUES
            ('Company Policy'), ('Employee Handbook'), ('Training Materials'), ('Safety Procedures'), ('Forms & Templates')
        ");
        
        // Lockers
        $this->conn->exec("INSERT INTO `locker_master` (`locker_number`, `status`, `location`) VALUES
            ('L001', 'Available', 'Building A - Floor 1'), ('L002', 'Available', 'Building A - Floor 1'),
            ('L003', 'Available', 'Building A - Floor 2'), ('L004', 'Available', 'Building B - Floor 1'),
            ('L005', 'Available', 'Building B - Floor 2')
        ");
        
        // Company Info
        $this->conn->exec("INSERT INTO `company_info` (`company_name`, `phone`, `fax`, `address`, `representative_name`) VALUES
            ('Trax Inter Trade Co., Ltd.', '+66-2-xxx-xxxx', '+66-2-xxx-xxxx', '123 Business Street, Bangkok, Thailand', 'John Doe')
        ");
        
        // Sample Employees - รหัส 8 หลัก
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $officerPassword = password_hash('officer123', PASSWORD_DEFAULT);
        $employeePassword = password_hash('emp123', PASSWORD_DEFAULT);
        
        $this->conn->exec("INSERT INTO `employees` 
            (`employee_id`, `prefix_id`, `full_name_th`, `full_name_en`, `function_id`, `division_id`, `department_id`, `section_id`, 
            `operation_id`, `position_id`, `position_level`, `labour_cost_id`, `hiring_type_id`, `customer_zone_id`, `contribution_level_id`,
            `sex`, `nationality`, `birthday`, `age`, `education_level_id`, `phone_no`, `date_of_hire`, `year_of_service`, 
            `status`, `username`, `password`, `role_id`, `theme_mode`, `theme_color_preference`, `language_preference`) VALUES
            ('90000001', 1, 'สมชาย ใจดี', 'Somchai Jaidee', 1, 1, 1, 1, 5, 1, 'C-Level', 3, 1, 1, 1, 'Male', 'Thai', '1985-03-15', 39, 2, '081-111-1111', '2010-01-15', 15, 'Active', 'admin', '$adminPassword', 1, 'light', '#3B82F6', 'en'),
            ('90000002', 2, 'สมหญิง รักงาน', 'Somying Rakngaan', 4, 1, 5, 5, 5, 2, 'Middle Management', 3, 1, 1, 1, 'Female', 'Thai', '1990-07-20', 34, 2, '081-222-2222', '2015-06-01', 9, 'Active', 'officer1', '$officerPassword', 2, 'light', '#10B981', 'th'),
            ('90000003', 1, 'จอห์น สมิธ', 'John Smith', 2, 2, 2, 2, 1, 3, 'Staff Level', 1, 1, 1, 2, 'Male', 'American', '1992-11-10', 32, 3, '081-333-3333', '2018-03-20', 6, 'Active', 'emp001', '$employeePassword', 3, 'light', '#F59E0B', 'en'),
            ('90000004', 3, 'มารี ต้น', 'Mary Htun', 2, 2, 3, 3, 2, 4, 'Staff Level', 2, 2, 2, 2, 'Female', 'Myanmar', '1995-05-25', 29, 4, '081-444-4444', '2020-08-15', 4, 'Active', 'emp002', '$employeePassword', 3, 'dark', '#EF4444', 'my'),
            ('90000005', 1, 'ประยุทธ์ ขยัน', 'Prayut Kayan', 3, 3, 4, 4, 3, 5, 'Entry Level', 1, 1, 3, 3, 'Male', 'Thai', '1988-12-05', 36, 5, '081-555-5555', '2012-10-01', 12, 'Active', 'emp003', '$employeePassword', 3, 'light', '#8B5CF6', 'th')
        ");
        
        // Localization Data
        $localizationData = [
            ['login', 'เข้าสู่ระบบ', 'Login', 'ဝင်ရောက်ရန်', 'auth'],
            ['username', 'ชื่อผู้ใช้', 'Username', 'အသုံးပြုသူအမည်', 'auth'],
            ['password', 'รหัสผ่าน', 'Password', 'လျှို့ဝှက်နံပါတ်', 'auth'],
            ['logout', 'ออกจากระบบ', 'Logout', 'ထွက်ရန်', 'auth'],
            ['dashboard', 'หน้าหลัก', 'Dashboard', 'ပင်မစာမျက်နှာ', 'menu'],
            ['welcome', 'ยินดีต้อนรับ', 'Welcome', 'ကြိုဆိုပါတယ်', 'general'],
            ['profile', 'ข้อมูลส่วนตัว', 'Profile', 'ကိုယ်ရေးအချက်အလက်', 'menu'],
            ['employees', 'พนักงาน', 'Employees', 'ဝန်ထမ်းများ', 'menu'],
            ['employee_id', 'รหัสพนักงาน', 'Employee ID', 'ဝန်ထမ်းနံပါတ်', 'field'],
            ['full_name', 'ชื่อ-นามสกุล', 'Full Name', 'အမည်အပြည့်အစုံ', 'field'],
            ['position', 'ตำแหน่ง', 'Position', 'ရာထူး', 'field'],
            ['department', 'แผนก', 'Department', 'ဌာန', 'field'],
            ['requests', 'คำขอ', 'Requests', 'တောင်းဆိုချက်များ', 'menu'],
            ['my_requests', 'คำขอของฉัน', 'My Requests', 'ကျွန်ုပ်၏တောင်းဆိုချက်များ', 'menu'],
            ['submit', 'ส่ง', 'Submit', 'တင်သွင်းရန်', 'action'],
            ['cancel', 'ยกเลิก', 'Cancel', 'ပယ်ဖျက်ရန်', 'action'],
            ['save', 'บันทึก', 'Save', 'သိမ်းဆည်းမည်', 'action'],
            ['edit', 'แก้ไข', 'Edit', 'တည်းဖြတ်မည်', 'action'],
            ['delete', 'ลบ', 'Delete', 'ဖျက်မည်', 'action'],
            ['search', 'ค้นหา', 'Search', 'ရှာဖွေမည်', 'action'],
            ['status', 'สถานะ', 'Status', 'အခြေအနေ', 'field'],
            ['documents', 'เอกสาร', 'Documents', 'စာရွက်စာတမ်းများ', 'menu'],
            ['document_submit', 'ส่งเอกสาร', 'Document Submit', 'စာရွက်တင်သွင်းရန်', 'menu'],
            ['document_management', 'จัดการเอกสาร', 'Document Management', 'စာရွက်စီမံခန့်ခွဲမှု', 'menu'],
            ['settings', 'ตั้งค่า', 'Settings', 'ချိန်ညှိမှုများ', 'menu'],
            ['master_data', 'ข้อมูลหลัก', 'Master Data', 'အခြေခံအချက်အလက်', 'menu'],
            ['manage_master_data', 'จัดการข้อมูลหลัก', 'Manage Master Data', 'အခြေခံအချက်အလက်စီမံခန့်ခွဲရန်', 'menu'],
            ['localization', 'จัดการภาษา', 'Localization', 'ဘာသာစကားစီမံခန့်ခွဲမှု', 'menu']
        ];
        
        $stmt = $this->conn->prepare("INSERT INTO `localization_master` (`key_id`, `th_text`, `en_text`, `my_text`, `category`) VALUES (?, ?, ?, ?, ?)");
        foreach ($localizationData as $data) {
            $stmt->execute($data);
        }
    }
    
    public function query($sql, $params = []) {
        try {
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