<?php
// /controllers/PreferencesHandler.php
// FIXED VERSION - No HTML output before JSON

// ✅ CRITICAL: No spaces or output before this line
if (session_status() === PHP_SESSION_NONE) {
    session_name('HRMS_SESSION');
    session_start();
}

// ✅ Suppress ALL errors in output
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../db/Database.php';

// ✅ Clean output buffer
if (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/json');

// Log function (file only, no output)
function logToFile($msg) {
    $logDir = __DIR__ . '/../logs';
    if (!file_exists($logDir)) {
        @mkdir($logDir, 0777, true);
    }
    @file_put_contents($logDir . '/prefs.log', date('[Y-m-d H:i:s] ') . $msg . "\n", FILE_APPEND);
}

logToFile("=== REQUEST START ===");

// Check method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Check authentication
if (empty($_SESSION['user_id']) || empty($_SESSION['logged_in'])) {
    logToFile("ERROR: Not authenticated!");
    echo json_encode([
        'success' => false, 
        'message' => 'Session expired. Please login again.',
        'redirect' => true
    ]);
    exit();
}

$action = $_POST['action'] ?? '';
$userId = $_SESSION['user_id'];

try {
    $db = Database::getInstance();
    
    if ($action === 'update_language') {
        $language = $_POST['language'] ?? '';
        
        if (!in_array($language, ['th', 'en', 'my'])) {
            throw new Exception('Invalid language');
        }
        
        $sql = "UPDATE employees SET language_preference = ? WHERE employee_id = ?";
        $db->query($sql, [$language, $userId]);
        
        $_SESSION['user_language'] = $language;
        
        logToFile("✅ Language updated to: $language");
        
        echo json_encode(['success' => true, 'message' => 'Language updated']);
        
    } elseif ($action === 'update_theme') {
        $mode = $_POST['mode'] ?? '';
        
        if (!in_array($mode, ['light', 'dark'])) {
            throw new Exception('Invalid theme mode');
        }
        
        $sql = "UPDATE employees SET theme_mode = ? WHERE employee_id = ?";
        $db->query($sql, [$mode, $userId]);
        
        $_SESSION['theme_mode'] = $mode;
        
        logToFile("✅ Theme updated to: $mode");
        
        echo json_encode(['success' => true, 'message' => 'Theme updated']);
        
    } else {
        throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    logToFile("❌ ERROR: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

exit(); // ✅ Force exit, no further output
?>