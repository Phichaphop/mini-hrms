<?php
// index.php - ออกแบบใหม่ทั้งหมด
require_once __DIR__ . '/config/db_config.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/db/Database.php';

$auth = new AuthController();
$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $result = $auth->login($username, $password);
    
    if ($result['success']) {
        header('Location: ' . BASE_URL . '/views/dashboard.php');
        exit();
    } else {
        $error = $result['message'];
        if (isset($result['redirect'])) {
            header('Location: ' . $result['redirect']);
            exit();
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $auth->logout();
}

if ($auth->isLoggedIn()) {
    header('Location: ' . BASE_URL . '/views/dashboard.php');
    exit();
}

$dbExists = false;
try {
    $dbExists = $db->databaseExists();
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Service - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .input-field {
            transition: all 0.3s ease;
        }
        .input-field:focus {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body class="p-4">
    <div class="w-full max-w-md">
        <div class="login-card overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-8 text-center">
                <div class="w-20 h-20 bg-white rounded-full mx-auto mb-4 flex items-center justify-center">
                    <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">HR Service</h1>
                <p class="text-blue-100 text-sm">Human Resource Management System</p>
            </div>
            
            <!-- Login Form -->
            <div class="p-8">
                <?php if (!$dbExists): ?>
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded">
                        <p class="text-sm text-yellow-700">
                            Database not initialized. Please 
                            <a href="<?php echo BASE_URL; ?>/views/admin/db_manager.php" class="font-medium underline">
                                setup the database
                            </a> first.
                        </p>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded">
                        <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="loginForm" class="space-y-6">
                    <input type="hidden" name="action" value="login">
                    
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                            Username
                        </label>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               required
                               class="input-field w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none"
                               placeholder="Enter your username">
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password
                        </label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               required
                               class="input-field w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none"
                               placeholder="Enter your password">
                    </div>
                    
                    <button type="submit" 
                            class="btn-login w-full text-white font-semibold py-3 rounded-xl shadow-lg">
                        Sign In
                    </button>
                </form>
                
                <!-- Demo Credentials -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <p class="text-xs text-gray-500 text-center mb-3">Demo Credentials:</p>
                    <div class="space-y-2 text-xs">
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <strong>Admin:</strong> admin / admin123
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <strong>Officer:</strong> officer1 / officer123
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <strong>Employee:</strong> emp001 / emp123
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-6 text-white text-sm">
            <p>&copy; <?php echo date('Y'); ?> Trax Inter Trade Co., Ltd.</p>
        </div>
    </div>

    <script>
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