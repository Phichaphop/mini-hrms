<?php
// /views/admin/employees.php
// Employee Management (Admin/Officer Only)

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../db/Database.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Employee.php';

$auth = new AuthController();
$auth->requireRole(['Admin', 'Officer']);

$pageTitle = 'Employee Management';
require_once __DIR__ . '/../layout/header.php';

$employeeModel = new Employee();
$db = Database::getInstance();

$message = null;
$messageType = 'success';

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if ($auth->hasRole('Admin')) {
        $result = $employeeModel->delete($_GET['id']);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
    }
}

// Get all employees
$employees = $employeeModel->getAll();

// Get master data for filters
$departments = $db->fetchAll("SELECT * FROM department_master ORDER BY department_name");
$positions = $db->fetchAll("SELECT * FROM position_master ORDER BY position_name");
?>

<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Employee Management</h1>
            <p class="text-gray-600 mt-1">Manage employee information</p>
        </div>
        <?php if ($auth->hasRole('Admin')): ?>
        <a href="<?php echo BASE_URL; ?>/views/admin/employee_add.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
            + Add Employee
        </a>
        <?php endif; ?>
    </div>
    
    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500'; ?>">
            <p class="<?php echo $messageType === 'success' ? 'text-green-700' : 'text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        </div>
    <?php endif; ?>
    
    <!-- Search and Filter -->
    <div class="bg-white rounded-lg shadow-lg p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="text" 
                   name="search" 
                   placeholder="Search by ID, Name..." 
                   value="<?php echo $_GET['search'] ?? ''; ?>"
                   class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            
            <select name="department" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Departments</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?php echo $dept['department_id']; ?>" <?php echo (isset($_GET['department']) && $_GET['department'] == $dept['department_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($dept['department_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Status</option>
                <option value="Active" <?php echo (isset($_GET['status']) && $_GET['status'] === 'Active') ? 'selected' : ''; ?>>Active</option>
                <option value="Inactive" <?php echo (isset($_GET['status']) && $_GET['status'] === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                <option value="Terminated" <?php echo (isset($_GET['status']) && $_GET['status'] === 'Terminated') ? 'selected' : ''; ?>>Terminated</option>
            </select>
            
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                Search
            </button>
        </form>
    </div>
    
    <!-- Employee Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Position</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($employees)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                No employees found
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($employees as $emp): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($emp['employee_id']); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold mr-3">
                                            <?php echo strtoupper(substr($emp['full_name_en'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900"><?php echo htmlspecialchars($emp['full_name_en']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($emp['username']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?php echo htmlspecialchars($emp['position_name'] ?? 'N/A'); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?php echo htmlspecialchars($emp['department_name'] ?? 'N/A'); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium <?php 
                                        echo $emp['status'] === 'Active' ? 'bg-green-100 text-green-800' : 
                                            ($emp['status'] === 'Inactive' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                                    ?>">
                                        <?php echo htmlspecialchars($emp['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex space-x-2">
                                        <a href="<?php echo BASE_URL; ?>/views/admin/employee_view.php?id=<?php echo $emp['employee_id']; ?>" class="text-blue-600 hover:text-blue-800">View</a>
                                        <?php if ($auth->hasRole('Admin')): ?>
                                            <a href="<?php echo BASE_URL; ?>/views/admin/employee_edit.php?id=<?php echo $emp['employee_id']; ?>" class="text-green-600 hover:text-green-800">Edit</a>
                                            <a href="?action=delete&id=<?php echo $emp['employee_id']; ?>" 
                                               onclick="return confirm('Are you sure you want to delete this employee?')" 
                                               class="text-red-600 hover:text-red-800">Delete</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Stats Summary -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Total Employees</div>
            <div class="text-2xl font-bold text-gray-800"><?php echo count($employees); ?></div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Active</div>
            <div class="text-2xl font-bold text-green-600">
                <?php echo count(array_filter($employees, fn($e) => $e['status'] === 'Active')); ?>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Inactive</div>
            <div class="text-2xl font-bold text-yellow-600">
                <?php echo count(array_filter($employees, fn($e) => $e['status'] === 'Inactive')); ?>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Terminated</div>
            <div class="text-2xl font-bold text-red-600">
                <?php echo count(array_filter($employees, fn($e) => $e['status'] === 'Terminated')); ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>