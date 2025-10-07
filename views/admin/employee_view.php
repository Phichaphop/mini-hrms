<?php
// /views/admin/employee_view.php
// View Employee Details

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../db/Database.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Employee.php';

$auth = new AuthController();
$auth->requireRole(['Admin', 'Officer']);

$pageTitle = 'Employee Details';
require_once __DIR__ . '/../layout/header.php';

$employeeModel = new Employee();
$employeeId = $_GET['id'] ?? '';

if (empty($employeeId)) {
    header('Location: ' . BASE_URL . '/views/admin/employees.php');
    exit();
}

$employee = $employeeModel->getById($employeeId);

if (!$employee) {
    header('Location: ' . BASE_URL . '/views/admin/employees.php');
    exit();
}
?>

<div class="max-w-6xl mx-auto">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Employee Details</h1>
            <p class="text-gray-600 mt-1">ID: <?php echo htmlspecialchars($employee['employee_id']); ?></p>
        </div>
        <div class="flex gap-2">
            <?php if ($auth->hasRole('Admin')): ?>
                <a href="<?php echo BASE_URL; ?>/views/admin/employee_edit.php?id=<?php echo $employee['employee_id']; ?>" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    Edit Employee
                </a>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>/views/admin/employees.php" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">
                Back to List
            </a>
        </div>
    </div>
    
    <div class="space-y-6">
        <!-- Personal Information -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Personal Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500">Employee ID</label>
                    <p class="mt-1 text-gray-900 font-semibold"><?php echo htmlspecialchars($employee['employee_id']); ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Prefix</label>
                    <p class="mt-1 text-gray-900"><?php echo htmlspecialchars($employee['prefix_name_en'] ?? 'N/A'); ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Full Name (English)</label>
                    <p class="mt-1 text-gray-900"><?php echo htmlspecialchars($employee['full_name_en'] ?? 'N/A'); ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Full Name (Thai)</label>
                    <p class="mt-1 text-gray-900"><?php echo htmlspecialchars($employee['full_name_th'] ?? 'N/A'); ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Sex</label>
                    <p class="mt-1 text-gray-900"><?php echo htmlspecialchars($employee['sex'] ?? 'N/A'); ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Nationality</label>
                    <p class="mt-1 text-gray-900"><?php echo htmlspecialchars($employee['nationality'] ?? 'N/A'); ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Birthday</label>
                    <p class="mt-1 text-gray-900"><?php echo $employee['birthday'] ? date('M d, Y', strtotime($employee['birthday'])) : 'N/A'; ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Age</label>
                    <p class="mt-1 text-gray-900"><?php echo htmlspecialchars($employee['age'] ?? 'N/A'); ?> years</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Education Level</label>
                    <p class="mt-1 text-gray-900"><?php echo htmlspecialchars($employee['education_level'] ?? 'N/A'); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Organization Information -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Organization</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500">Function</label>
                    <p class="mt-1 text-gray-900"><?php echo htmlspecialchars($employee['function_name'] ?? 'N/A'); ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Division</label>
                    <p class="mt-1 text-gray-900"><?php echo htmlspecialchars($employee['division_name'] ?? 'N/A'); ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Department</label>
                    <p class="mt-1 text-gray-900"><?php echo htmlspecialchars($employee['department_name'] ?? 'N/A'); ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Section</label>
                    <p class="mt-1 text-gray-900"><?php echo htmlspecialchars($employee['section_name'] ?? 'N/A'); ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Position</label>
                    <p class="mt-1 text-gray-900"><?php echo htmlspecialchars($employee['position_name'] ?? 'N/A'); ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Position Level</label>
                    <p class="mt-1 text-gray-900"><?php echo htmlspecialchars($employee['position_level'] ?? 'N/A'); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Contact Information -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Contact Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500">Phone Number</label>
                    <p class="mt-1 text-gray-900"><?php echo htmlspecialchars($employee['phone_no'] ?? 'N/A'); ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Address</label>
                    <p class="mt-1 text-gray-900">
                        <?php 
                        $address = array_filter([
                            $employee['address_village'],
                            $employee['address_subdistrict'],
                            $employee['address_district'],
                            $employee['address_province']
                        ]);
                        echo !empty($address) ? implode(', ', $address) : 'None';
                        ?>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Employment Information -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Employment</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500">Date of Hire</label>
                    <p class="mt-1 text-gray-900"><?php echo $employee['date_of_hire'] ? date('M d, Y', strtotime($employee['date_of_hire'])) : 'N/A'; ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Years of Service</label>
                    <p class="mt-1 text-gray-900"><?php echo htmlspecialchars($employee['year_of_service'] ?? 'N/A'); ?> years</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Status</label>
                    <p class="mt-1">
                        <span class="px-3 py-1 rounded-full text-sm font-medium <?php 
                            echo $employee['status'] === 'Active' ? 'bg-green-100 text-green-800' : 
                                ($employee['status'] === 'Inactive' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                        ?>">
                            <?php echo htmlspecialchars($employee['status']); ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- System Information -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">System Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500">Username</label>
                    <p class="mt-1 text-gray-900"><?php echo htmlspecialchars($employee['username']); ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Role</label>
                    <p class="mt-1 text-gray-900"><?php echo htmlspecialchars($employee['role_name']); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>