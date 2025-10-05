<?php
// /index.php
// Main Entry Point and Router

require_once __DIR__ . '/config/db_config.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/db/Database.php';

$auth = new AuthController();
$db = Database::getInstance();

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $result = $auth->login($username, $password);
    
    if ($result['success']) {
        header('Location: ' . BASE_URL . '/views/dashboard.php');
        exit();
    } else {
        $error = $result['message'];
        // Check if we need to redirect to DB manager
        if (isset($result['redirect'])) {
            header('Location: ' . $result['redirect']);
            exit();
        }
    }
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $auth->logout();
}

// If already logged in, redirect to dashboard
if ($auth->isLoggedIn()) {
    header('Location: ' . BASE_URL . '/views/dashboard.php');
    exit();
}

// Check if database exists
$dbExists = false;
try {
    $dbExists = $db->databaseExists();
} catch (Exception $e) {
    // Ignore error, will show setup message
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini HRMS - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-8 text-center">
                <h1 class="text-3xl font-bold text-white mb-2">Mini HRMS</h1>
                <p class="text-blue-100">Human Resource Management System</p>
            </div>
            
            <!-- Login Form -->
            <div class="p-8">
                <?php if (!$dbExists): ?>
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    Database not initialized. Please 
                                    <a href="<?php echo BASE_URL; ?>/views/admin/db_manager.php" class="font-medium underline hover:text-yellow-800">
                                        setup the database
                                    </a> first.
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                        <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['error']) && $_GET['error'] === 'login_required'): ?>
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                        <p class="text-sm text-blue-700">Please login to continue</p>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="loginForm">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="mb-6">
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                            Username
                        </label>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               required
                               class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                               placeholder="Enter your username">
                    </div>
                    
                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password
                        </label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               required
                               class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                               placeholder="Enter your password">
                    </div>
                    
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white font-semibold py-3 rounded-lg hover:from-blue-600 hover:to-purple-700 transition duration-200 shadow-lg">
                        Login
                    </button>
                </form>
                
                <div class="mt-6 text-center">
                    <a href="<?php echo BASE_URL; ?>/views/auth/forgot_password.php" class="text-sm text-blue-600 hover:text-blue-700">
                        Forgot Password?
                    </a>
                </div>
                
                <!-- Demo Credentials -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <p class="text-xs text-gray-500 text-center mb-3">Demo Credentials:</p>
                    <div class="space-y-2 text-xs">
                        <div class="bg-gray-50 p-2 rounded">
                            <strong>Admin:</strong> admin / admin123
                        </div>
                        <div class="bg-gray-50 p-2 rounded">
                            <strong>Officer:</strong> officer1 / officer123
                        </div>
                        <div class="bg-gray-50 p-2 rounded">
                            <strong>Employee:</strong> emp001 / emp123
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-6 text-white text-sm">
            <p>&copy; 2025 Trax Inter Trade Co., Ltd. All rights reserved.</p>
        </div>
    </div>

    <script>
        // Client-side validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            
            if (username.length < 3) {
                e.preventDefault();
                alert('Username must be at least 3 characters');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters');
                return false;
            }
        });
    </script>
</body>
</html>