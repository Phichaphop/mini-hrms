<?php
// /views/admin/employee_add.php
// Add New Employee (Admin Only)

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../db/Database.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Employee.php';

$auth = new AuthController();
$auth->requireRole('Admin');

$pageTitle = 'Add Employee';
require_once __DIR__ . '/../layout/header.php';

$employeeModel = new Employee();
$db = Database::getInstance();

$message = null;
$messageType = 'success';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'employee_id' => $_POST['employee_id'] ?? '',
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
        'password' => $_POST['password'] ?? '',
        'role_id' => $_POST['role_id'] ?? 3,
        'status' => 'Active'
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
    
    $result = $employeeModel->create($data);
    
    if ($result['success']) {
        $message = 'Employee added successfully!';
        $messageType = 'success';
        // Redirect after 2 seconds
        header("refresh:2;url=" . BASE_URL . "/views/admin/employees.php");
    } else {
        $message = $result['message'];
        $messageType = 'error';
    }
}

// Get master data for dropdowns
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
        <h1 class="text-2xl font-bold text-gray-800">Add New Employee</h1>
        <p class="text-gray-600 mt-1">Fill in employee information</p>
    </div>
    
    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500'; ?>">
            <p class="<?php echo $messageType === 'success' ? 'text-green-700' : 'text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
            <?php if ($messageType === 'success'): ?>
                <p class="text-sm text-green-600 mt-1">Redirecting to employee list...</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="bg-white rounded-lg shadow-lg p-6">
        <form method="POST" id="employeeForm" class="space-y-6">
            <!-- Basic Information -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Employee ID <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="employee_id" name="employee_id" required maxlength="6" 
                               pattern="[0-9]{6}" placeholder="000000"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">6-digit number</p>
                    </div>
                    
                    <div>
                        <label for="prefix_id" class="block text-sm font-medium text-gray-700 mb-2">Prefix</label>
                        <select id="prefix_id" name="prefix_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Prefix</option>
                            <?php foreach ($prefixes as $prefix): ?>
                                <option value="<?php echo $prefix['prefix_id']; ?>">
                                    <?php echo htmlspecialchars($prefix['prefix_name_en']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="sex" class="block text-sm font-medium text-gray-700 mb-2">Sex</label>
                        <select id="sex" name="sex" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Sex</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="full_name_en" class="block text-sm font-medium text-gray-700 mb-2">
                            Full Name (English) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="full_name_en" name="full_name_en" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="full_name_th" class="block text-sm font-medium text-gray-700 mb-2">
                            Full Name (Thai)
                        </label>
                        <input type="text" id="full_name_th" name="full_name_th"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>
            
            <!-- Organization Information -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Organization</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="function_id" class="block text-sm font-medium text-gray-700 mb-2">Function</label>
                        <select id="function_id" name="function_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Function</option>
                            <?php foreach ($functions as $func): ?>
                                <option value="<?php echo $func['function_id']; ?>">
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
                                <option value="<?php echo $div['division_id']; ?>">
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
                                <option value="<?php echo $dept['department_id']; ?>">
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
                                <option value="<?php echo $sec['section_id']; ?>">
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
                                <option value="<?php echo $pos['position_id']; ?>">
                                    <?php echo htmlspecialchars($pos['position_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="position_level" class="block text-sm font-medium text-gray-700 mb-2">Position Level</label>
                        <input type="text" id="position_level" name="position_level"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>
            
            <!-- Personal Information -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Personal Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="birthday" class="block text-sm font-medium text-gray-700 mb-2">Birthday</label>
                        <input type="date" id="birthday" name="birthday"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="nationality" class="block text-sm font-medium text-gray-700 mb-2">Nationality</label>
                        <input type="text" id="nationality" name="nationality"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="education_level" class="block text-sm font-medium text-gray-700 mb-2">Education Level</label>
                        <input type="text" id="education_level" name="education_level"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="phone_no" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                        <input type="tel" id="phone_no" name="phone_no"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="address_village" class="block text-sm font-medium text-gray-700 mb-2">Village/Address</label>
                        <input type="text" id="address_village" name="address_village"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="address_subdistrict" class="block text-sm font-medium text-gray-700 mb-2">Sub-district</label>
                        <input type="text" id="address_subdistrict" name="address_subdistrict"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="address_district" class="block text-sm font-medium text-gray-700 mb-2">District</label>
                        <input type="text" id="address_district" name="address_district"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="address_province" class="block text-sm font-medium text-gray-700 mb-2">Province</label>
                        <input type="text" id="address_province" name="address_province"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>
            
            <!-- Employment Information -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Employment</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="date_of_hire" class="block text-sm font-medium text-gray-700 mb-2">Date of Hire</label>
                        <input type="date" id="date_of_hire" name="date_of_hire"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="role_id" class="block text-sm font-medium text-gray-700 mb-2">
                            System Role <span class="text-red-500">*</span>
                        </label>
                        <select id="role_id" name="role_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role['role_id']; ?>" <?php echo $role['role_id'] == 3 ? 'selected' : ''; ?>>
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
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                            Username <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="username" name="username" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="password" name="password" required minlength="6"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Minimum 6 characters</p>
                    </div>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="flex gap-3 pt-4 border-t">
                <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
                    Add Employee
                </button>
                <a href="<?php echo BASE_URL; ?>/views/admin/employees.php" class="flex-1 bg-gray-500 text-white py-3 rounded-lg hover:bg-gray-600 transition font-semibold text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    // Form validation
    document.getElementById('employeeForm').addEventListener('submit', function(e) {
        const employeeId = document.getElementById('employee_id').value;
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        
        if (employeeId.length !== 6 || !/^\d{6}$/.test(employeeId)) {
            e.preventDefault();
            alert('Employee ID must be exactly 6 digits!');
            return false;
        }
        
        if (username.length < 3) {
            e.preventDefault();
            alert('Username must be at least 3 characters!');
            return false;
        }
        
        if (password.length < 6) {
            e.preventDefault();
            alert('Password must be at least 6 characters!');
            return false;
        }
    });
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>