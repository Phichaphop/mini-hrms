<?php
// /controllers/get_request_details.php

session_start();
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../db/Database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$type = $_GET['type'] ?? '';
$id = $_GET['id'] ?? '';

$tableMap = [
    'Leave' => 'leave_requests',
    'Certificate' => 'certificate_requests',
    'ID Card' => 'id_card_requests',
    'Shuttle Bus' => 'shuttle_bus_requests',
    'Locker' => 'locker_usage_requests',
    'Supplies' => 'supplies_requests',
    'Skill Test' => 'skill_test_requests',
    'Document Submission' => 'document_submissions'
];

if (!isset($tableMap[$type])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request type']);
    exit();
}

try {
    $db = Database::getInstance();
    $table = $tableMap[$type];
    
    $sql = "SELECT * FROM $table WHERE request_id = ? AND employee_id = ?";
    $data = $db->fetchOne($sql, [$id, $_SESSION['user_id']]);
    
    if ($data) {
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Request not found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>