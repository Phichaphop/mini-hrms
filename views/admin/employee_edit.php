<?php
// /views/admin/employee_edit.php
// Edit Employee (Admin Only)

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../db/Database.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Employee.php';

$auth = new AuthController();
$auth->requireRole('Admin');

$pageTitle = 'Edit Employee';
require_once __DIR__ . '/../layout/header.php';

$employeeModel = new Employee();
$db = Database::getInstance();

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

$message = null;
$messageType = 'success';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'prefix_id' => $_POST['prefix_id'] ?? null,
        'full_name_th' => $_POST['full_name_th'] ?? '',
        'full_name_en' => $_POST['full_name_en'] ?? '',
        'function_id' => $_POST['function_id'] ?? null,
        'division_id' => $_POST['division_id'] ?? null,
        'department_id' => $_POST['department_id'] ?? null,
        'section_id' => $_POST['section_id'] ?? null,
        'position_id' => $_POST['position_id'] ?? null,
        'position_level' => $_POST['position_level'] ?? '',
        'sex' => $_POST['sex'] ?? '',
        'nationality' => $_POST['nationality'] ?? '',
        'birthday' => $_POST['birthday'] ?? null,
        'education_level' => $_POST['education_level'] ?? '',
        'phone_no' => $_POST['phone_no'] ?? '',
        'address_village' => $_POST['address_village'] ?? '',
        'address_subdistrict' => $_POST['address_subdistrict'] ?? '',
        'address_district' => $_POST['address_district'] ?? '',
        'address_province' => $_POST['address_province'] ?? '',
        'date_of_hire' => $_POST['date_of_hire'] ?? null,
        'username' => $_POST['username'] ?? '',
        'role_id' => $_POST['role_id'] ?? 3,
        'status' => $_POST['status'] ?? 'Active'
    ];
    
    // Calculate age if birthday provided
    if (!empty($data['birthday'])) {
        $birthDate = new DateTime($data['birthday']);
        $today = new DateTime();
        $data['age'] = $today->diff($birthDate)->y;
    }
    
    // Calculate years of service if hire date provided
    if (!empty($data['date_of_hire'])) {
        $hireDate = new DateTime($data['date_of_hire']);
        $today = new DateTime();
        $data['year_of_service'] = $today->diff($hireDate)->y;
    }
    
    // Only update password if provided
    if (!empty($_POST['password'])) {
        $data['password'] = $_POST['password'];
    }
    
    $result = $employeeModel->update($employeeId, $data);
    
    if ($result['success']) {
        $message = 'Employee updated successfully!';
        $messageType = 'success';
        $employee = $employeeModel->getById($employeeId);
    } else {
        $message = $result['message'];
        $messageType = 'error';
    }
}

// Get master data
$prefixes = $db->fetchAll("SELECT * FROM prefix_master ORDER BY prefix_id");
$functions = $db->fetchAll("SELECT * FROM function_master ORDER BY function_name");
$divisions = $db->fetchAll("SELECT * FROM division_master ORDER BY division_name");
$departments = $db->fetchAll("SELECT * FROM department_master ORDER BY department_name");
$sections = $db->fetchAll("SELECT * FROM section_master ORDER BY section_name");
$positions = $db->fetchAll("SELECT * FROM position_master ORDER BY position_name");
$roles = $db->fetchAll("SELECT * FROM roles ORDER BY role_id");
?>

