<?php
// /controllers/PreferencesHandler.php
// Handle user preference updates - FIXED SESSION ISSUE

// CRITICAL: Start session BEFORE any output
session_name('HRMS_SESSION'); // ใช้ชื่อเดียวกับใน AuthController
session_start();

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../db/Database.php';

// Set JSON header
header('Content-Type: application/json');

// Debug logging
function logDebug($message, $data = null) {
    $logFile = __DIR__ . '/../logs/preferences_debug.log';
    $logDir = dirname($logFile);
    
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $timestamp = date('[Y-m-d H:i:s]');
    $logMessage = "$timestamp $message";
    
    if ($data !== null) {
        $logMessage .= "\n" . print_r($data, true);
    }
    
    file_put_contents($logFile, $logMessage . "\n", FILE_APPEND);
}

// Log all request data
logDebug("=== NEW REQUEST ===");
logDebug("POST Data:", $_POST);
logDebug("Session Data:", $_SESSION);
logDebug("Session ID: " . session_id());

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logDebug("ERROR: Invalid request method");
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Check authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
    logDebug("ERROR: Not authenticated");
    logDebug("Session user_id: " . ($_SESSION['user_id'] ?? 'NOT SET'));
    logDebug("Session logged_in: " . ($_SESSION['logged_in'] ?? 'NOT SET'));
    
    echo json_encode([
        'success' => false, 
        'message' => 'Not authenticated. Please login again.',
        'debug' => [
            'session_id' => session_id(),
            'has_user_id' => isset($_SESSION['user_id']),
            'has_logged_in' => isset($_SESSION['logged_in'])
        ]
    ]);
    exit();
}

$action = $_POST['action'] ?? '';
$userId = $_SESSION['user_id'];

logDebug("User ID: $userId");
logDebug("Action: $action");

try {
    $db = Database::getInstance();
    
    if ($action === 'update_language') {
        $language = $_POST['language'] ?? '';
        
        logDebug("Updating language to: $language");
        
        // Validate language
        if (!in_array($language, ['th', 'en', 'my'])) {
            throw new Exception('Invalid language: ' . $language);
        }
        
        // Update database
        $sql = "UPDATE employees SET language_preference = ? WHERE employee_id = ?";
        $stmt = $db->query($sql, [$language, $userId]);
        
        // Update session
        $_SESSION['user_language'] = $language;
        
        logDebug("✅ Language updated successfully");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Language updated successfully',
            'language' => $language
        ]);
        
    } elseif ($action === 'update_theme') {
        $mode = $_POST['mode'] ?? '';
        
        logDebug("Updating theme to: $mode");
        
        // Validate theme mode
        if (!in_array($mode, ['light', 'dark'])) {
            throw new Exception('Invalid theme mode: ' . $mode);
        }
        
        // Update database
        $sql = "UPDATE employees SET theme_mode = ? WHERE employee_id = ?";
        $stmt = $db->query($sql, [$mode, $userId]);
        
        // Update session
        $_SESSION['theme_mode'] = $mode;
        
        logDebug("✅ Theme updated successfully");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Theme mode updated successfully',
            'mode' => $mode
        ]);
        
    } else {
        throw new Exception('Invalid action: ' . $action);
    }
    
} catch (Exception $e) {
    logDebug("❌ ERROR: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>