<?php
// /controllers/upload_profile_pic.php
// Handle profile picture upload

session_start();
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../db/Database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['profile_pic'])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit();
}

try {
    $file = $_FILES['profile_pic'];
    
    // Validate file
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Invalid file type. Only JPG, PNG, GIF allowed.');
    }
    
    if ($file['size'] > 2 * 1024 * 1024) { // 2MB
        throw new Exception('File too large. Maximum 2MB.');
    }
    
    // Create upload directory
    $uploadDir = __DIR__ . '/../assets/uploads/profiles/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Generate unique filename
    $fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFileName = $_SESSION['user_id'] . '_' . time() . '.' . $fileExt;
    $targetPath = $uploadDir . $newFileName;
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $filePath = '/assets/uploads/profiles/' . $newFileName;
        
        // Update database
        $db = Database::getInstance();
        $sql = "UPDATE employees SET profile_pic_path = ? WHERE employee_id = ?";
        $db->query($sql, [$filePath, $_SESSION['user_id']]);
        
        // Update session
        $_SESSION['profile_pic'] = $filePath;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Profile picture updated',
            'path' => BASE_URL . $filePath
        ]);
    } else {
        throw new Exception('Failed to upload file');
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>