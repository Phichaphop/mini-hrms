<?php
// /controllers/check_pending_rating.php
// Check if user has pending satisfaction ratings

session_start();
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../db/Database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

try {
    $db = Database::getInstance();
    $userId = $_SESSION['user_id'];
    
    // Check all request types for completed requests without ratings
    $tables = [
        'leave_requests' => 'Leave Request',
        'certificate_requests' => 'Certificate Request',
        'id_card_requests' => 'ID Card Request',
        'shuttle_bus_requests' => 'Shuttle Bus Request',
        'locker_usage_requests' => 'Locker Request',
        'supplies_requests' => 'Supplies Request',
        'skill_test_requests' => 'Skill Test Request'
    ];
    
    $pendingRatings = [];
    
    foreach ($tables as $table => $name) {
        $sql = "SELECT request_id, created_at FROM $table 
                WHERE employee_id = ? 
                AND status = 'Complete' 
                AND (satisfaction_score IS NULL OR satisfaction_score = 0)
                ORDER BY created_at DESC
                LIMIT 1";
        
        $result = $db->fetchOne($sql, [$userId]);
        
        if ($result) {
            $pendingRatings[] = [
                'table' => $table,
                'type' => $name,
                'request_id' => $result['request_id'],
                'created_at' => $result['created_at']
            ];
            break; // Only need to find one pending rating
        }
    }
    
    if (!empty($pendingRatings)) {
        echo json_encode([
            'success' => true,
            'has_pending' => true,
            'pending' => $pendingRatings[0]
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'has_pending' => false
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>