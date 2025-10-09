<?php
// /views/admin/employee_edit.php - UPDATED VERSION
// Edit Employee Information - With Auto-Calculate Features

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../db/Database.php';
require_once __DIR__ . '/../../db/Localization.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Employee.php';
require_once __DIR__ . '/../../includes/dropdown_helper.php';

$auth = new AuthController();
$auth->requireRole(['Admin', 'Officer']);

$pageTitle = 'Edit Employee';
require_once __DIR__ . '/../layout/header.php';

$db = Database::getInstance();
$employeeModel = new Employee();

$employeeId = $_GET['id'] ?? null;
$message = null;
$messageType = 'success';

if (!$employeeId) {
    header('Location: ' . BASE_URL . '/views/admin/employees.php');
    exit();
}

// Get employee data
$employee = $db->fetchOne("SELECT * FROM employees WHERE employee_id = ?", [$employeeId]);

if (!$employee) {
    header('Location: ' . BASE_URL . '/views/admin/employees.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $updateData = [
            'prefix_id' => $_POST['prefix_id'] ?: null,
            'full_name_th' => $_POST['full_name_th'],
            'full_name_en' => $_POST['full_name_en'],
            'function_id' => $_POST['function_id'] ?: null,
            'division_id' => $_POST['division_id'] ?: null,
            'department_id' => $_POST['department_id'] ?: null,
            'section_id' => $_POST['section_id'] ?: null,
            'operation_id' => $_POST['operation_id'] ?: null,
            'position_id' => $_POST['position_id'] ?: null,
            'position_level' => $_POST['position_level'],
            'labour_cost_id' => $_POST['labour_cost_id'] ?: null,
            'hiring_type_id' => $_POST['hiring_type_id'] ?: null,
            'customer_zone_id' => $_POST['customer_zone_id'] ?: null,
            'contribution_level_id' => $_POST['contribution_level_id'] ?: null,
            'sex' => $_POST['sex'],
            'nationality' => $_POST['nationality'],
            'birthday' => $_POST['birthday'] ?: null,
            'age' => $_POST['age'] ?: null,
            'education_level_id' => $_POST['education_level_id'] ?: null,
            'phone_no' => $_POST['phone_no'],
            'address_village' => $_POST['address_village'],
            'address_subdistrict' => $_POST['address_subdistrict'],
            'address_district' => $_POST['address_district'],
            'address_province' => $_POST['address_province'],
            'date_of_hire' => $_POST['date_of_hire'] ?: null,
            'year_of_service' => $_POST['year_of_service'] ?: null,
            'date_of_termination' => $_POST['date_of_termination'] ?: null,
            'month_of_termination' => $_POST['month_of_termination'] ?: null,
            'status' => $_POST['status'],
            'reason_for_termination' => $_POST['reason_for_termination'],
            'suggestion' => $_POST['suggestion'],
            'remark' => $_POST['remark'],
            'username' => $_POST['username'],
            'role_id' => $_POST['role_id']
        ];
        
        // Update password only if provided
        if (!empty($_POST['password'])) {
            $updateData['password'] = $_POST['password'];
        }
        
        $result = $employeeModel->update($employeeId, $updateData);
        
        if ($result['success']) {
            $message = 'Employee updated successfully!';
            $messageType = 'success';
            // Refresh employee data
            $employee = $db->fetchOne("SELECT * FROM employees WHERE employee_id = ?", [$employeeId]);
        } else {
            $message = $result['message'];
            $messageType = 'error';
        }
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}
?>

