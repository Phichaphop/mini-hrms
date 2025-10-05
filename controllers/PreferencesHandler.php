<?php
// /controllers/PreferencesHandler.php
// Handle user preference updates (language, theme color)

session_start();
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../db/Database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$action = $_POST['action'] ?? '';
$userId = $_SESSION['user_id'];
$db = Database::getInstance();

try {
    if ($action === 'update_language') {
        $language = $_POST['language'] ?? '';
        
        if (!in_array($language, ['th', 'en', 'my'])) {
            throw new Exception('Invalid language');
        }
        
        $sql = "UPDATE employees SET language_preference = ? WHERE employee_id = ?";
        $db->query($sql, [$language, $userId]);
        
        $_SESSION['user_language'] = $language;
        
        echo json_encode(['success' => true, 'message' => 'Language updated successfully']);
        
    } elseif ($action === 'update_theme') {
        $color = $_POST['color'] ?? '';
        
        if (!preg_match('/^#[0-9A-F]{6}$/i', $color)) {
            throw new Exception('Invalid color format');
        }
        
        $sql = "UPDATE employees SET theme_color_preference = ? WHERE employee_id = ?";
        $db->query($sql, [$color, $userId]);
        
        $_SESSION['theme_color'] = $color;
        
        echo json_encode(['success' => true, 'message' => 'Theme color updated successfully']);
        
    } else {
        throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>