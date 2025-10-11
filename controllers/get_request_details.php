<?php
// /controllers/get_request_details.php - COMPLETE SECURE VERSION
// Get detailed information about a specific request

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../db/Database.php';

// Set JSON header
header('Content-Type: application/json');

// Enable error logging (but not display)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Log function for debugging
function logDebug($message) {
    error_log('[get_request_details] ' . $message);
}

try {
    logDebug('Request started');
    
    // Check authentication
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        logDebug('Authentication failed - no user_id in session');
        echo json_encode([
            'success' => false, 
            'message' => 'Authentication required. Please login again.'
        ]);
        exit();
    }
    
    // Get and validate parameters
    $table = $_GET['table'] ?? '';
    $id = $_GET['id'] ?? '';
    
    logDebug("Parameters received - table: $table, id: $id");
    
    // Validate table name (security check)
    $allowedTables = [
        'leave_requests',
        'certificate_requests',
        'id_card_requests',
        'shuttle_bus_requests',
        'locker_usage_requests',
        'supplies_requests',
        'skill_test_requests',
        'document_submissions'
    ];
    
    if (!in_array($table, $allowedTables)) {
        logDebug("Invalid table name: $table");
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid request type'
        ]);
        exit();
    }
    
    // Validate request ID
    if (empty($id) || !is_numeric($id)) {
        logDebug("Invalid request ID: $id");
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid request ID'
        ]);
        exit();
    }
    
    // Get database instance
    $db = Database::getInstance();
    
    // Build secure SQL query with parameterized statement
    $sql = "SELECT * FROM `$table` WHERE request_id = ? AND employee_id = ?";
    
    logDebug("Executing query: $sql with params [$id, {$_SESSION['user_id']}]");
    
    // Execute query
    $data = $db->fetchOne($sql, [$id, $_SESSION['user_id']]);
    
    if ($data) {
        logDebug("Request found successfully");
        
        // Format dates for better display
        if (isset($data['created_at'])) {
            $data['created_at_formatted'] = date('M d, Y H:i', strtotime($data['created_at']));
        }
        
        if (isset($data['start_date'])) {
            $data['start_date_formatted'] = date('M d, Y', strtotime($data['start_date']));
        }
        
        if (isset($data['end_date'])) {
            $data['end_date_formatted'] = date('M d, Y', strtotime($data['end_date']));
        }
        
        // Parse JSON fields if they exist
        if (isset($data['certificate_types']) && is_string($data['certificate_types'])) {
            $decoded = json_decode($data['certificate_types'], true);
            if (is_array($decoded)) {
                $data['certificate_types'] = implode(', ', $decoded);
            }
        }
        
        echo json_encode([
            'success' => true,
            'data' => $data,
            'table' => $table
        ]);
        
    } else {
        logDebug("Request not found or access denied");
        echo json_encode([
            'success' => false, 
            'message' => 'Request not found or you do not have permission to view it'
        ]);
    }
    
} catch (PDOException $e) {
    logDebug("Database error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error occurred. Please try again later.'
    ]);
    
} catch (Exception $e) {
    logDebug("General error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}

logDebug('Request completed');
?>