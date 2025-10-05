<?php
// /models/Employee.php
// Employee Data Model

require_once __DIR__ . '/../db/Database.php';

class Employee {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all employees
     * @return array
     */
    public function getAll() {
        $sql = "SELECT e.*, 
                p.prefix_name_en,
                f.function_name,
                d.division_name,
                dep.department_name,
                s.section_name,
                pos.position_name,
                r.role_name
                FROM employees e
                LEFT JOIN prefix_master p ON e.prefix_id = p.prefix_id
                LEFT JOIN function_master f ON e.function_id = f.function_id
                LEFT JOIN division_master d ON e.division_id = d.division_id
                LEFT JOIN department_master dep ON e.department_id = dep.department_id
                LEFT JOIN section_master s ON e.section_id = s.section_id
                LEFT JOIN position_master pos ON e.position_id = pos.position_id
                LEFT JOIN roles r ON e.role_id = r.role_id
                ORDER BY e.employee_id";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get employee by ID
     * @param string $employeeId
     * @return array|null
     */
    public function getById($employeeId) {
        $sql = "SELECT e.*, 
                p.prefix_name_en, p.prefix_name_th, p.prefix_name_my,
                f.function_name,
                d.division_name,
                dep.department_name,
                s.section_name,
                pos.position_name,
                r.role_name
                FROM employees e
                LEFT JOIN prefix_master p ON e.prefix_id = p.prefix_id
                LEFT JOIN function_master f ON e.function_id = f.function_id
                LEFT JOIN division_master d ON e.division_id = d.division_id
                LEFT JOIN department_master dep ON e.department_id = dep.department_id
                LEFT JOIN section_master s ON e.section_id = s.section_id
                LEFT JOIN position_master pos ON e.position_id = pos.position_id
                LEFT JOIN roles r ON e.role_id = r.role_id
                WHERE e.employee_id = ?";
        
        return $this->db->fetchOne($sql, [$employeeId]);
    }
    
    /**
     * Create new employee
     * @param array $data
     * @return array
     */
    public function create($data) {
        try {
            // Validate required fields
            if (empty($data['employee_id']) || empty($data['username']) || empty($data['password'])) {
                return ['success' => false, 'message' => 'Required fields missing'];
            }
            
            // Check if employee ID already exists
            $existing = $this->getById($data['employee_id']);
            if ($existing) {
                return ['success' => false, 'message' => 'Employee ID already exists'];
            }
            
            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO employees (
                employee_id, prefix_id, full_name_th, full_name_en, function_id, 
                division_id, department_id, section_id, operation, position_id, 
                position_level, labour_cost, hiring_type, customer_zone, contribution_level,
                sex, nationality, birthday, age, education_level, phone_no,
                address_village, address_subdistrict, address_district, address_province,
                date_of_hire, year_of_service, status, username, password, role_id,
                theme_color_preference, language_preference
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )";
            
            $this->db->query($sql, [
                $data['employee_id'],
                $data['prefix_id'] ?? null,
                $data['full_name_th'] ?? null,
                $data['full_name_en'] ?? null,
                $data['function_id'] ?? null,
                $data['division_id'] ?? null,
                $data['department_id'] ?? null,
                $data['section_id'] ?? null,
                $data['operation'] ?? null,
                $data['position_id'] ?? null,
                $data['position_level'] ?? null,
                $data['labour_cost'] ?? null,
                $data['hiring_type'] ?? null,
                $data['customer_zone'] ?? null,
                $data['contribution_level'] ?? null,
                $data['sex'] ?? null,
                $data['nationality'] ?? null,
                $data['birthday'] ?? null,
                $data['age'] ?? null,
                $data['education_level'] ?? null,
                $data['phone_no'] ?? null,
                $data['address_village'] ?? null,
                $data['address_subdistrict'] ?? null,
                $data['address_district'] ?? null,
                $data['address_province'] ?? null,
                $data['date_of_hire'] ?? null,
                $data['year_of_service'] ?? null,
                $data['status'] ?? 'Active',
                $data['username'],
                $hashedPassword,
                $data['role_id'] ?? 3, // Default to Employee role
                $data['theme_color_preference'] ?? DEFAULT_THEME_COLOR,
                $data['language_preference'] ?? DEFAULT_LANGUAGE
            ]);
            
            return ['success' => true, 'message' => 'Employee created successfully'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to create employee: ' . $e->getMessage()];
        }
    }
    
    /**
     * Update employee
     * @param string $employeeId
     * @param array $data
     * @return array
     */
    public function update($employeeId, $data) {
        try {
            $fields = [];
            $values = [];
            
            // Build dynamic update query
            $allowedFields = [
                'prefix_id', 'full_name_th', 'full_name_en', 'function_id', 
                'division_id', 'department_id', 'section_id', 'operation', 'position_id',
                'position_level', 'labour_cost', 'hiring_type', 'customer_zone', 
                'contribution_level', 'sex', 'nationality', 'birthday', 'age',
                'education_level', 'phone_no', 'address_village', 'address_subdistrict',
                'address_district', 'address_province', 'date_of_hire', 'year_of_service',
                'date_of_termination', 'month_of_termination', 'status', 
                'reason_for_termination', 'suggestion', 'remark', 'username',
                'role_id', 'profile_pic_path'
            ];
            
            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $data)) {
                    $fields[] = "`$field` = ?";
                    $values[] = $data[$field];
                }
            }
            
            // Handle password separately
            if (!empty($data['password'])) {
                $fields[] = "`password` = ?";
                $values[] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            
            if (empty($fields)) {
                return ['success' => false, 'message' => 'No fields to update'];
            }
            
            $values[] = $employeeId;
            $sql = "UPDATE employees SET " . implode(', ', $fields) . " WHERE employee_id = ?";
            
            $this->db->query($sql, $values);
            
            return ['success' => true, 'message' => 'Employee updated successfully'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to update employee: ' . $e->getMessage()];
        }
    }
    
    /**
     * Delete employee
     * @param string $employeeId
     * @return array
     */
    public function delete($employeeId) {
        try {
            $sql = "DELETE FROM employees WHERE employee_id = ?";
            $this->db->query($sql, [$employeeId]);
            
            return ['success' => true, 'message' => 'Employee deleted successfully'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to delete employee: ' . $e->getMessage()];
        }
    }
    
    /**
     * Search employees
     * @param string $keyword
     * @return array
     */
    public function search($keyword) {
        $sql = "SELECT e.*, 
                p.prefix_name_en,
                f.function_name,
                d.division_name,
                dep.department_name,
                s.section_name,
                pos.position_name,
                r.role_name
                FROM employees e
                LEFT JOIN prefix_master p ON e.prefix_id = p.prefix_id
                LEFT JOIN function_master f ON e.function_id = f.function_id
                LEFT JOIN division_master d ON e.division_id = d.division_id
                LEFT JOIN department_master dep ON e.department_id = dep.department_id
                LEFT JOIN section_master s ON e.section_id = s.section_id
                LEFT JOIN position_master pos ON e.position_id = pos.position_id
                LEFT JOIN roles r ON e.role_id = r.role_id
                WHERE e.employee_id LIKE ? 
                   OR e.full_name_en LIKE ? 
                   OR e.full_name_th LIKE ?
                   OR e.username LIKE ?
                ORDER BY e.employee_id";
        
        $searchTerm = "%$keyword%";
        return $this->db->fetchAll($sql, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    }
    
    /**
     * Get employees for dropdown (ID and name only)
     * @return array
     */
    public function getAllForDropdown() {
        $sql = "SELECT employee_id, full_name_en, full_name_th FROM employees WHERE status = 'Active' ORDER BY employee_id";
        return $this->db->fetchAll($sql);
    }
}
?>