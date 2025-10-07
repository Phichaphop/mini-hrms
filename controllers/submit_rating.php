<?php
// /controllers/submit_rating.php
// Submit satisfaction rating

session_start();
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../db/Database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

try {
    $db = Database::getInstance();
    
    $table = $_POST['table'] ?? '';
    $requestId = $_POST['request_id'] ?? '';
    $score = intval($_POST['score'] ?? 0);
    $feedback = $_POST['feedback'] ?? '';
    
    if (empty($table) || empty($requestId) || $score < 1 || $score > 5) {
        throw new Exception('Invalid rating data');
    }
    
    $sql = "UPDATE $table 
            SET satisfaction_score = ?, satisfaction_feedback = ? 
            WHERE request_id = ? AND employee_id = ?";
    
    $db->query($sql, [$score, $feedback, $requestId, $_SESSION['user_id']]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your feedback!'
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>