<?php
// /views/admin/db_manager.php
// Database Management Interface (Super Admin Access)

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../db/Database.php';

$db = Database::getInstance();
$message = null;
$messageType = 'success';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify super admin code
    $inputCode = $_POST['admin_code'] ?? '';
    
    if ($inputCode !== SUPER_ADMIN_CODE) {
        $message = 'Invalid Super Admin Security Code!';
        $messageType = 'error';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'create_database':
                $result = $db->createDatabase();
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
                break;
                
            case 'drop_database':
                $result = $db->dropDatabase();
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
                break;
                
            case 'create_tables':
                $result = $db->createAllTables();
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
                break;
                
            case 'drop_tables':
                $result = $db->dropAllTables();
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
                break;
        }
    }
}

// Check database status
$dbExists = false;
$tablesExist = false;
try {
    $dbExists = $db->databaseExists();
    if ($dbExists) {
        $db->getConnection()->exec("USE `" . DB_NAME . "`");
        $stmt = $db->getConnection()->query("SHOW TABLES");
        $tablesExist = $stmt->rowCount() > 0;
    }
} catch (Exception $e) {
    // Ignore
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Manager - Mini HRMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen p-4 md:p-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Database Manager</h1>
                    <p class="text-gray-600 mt-1">Super Admin Access Required</p>
                </div>
                <a href="/index.php" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">
                    Back to Login
                </a>
            </div>
        </div>
        
        <!-- Alert Messages -->
        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500'; ?>">
                <p class="<?php echo $messageType === 'success' ? 'text-green-700' : 'text-red-700'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </p>
            </div>
        <?php endif; ?>
        
        <!-- Status Cards -->
        <div class="grid md:grid-cols-2 gap-6 mb-6">
            <!-- Database Status -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Database Status</h2>
                    <span class="px-3 py-1 rounded-full text-sm font-medium <?php echo $dbExists ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                        <?php echo $dbExists ? 'EXISTS' : 'NOT FOUND'; ?>
                    </span>
                </div>
                <div class="space-y-2 text-sm text-gray-600">
                    <p><strong>Server:</strong> <?php echo DB_SERVER; ?></p>
                    <p><strong>Database:</strong> <?php echo DB_NAME; ?></p>
                    <p><strong>User:</strong> <?php echo DB_USER; ?></p>
                </div>
            </div>
            
            <!-- Tables Status -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Tables Status</h2>
                    <span class="px-3 py-1 rounded-full text-sm font-medium <?php echo $tablesExist ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                        <?php echo $tablesExist ? 'CREATED' : 'EMPTY'; ?>
                    </span>
                </div>
                <p class="text-sm text-gray-600">
                    <?php if ($tablesExist): ?>
                        All required tables have been created and seeded with initial data.
                    <?php else: ?>
                        No tables found. Please create tables to initialize the system.
                    <?php endif; ?>
                </p>
            </div>
        </div>
        
        <!-- Warning Notice -->
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-yellow-800">⚠️ Critical Operations</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>These operations will permanently modify your database. Please ensure you understand the consequences:</p>
                        <ul class="list-disc list-inside mt-2 space-y-1">
                            <li><strong>Drop Database:</strong> Deletes the entire database and ALL data</li>
                            <li><strong>Drop Tables:</strong> Removes all tables and data but keeps the database</li>
                            <li><strong>Create Tables:</strong> Creates all tables and seeds initial demo data</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Management Operations -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Database Operations</h2>
            
            <form method="POST" id="dbForm" class="space-y-6">
                <!-- Security Code Input -->
                <div>
                    <label for="admin_code" class="block text-sm font-medium text-gray-700 mb-2">
                        Super Admin Security Code <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                           id="admin_code" 
                           name="admin_code" 
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Enter security code to proceed">
                    <p class="mt-1 text-xs text-gray-500">
                        This code is defined in /config/db_config.php (SUPER_ADMIN_CODE)
                    </p>
                </div>
                
                <!-- Operation Buttons -->
                <div class="grid md:grid-cols-2 gap-4">
                    <!-- Create Database -->
                    <div class="border border-green-200 rounded-lg p-4 bg-green-50">
                        <h3 class="font-semibold text-green-800 mb-2">Create Database</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Creates a new database named "<?php echo DB_NAME; ?>"
                        </p>
                        <button type="submit" 
                                name="action" 
                                value="create_database"
                                <?php echo $dbExists ? 'disabled' : ''; ?>
                                onclick="return confirmAction('create database')"
                                class="w-full bg-green-500 text-white py-2 rounded-lg hover:bg-green-600 transition disabled:bg-gray-300 disabled:cursor-not-allowed">
                            <?php echo $dbExists ? '✓ Database Exists' : 'Create Database'; ?>
                        </button>
                    </div>
                    
                    <!-- Create Tables -->
                    <div class="border border-blue-200 rounded-lg p-4 bg-blue-50">
                        <h3 class="font-semibold text-blue-800 mb-2">Create All Tables</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Creates all required tables with seed data
                        </p>
                        <button type="submit" 
                                name="action" 
                                value="create_tables"
                                <?php echo !$dbExists ? 'disabled' : ''; ?>
                                onclick="return confirmAction('create all tables and seed data')"
                                class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition disabled:bg-gray-300 disabled:cursor-not-allowed">
                            Create All Tables
                        </button>
                    </div>
                    
                    <!-- Drop Tables -->
                    <div class="border border-orange-200 rounded-lg p-4 bg-orange-50">
                        <h3 class="font-semibold text-orange-800 mb-2">Drop All Tables</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Removes all tables (keeps database)
                        </p>
                        <button type="submit" 
                                name="action" 
                                value="drop_tables"
                                <?php echo !$tablesExist ? 'disabled' : ''; ?>
                                onclick="return confirmAction('DROP ALL TABLES', true)"
                                class="w-full bg-orange-500 text-white py-2 rounded-lg hover:bg-orange-600 transition disabled:bg-gray-300 disabled:cursor-not-allowed">
                            Drop All Tables
                        </button>
                    </div>
                    
                    <!-- Drop Database -->
                    <div class="border border-red-200 rounded-lg p-4 bg-red-50">
                        <h3 class="font-semibold text-red-800 mb-2">Drop Database</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            ⚠️ DANGER: Deletes entire database
                        </p>
                        <button type="submit" 
                                name="action" 
                                value="drop_database"
                                <?php echo !$dbExists ? 'disabled' : ''; ?>
                                onclick="return confirmAction('DROP THE ENTIRE DATABASE', true)"
                                class="w-full bg-red-500 text-white py-2 rounded-lg hover:bg-red-600 transition disabled:bg-gray-300 disabled:cursor-not-allowed">
                            Drop Database
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Quick Setup Guide -->
        <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Quick Setup Guide</h2>
            <div class="space-y-3 text-sm">
                <div class="flex items-start">
                    <span class="flex-shrink-0 w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold mr-3">1</span>
                    <div>
                        <p class="font-semibold">Enter Super Admin Security Code</p>
                        <p class="text-gray-600">Enter the security code defined in your config file</p>
                    </div>
                </div>
                <div class="flex items-start">
                    <span class="flex-shrink-0 w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold mr-3">2</span>
                    <div>
                        <p class="font-semibold">Click "Create Database"</p>
                        <p class="text-gray-600">This creates the database: <?php echo DB_NAME; ?></p>
                    </div>
                </div>
                <div class="flex items-start">
                    <span class="flex-shrink-0 w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold mr-3">3</span>
                    <div>
                        <p class="font-semibold">Click "Create All Tables"</p>
                        <p class="text-gray-600">Creates all tables and seeds with 5-15 demo records</p>
                    </div>
                </div>
                <div class="flex items-start">
                    <span class="flex-shrink-0 w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center font-bold mr-3">✓</span>
                    <div>
                        <p class="font-semibold">System Ready!</p>
                        <p class="text-gray-600">Return to login page and use demo credentials to access the system</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-8 text-gray-500 text-sm">
            <p>Mini HRMS Database Manager v1.0</p>
            <p class="mt-1">Trax Inter Trade Co., Ltd. &copy; 2025</p>
        </div>
    </div>

    <script>
        function confirmAction(actionName, isDangerous = false) {
            const adminCode = document.getElementById('admin_code').value;
            
            if (!adminCode) {
                alert('Please enter the Super Admin Security Code first!');
                return false;
            }
            
            let message = `Are you sure you want to ${actionName}?`;
            
            if (isDangerous) {
                message = `⚠️ DANGER: This will ${actionName}!\n\nThis action CANNOT be undone and will result in DATA LOSS.\n\nAre you absolutely sure?`;
            }
            
            return confirm(message);
        }
        
        // Add validation to form
        document.getElementById('dbForm').addEventListener('submit', function(e) {
            const adminCode = document.getElementById('admin_code').value.trim();
            
            if (adminCode.length < 3) {
                e.preventDefault();
                alert('Security code is too short!');
                return false;
            }
        });
    </script>
</body>
</html>