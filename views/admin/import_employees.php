<?php
// /views/admin/import_employees.php
// Import Employees from Excel

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../db/Database.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

$auth = new AuthController();
$auth->requireRole('Admin');

$pageTitle = 'Import Employees';
require_once __DIR__ . '/../layout/header.php';

$db = Database::getInstance();
$message = null;
$messageType = 'success';
$importResults = [];

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    try {
        if ($_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['excel_file']['tmp_name'];
            $fileName = $_FILES['excel_file']['name'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            if (!in_array($fileExt, ['xlsx', 'xls', 'csv'])) {
                throw new Exception('Invalid file type. Please upload Excel or CSV file.');
            }
            
            // Read file based on type
            if ($fileExt === 'csv') {
                $handle = fopen($tmpName, 'r');
                $headers = fgetcsv($handle);
                
                $success = 0;
                $failed = 0;
                $errors = [];
                
                while (($row = fgetcsv($handle)) !== false) {
                    try {
                        // Map CSV columns to database fields
                        $data = [
                            'employee_id' => $row[0] ?? '',
                            'full_name_en' => $row[1] ?? '',
                            'full_name_th' => $row[2] ?? '',
                            'username' => $row[3] ?? '',
                            'password' => $row[4] ?? 'password123', // Default password
                            'role_id' => $row[5] ?? 3,
                            'status' => 'Active'
                        ];
                        
                        if (empty($data['employee_id']) || empty($data['username'])) {
                            throw new Exception('Missing required fields');
                        }
                        
                        // Hash password
                        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
                        
                        // Insert into database
                        $sql = "INSERT INTO employees (employee_id, full_name_en, full_name_th, username, password, role_id, status) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $db->query($sql, [
                            $data['employee_id'],
                            $data['full_name_en'],
                            $data['full_name_th'],
                            $data['username'],
                            $hashedPassword,
                            $data['role_id'],
                            $data['status']
                        ]);
                        
                        $success++;
                    } catch (Exception $e) {
                        $failed++;
                        $errors[] = "Row " . ($success + $failed) . ": " . $e->getMessage();
                    }
                }
                
                fclose($handle);
                
                $importResults = [
                    'success' => $success,
                    'failed' => $failed,
                    'errors' => $errors
                ];
                
                $message = "Import completed: $success successful, $failed failed";
                $messageType = $failed > 0 ? 'warning' : 'success';
            }
        }
    } catch (Exception $e) {
        $message = 'Import failed: ' . $e->getMessage();
        $messageType = 'error';
    }
}
?>

<div class="max-w-6xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Import Employees</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Upload Excel/CSV file to import employee data</p>
    </div>
    
    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 border-l-4 border-green-500' : ($messageType === 'warning' ? 'bg-yellow-50 border-l-4 border-yellow-500' : 'bg-red-50 border-l-4 border-red-500'); ?>">
            <p class="<?php echo $messageType === 'success' ? 'text-green-700' : ($messageType === 'warning' ? 'text-yellow-700' : 'text-red-700'); ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
            <?php if (!empty($importResults['errors'])): ?>
                <details class="mt-2">
                    <summary class="cursor-pointer font-semibold">View Errors</summary>
                    <ul class="list-disc list-inside mt-2 text-sm">
                        <?php foreach ($importResults['errors'] as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </details>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Download Template -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 mb-6">
        <div class="flex items-start">
            <svg class="w-6 h-6 text-blue-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <h3 class="font-semibold text-blue-800 dark:text-blue-300">Download Template First</h3>
                <p class="text-sm text-blue-700 dark:text-blue-400 mt-1">Download the Excel template, fill in employee data, then upload it below.</p>
                <a href="<?php echo BASE_URL; ?>/assets/templates/employee_import_template.csv" 
                   download
                   class="inline-block mt-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm">
                    📥 Download CSV Template
                </a>
            </div>
        </div>
    </div>
    
    <!-- Upload Form -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <div>
                <label for="excel_file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Upload Excel/CSV File
                </label>
                <input type="file" 
                       id="excel_file" 
                       name="excel_file" 
                       accept=".xlsx,.xls,.csv"
                       required
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Supported formats: CSV, XLSX, XLS (Max 10MB)</p>
            </div>
            
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 p-4">
                <h4 class="font-semibold text-yellow-800 dark:text-yellow-300 mb-2">CSV Format Requirements:</h4>
                <ul class="list-disc list-inside text-sm text-yellow-700 dark:text-yellow-400 space-y-1">
                    <li>Column 1: Employee ID (required, 6 digits)</li>
                    <li>Column 2: Full Name English (required)</li>
                    <li>Column 3: Full Name Thai</li>
                    <li>Column 4: Username (required, unique)</li>
                    <li>Column 5: Password (optional, default: password123)</li>
                    <li>Column 6: Role ID (1=Admin, 2=Officer, 3=Employee)</li>
                </ul>
            </div>
            
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition font-semibold">
                    📤 Upload and Import
                </button>
                <a href="<?php echo BASE_URL; ?>/views/admin/employees.php" 
                   class="flex-1 bg-gray-500 text-white py-3 rounded-lg hover:bg-gray-600 transition font-semibold text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>