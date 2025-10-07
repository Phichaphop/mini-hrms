<?php
// /views/employee/profile.php
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../db/Database.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Employee.php';

$auth = new AuthController();
$auth->requireLogin();

$pageTitle = 'My Profile';
require_once __DIR__ . '/../layout/header.php';

$employeeModel = new Employee();
$employee = $employeeModel->getById($_SESSION['user_id']);

$message = null;
$messageType = 'success';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'phone_no' => $_POST['phone_no'] ?? '',
        'address_village' => $_POST['address_village'] ?? '',
        'address_subdistrict' => $_POST['address_subdistrict'] ?? '',
        'address_district' => $_POST['address_district'] ?? '',
        'address_province' => $_POST['address_province'] ?? ''
    ];
    
    $result = $employeeModel->update($_SESSION['user_id'], $data);
    
    if ($result['success']) {
        $message = 'Profile updated successfully!';
        $messageType = 'success';
        $employee = $employeeModel->getById($_SESSION['user_id']);
    } else {
        $message = $result['message'];
        $messageType = 'error';
    }
}
?>

<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">My Profile</h1>
        <p class="text-gray-600 mt-1">View and edit your personal information</p>
    </div>
    
    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500'; ?>">
            <p class="<?php echo $messageType === 'success' ? 'text-green-700' : 'text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        </div>
    <?php endif; ?>
    
    <div class="bg-white rounded-lg shadow-lg p-6">
        <form method="POST" class="space-y-6">
            <!-- Personal Information (Read-only) -->
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <h3 class="font-semibold text-blue-900 mb-3">Personal Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Employee ID</label>
                        <input type="text" value="<?php echo htmlspecialchars($employee['employee_id']); ?>" readonly class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" value="<?php echo htmlspecialchars($employee['full_name_en']); ?>" readonly class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                        <input type="text" value="<?php echo htmlspecialchars($employee['position_name'] ?? 'N/A'); ?>" readonly class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                        <input type="text" value="<?php echo htmlspecialchars($employee['department_name'] ?? 'N/A'); ?>" readonly class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-600">
                    </div>
                </div>
            </div>
            
            <!-- Editable Contact Information -->
            <div>
                <h3 class="font-semibold text-gray-900 mb-3">Contact Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="phone_no" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                        <input type="tel" id="phone_no" name="phone_no" value="<?php echo htmlspecialchars($employee['phone_no'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="address_village" class="block text-sm font-medium text-gray-700 mb-2">Village/Address</label>
                        <input type="text" id="address_village" name="address_village" value="<?php echo htmlspecialchars($employee['address_village'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="address_subdistrict" class="block text-sm font-medium text-gray-700 mb-2">Sub-district</label>
                        <input type="text" id="address_subdistrict" name="address_subdistrict" value="<?php echo htmlspecialchars($employee['address_subdistrict'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="address_district" class="block text-sm font-medium text-gray-700 mb-2">District</label>
                        <input type="text" id="address_district" name="address_district" value="<?php echo htmlspecialchars($employee['address_district'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="address_province" class="block text-sm font-medium text-gray-700 mb-2">Province</label>
                        <input type="text" id="address_province" name="address_province" value="<?php echo htmlspecialchars($employee['address_province'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>
            
            <div class="flex gap-3 pt-4 border-t">
                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-3 rounded-lg hover:from-blue-700 hover:to-blue-800 transition font-semibold shadow-lg">
                    Save Changes
                </button>
                <a href="<?php echo BASE_URL; ?>/views/dashboard.php" class="w-full bg-gray-500 text-white py-3 rounded-lg hover:bg-gray-600 transition font-semibold text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>