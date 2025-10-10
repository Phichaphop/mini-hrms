<?php
// /controllers/ajax_handler.php
// AJAX Handler for various requests

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../db/Database.php';

header('Content-Type: application/json');

$db = Database::getInstance();
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_employee_info':
            $employeeId = $_GET['employee_id'] ?? '';
            
            if (empty($employeeId)) {
                echo json_encode(['success' => false, 'message' => 'Employee ID required']);
                exit;
            }
            
            $sql = "SELECT e.*, 
                    p.position_name,
                    d.department_name
                    FROM employees e
                    LEFT JOIN position_master p ON e.position_id = p.position_id
                    LEFT JOIN department_master d ON e.department_id = d.department_id
                    WHERE e.employee_id = ? AND e.status = 'Active'";
            
            $employee = $db->fetchOne($sql, [$employeeId]);
            
            if ($employee) {
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'employee_id' => $employee['employee_id'],
                        'full_name_en' => $employee['full_name_en'] ?? 'N/A',
                        'full_name_th' => $employee['full_name_th'] ?? 'N/A',
                        'position_name' => $employee['position_name'] ?? 'N/A',
                        'department_name' => $employee['department_name'] ?? 'N/A'
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Employee not found']);
            }
            break;
            
        case 'get_submission_details':
            $submissionId = $_GET['submission_id'] ?? '';
            
            if (empty($submissionId)) {
                echo json_encode(['success' => false, 'message' => 'Submission ID required']);
                exit;
            }
            
            $sql = "SELECT ds.*, 
                    e.employee_id, e.full_name_en,
                    sc.category_name_en,
                    st.type_name_en,
                    h.full_name_en as handler_name
                    FROM document_submissions ds
                    LEFT JOIN employees e ON ds.employee_id = e.employee_id
                    LEFT JOIN service_category_master sc ON ds.service_category_id = sc.category_id
                    LEFT JOIN service_type_master st ON ds.service_type_id = st.type_id
                    LEFT JOIN employees h ON ds.handler_id = h.employee_id
                    WHERE ds.submission_id = ?";
            
            $submission = $db->fetchOne($sql, [$submissionId]);
            
            if ($submission) {
                echo json_encode(['success' => true, 'data' => $submission]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Submission not found']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>