<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Edit Employee</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Update employee information</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/views/admin/employees.php" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">
            ← Back to List
        </a>
    </div>
    
    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500' : 'bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500'; ?>">
            <p class="<?php echo $messageType === 'success' ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        </div>
    <?php endif; ?>
    
    <form method="POST" class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <!-- Personal Information -->
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                Personal Information
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="employee_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Employee ID <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="employee_id" 
                           value="<?php echo htmlspecialchars($employee['employee_id']); ?>" 
                           readonly 
                           class="w-full px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-600 dark:text-gray-400">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Cannot be changed</p>
                </div>
                
                <div>
                    <label for="prefix_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Prefix</label>
                    <select id="prefix_id" name="prefix_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <?php echo getPrefixOptions($db, $employee['prefix_id']); ?>
                    </select>
                </div>
                
                <div>
                    <label for="sex" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sex <span class="text-red-500">*</span></label>
                    <select id="sex" name="sex" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <?php echo getSexOptions($employee['sex']); ?>
                    </select>
                </div>
                
                <div class="md:col-span-2">
                    <label for="full_name_en" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Full Name (English) <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="full_name_en" name="full_name_en" value="<?php echo htmlspecialchars($employee['full_name_en']); ?>" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label for="nationality" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nationality</label>
                    <select id="nationality" name="nationality" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <?php echo getNationalityOptions($employee['nationality']); ?>
                    </select>
                </div>
                
                <div class="md:col-span-3">
                    <label for="full_name_th" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Full Name (Thai)</label>
                    <input type="text" id="full_name_th" name="full_name_th" value="<?php echo htmlspecialchars($employee['full_name_th']); ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label for="birthday" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Birthday</label>
                    <input type="date" id="birthday" name="birthday" value="<?php echo htmlspecialchars($employee['birthday']); ?>" onchange="calculateAge()" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label for="age" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Age (Auto)</label>
                    <input type="number" 
                           id="age" 
                           name="age" 
                           value="<?php echo htmlspecialchars($employee['age']); ?>" 
                           readonly 
                           class="w-full px-4 py-2 bg-gray-100 dark:bg-gray-600 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-600 dark:text-gray-300">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Auto-calculated from birthday</p>
                </div>
                
                <div>
                    <label for="education_level_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Education Level</label>
                    <select id="education_level_id" name="education_level_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <?php echo getEducationLevelOptions($db, $employee['education_level_id']); ?>
                    </select>
                </div>
                
                <div>
                    <label for="phone_no" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone Number</label>
                    <input type="text" id="phone_no" name="phone_no" value="<?php echo htmlspecialchars($employee['phone_no']); ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
            </div>
        </div>
        
        <!-- Address Information -->
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                Address Information
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label for="address_village" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Village/House No.</label>
                    <input type="text" id="address_village" name="address_village" value="<?php echo htmlspecialchars($employee['address_village']); ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label for="address_subdistrict" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Subdistrict</label>
                    <input type="text" id="address_subdistrict" name="address_subdistrict" value="<?php echo htmlspecialchars($employee['address_subdistrict']); ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label for="address_district" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">District</label>
                    <input type="text" id="address_district" name="address_district" value="<?php echo htmlspecialchars($employee['address_district']); ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label for="address_province" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Province</label>
                    <input type="text" id="address_province" name="address_province" value="<?php echo htmlspecialchars($employee['address_province']); ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
            </div>
        </div>
        
        <!-- Employment Information -->
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                Employment Information
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="function_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Function</label>
                    <select id="function_id" name="function_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <?php echo getFunctionOptions($db, $employee['function_id']); ?>
                    </select>
                </div>
                
                <div>
                    <label for="division_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Division</label>
                    <select id="division_id" name="division_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <?php echo getDivisionOptions($db, $employee['division_id']); ?>
                    </select>
                </div>
                
                <div>
                    <label for="department_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Department</label>
                    <select id="department_id" name="department_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <?php echo getDepartmentOptions($db, $employee['department_id']); ?>
                    </select>
                </div>
                
                <div>
                    <label for="section_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Section</label>
                    <select id="section_id" name="section_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <?php echo getSectionOptions($db, $employee['section_id']); ?>
                    </select>
                </div>
                
                <div>
                    <label for="operation_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Operation</label>
                    <select id="operation_id" name="operation_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <?php echo getOperationOptions($db, $employee['operation_id']); ?>
                    </select>
                </div>
                
                <div>
                    <label for="position_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Position</label>
                    <select id="position_id" name="position_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <?php echo getPositionOptions($db, $employee['position_id']); ?>
                    </select>
                </div>
                
                <div>
                    <label for="position_level" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Position Level</label>
                    <select id="position_level" name="position_level" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <?php echo getPositionLevelOptions($db, $employee['position_level']); ?>
                    </select>
                </div>
                
                <div>
                    <label for="labour_cost_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Labour Cost Category</label>
                    <select id="labour_cost_id" name="labour_cost_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <?php echo getLabourCostOptions($db, $employee['labour_cost_id']); ?>
                    </select>
                </div>
                
                <div>
                    <label for="hiring_type_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Hiring Type</label>
                    <select id="hiring_type_id" name="hiring_type_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <?php echo getHiringTypeOptions($db, $employee['hiring_type_id']); ?>
                    </select>
                </div>
                
                <div>
                    <label for="customer_zone_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Customer Zone</label>
                    <select id="customer_zone_id" name="customer_zone_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <?php echo getCustomerZoneOptions($db, $employee['customer_zone_id']); ?>
                    </select>
                </div>
                
                <div>
                    <label for="contribution_level_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Contribution Level</label>
                    <select id="contribution_level_id" name="contribution_level_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <?php echo getContributionLevelOptions($db, $employee['contribution_level_id']); ?>
                    </select>
                </div>
                
                <div>
                    <label for="date_of_hire" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date of Hire</label>
                    <input type="date" id="date_of_hire" name="date_of_hire" value="<?php echo htmlspecialchars($employee['date_of_hire']); ?>" onchange="calculateYearsOfService()" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label for="year_of_service" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Years of Service (Auto)</label>
                    <input type="number" 
                           id="year_of_service" 
                           name="year_of_service" 
                           value="<?php echo htmlspecialchars($employee['year_of_service']); ?>" 
                           readonly 
                           class="w-full px-4 py-2 bg-gray-100 dark:bg-gray-600 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-600 dark:text-gray-300">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Auto-calculated from date of hire</p>
                </div>
                
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status <span class="text-red-500">*</span></label>
                    <select id="status" name="status" required onchange="handleStatusChange()" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <?php echo getStatusOptions($employee['status']); ?>
                    </select>
                </div>
                
                <div id="terminationFields" class="md:col-span-3 <?php echo $employee['status'] === 'Terminated' ? '' : 'hidden'; ?>">
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                        <h3 class="font-semibold text-red-800 dark:text-red-300 mb-3">Termination Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="date_of_termination" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date of Termination</label>
                                <input type="date" id="date_of_termination" name="date_of_termination" value="<?php echo htmlspecialchars($employee['date_of_termination']); ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            </div>
                            
                            <div>
                                <label for="month_of_termination" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Month of Termination</label>
                                <input type="number" id="month_of_termination" name="month_of_termination" value="<?php echo htmlspecialchars($employee['month_of_termination']); ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="reason_for_termination" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reason for Termination</label>
                            <textarea id="reason_for_termination" name="reason_for_termination" rows="2" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"><?php echo htmlspecialchars($employee['reason_for_termination']); ?></textarea>
                        </div>
                        
                        <div>
                            <label for="suggestion" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Suggestion</label>
                            <textarea id="suggestion" name="suggestion" rows="2" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"><?php echo htmlspecialchars($employee['suggestion']); ?></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="md:col-span-3">
                    <label for="remark" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Remark</label>
                    <textarea id="remark" name="remark" rows="2" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"><?php echo htmlspecialchars($employee['remark']); ?></textarea>
                </div>
            </div>
        </div>
        
        <!-- System Information -->
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                System Information
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Username <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($employee['username']); ?>" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Password <span class="text-xs text-gray-500">(Leave blank to keep current)</span>
                    </label>
                    <input type="password" id="password" name="password" placeholder="Enter new password to change" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Leave blank to keep current password</p>
                </div><div>
                    <label for="role_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Role <span class="text-red-500">*</span>
                    </label>
                    <select id="role_id" name="role_id" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <?php echo getRoleOptions($db, $employee['role_id']); ?>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Submit Button -->
        <div class="flex justify-end gap-3">
            <a href="<?php echo BASE_URL; ?>/views/admin/employees.php" class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition font-semibold">
                Cancel
            </a>
            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition font-semibold shadow-lg">
                💾 Update Employee
            </button>
        </div>
    </form>