<div class="max-w-6xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Edit Employee</h1>
        <p class="text-gray-600 mt-1">ID: <?php echo htmlspecialchars($employee['employee_id']); ?></p>
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
            <!-- Basic Information -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Employee ID</label>
                        <input type="text" value="<?php echo htmlspecialchars($employee['employee_id']); ?>" readonly class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-600">
                    </div>
                    
                    <div>
                        <label for="prefix_id" class="block text-sm font-medium text-gray-700 mb-2">Prefix</label>
                        <select id="prefix_id" name="prefix_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Prefix</option>
                            <?php foreach ($prefixes as $prefix): ?>
                                <option value="<?php echo $prefix['prefix_id']; ?>" <?php echo $employee['prefix_id'] == $prefix['prefix_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($prefix['prefix_name_en']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="sex" class="block text-sm font-medium text-gray-700 mb-2">Sex</label>
                        <select id="sex" name="sex" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Sex</option>
                            <option value="Male" <?php echo $employee['sex'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo $employee['sex'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo $employee['sex'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="full_name_en" class="block text-sm font-medium text-gray-700 mb-2">Full Name (English)</label>
                        <input type="text" id="full_name_en" name="full_name_en" value="<?php echo htmlspecialchars($employee['full_name_en']); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="full_name_th" class="block text-sm font-medium text-gray-700 mb-2">Full Name (Thai)</label>
                        <input type="text" id="full_name_th" name="full_name_th" value="<?php echo htmlspecialchars($employee['full_name_th'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>
            
            <!-- Organization (same as add form) -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Organization</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="function_id" class="block text-sm font-medium text-gray-700 mb-2">Function</label>
                        <select id="function_id" name="function_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Function</option>
                            <?php foreach ($functions as $func): ?>
                                <option value="<?php echo $func['function_id']; ?>" <?php echo $employee['function_id'] == $func['function_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($func['function_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="division_id" class="block text-sm font-medium text-gray-700 mb-2">Division</label>
                        <select id="division_id" name="division_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Division</option>
                            <?php foreach ($divisions as $div): ?>
                                <option value="<?php echo $div['division_id']; ?>" <?php echo $employee['division_id'] == $div['division_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($div['division_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="department_id" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                        <select id="department_id" name="department_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['department_id']; ?>" <?php echo $employee['department_id'] == $dept['department_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['department_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="section_id" class="block text-sm font-medium text-gray-700 mb-2">Section</label>
                        <select id="section_id" name="section_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Section</option>
                            <?php foreach ($sections as $sec): ?>
                                <option value="<?php echo $sec['section_id']; ?>" <?php echo $employee['section_id'] == $sec['section_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($sec['section_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="position_id" class="block text-sm font-medium text-gray-700 mb-2">Position</label>
                        <select id="position_id" name="position_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Position</option>
                            <?php foreach ($positions as $pos): ?>
                                <option value="<?php echo $pos['position_id']; ?>" <?php echo $employee['position_id'] == $pos['position_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($pos['position_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="position_level" class="block text-sm font-medium text-gray-700 mb-2">Position Level</label>
                        <input type="text" id="position_level" name="position_level" value="<?php echo htmlspecialchars($employee['position_level'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>
            
            <!-- Personal Information (similar structure) -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Personal Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="birthday" class="block text-sm font-medium text-gray-700 mb-2">Birthday</label>
                        <input type="date" id="birthday" name="birthday" value="<?php echo $employee['birthday']; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="nationality" class="block text-sm font-medium text-gray-700 mb-2">Nationality</label>
                        <input type="text" id="nationality" name="nationality" value="<?php echo htmlspecialchars($employee['nationality'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="education_level" class="block text-sm font-medium text-gray-700 mb-2">Education Level</label>
                        <input type="text" id="education_level" name="education_level" value="<?php echo htmlspecialchars($employee['education_level'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
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
                    
                    <div>
                        <label for="address_province" class="block text-sm font-medium text-gray-700 mb-2">Province</label>
                        <input type="text" id="address_province" name="address_province" value="<?php echo htmlspecialchars($employee['address_province'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>
            
            <!-- Employment -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Employment</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="date_of_hire" class="block text-sm font-medium text-gray-700 mb-2">Date of Hire</label>
                        <input type="date" id="date_of_hire" name="date_of_hire" value="<?php echo $employee['date_of_hire']; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select id="status" name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></select>
                        <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select id="status" name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="Active" <?php echo $employee['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                            <option value="Inactive" <?php echo $employee['status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                            <option value="Terminated" <?php echo $employee['status'] === 'Terminated' ? 'selected' : ''; ?>>Terminated</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="role_id" class="block text-sm font-medium text-gray-700 mb-2">System Role</label>
                        <select id="role_id" name="role_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role['role_id']; ?>" <?php echo $employee['role_id'] == $role['role_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($role['role_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Login Credentials -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Login Credentials</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($employee['username']); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                        <input type="password" id="password" name="password" minlength="6" placeholder="Leave blank to keep current password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Only fill if you want to change password</p>
                    </div>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="flex gap-3 pt-4 border-t">
                <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
                    Update Employee
                </button>
                <a href="<?php echo BASE_URL; ?>/views/admin/employees.php" class="flex-1 bg-gray-500 text-white py-3 rounded-lg hover:bg-gray-600 transition font-semibold text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>