</div>

<script>
// Calculate Age from Birthday
function calculateAge() {
    const birthday = document.getElementById('birthday').value;
    if (birthday) {
        const birthDate = new Date(birthday);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        document.getElementById('age').value = age >= 0 ? age : 0;
    }
}

// Calculate Years of Service from Date of Hire
function calculateYearsOfService() {
    const dateOfHire = document.getElementById('date_of_hire').value;
    if (dateOfHire) {
        const hireDate = new Date(dateOfHire);
        const today = new Date();
        let years = today.getFullYear() - hireDate.getFullYear();
        const monthDiff = today.getMonth() - hireDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < hireDate.getDate())) {
            years--;
        }
        
        document.getElementById('year_of_service').value = years >= 0 ? years : 0;
    }
}

// Handle Status Change - Show/Hide Termination Fields
function handleStatusChange() {
    const status = document.getElementById('status').value;
    const terminationFields = document.getElementById('terminationFields');
    
    if (status === 'Terminated') {
        terminationFields.classList.remove('hidden');
        // Auto-fill termination date with today if empty
        const terminationDate = document.getElementById('date_of_termination');
        if (!terminationDate.value) {
            terminationDate.value = new Date().toISOString().split('T')[0];
        }
    } else {
        terminationFields.classList.add('hidden');
        // Clear termination fields when status is not Terminated
        document.getElementById('date_of_termination').value = '';
        document.getElementById('month_of_termination').value = '';
        document.getElementById('reason_for_termination').value = '';
        document.getElementById('suggestion').value = '';
    }
}

// Initialize calculations on page load
window.addEventListener('DOMContentLoaded', function() {
    // Calculate age if birthday exists
    const birthday = document.getElementById('birthday').value;
    if (birthday) {
        calculateAge();
    }
    
    // Calculate years of service if date of hire exists
    const dateOfHire = document.getElementById('date_of_hire').value;
    if (dateOfHire) {
        calculateYearsOfService();
    }
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>