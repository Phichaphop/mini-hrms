<?php
// /views/qr_form/document_submit.php
// QR Code Document Submission Form (No login required)

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../db/Database.php';
require_once __DIR__ . '/../../models/Employee.php';

$db = Database::getInstance();
$employeeModel = new Employee();

$message = null;
$messageType = 'success';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeId = $_POST['employee_id'] ?? '';
    $categoryId = $_POST['service_category_id'] ?? '';
    $typeId = $_POST['service_type_id'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    // Validate
    if (empty($employeeId) || empty($categoryId) || empty($typeId)) {
        $message = 'Please fill all required fields';
        $messageType = 'error';
    } else {
        try {
            // Handle file upload if present
            $documentPath = null;
            if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../assets/uploads/qr_submissions/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileName = uniqid() . '_' . basename($_FILES['document']['name']);
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['document']['tmp_name'], $targetPath)) {
                    $documentPath = '/assets/uploads/qr_submissions/' . $fileName;
                }
            }
            
            $sql = "INSERT INTO qr_document_submissions 
                    (employee_id, service_category_id, service_type_id, document_path, notes, status) 
                    VALUES (?, ?, ?, ?, ?, 'New')";
            $db->query($sql, [$employeeId, $categoryId, $typeId, $documentPath, $notes]);
            
            $message = 'Document submitted successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Failed to submit: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get all employees for dropdown
$employees = $employeeModel->getAllForDropdown();

// Get service categories and types
$categories = $db->fetchAll("SELECT * FROM service_category_master ORDER BY category_name_en");
$types = $db->fetchAll("SELECT * FROM service_type_master ORDER BY type_name_en");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Submission - Mini HRMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="gradient-bg text-white p-4 shadow-lg">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-2xl font-bold">Mini HRMS</h1>
                <p class="text-sm opacity-90">QR Document Submission</p>
            </div>
        </header>
        
        <!-- Main Content -->
        <main class="flex-1 p-4 md:p-8">
            <div class="max-w-4xl mx-auto">
                <!-- Alert Messages -->
                <?php if ($message): ?>
                    <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500'; ?>">
                        <p class="<?php echo $messageType === 'success' ? 'text-green-700' : 'text-red-700'; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </p>
                    </div>
                <?php endif; ?>
                
                <!-- Submission Form -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">Document Submission Form</h2>
                        <p class="text-gray-600 mt-1">Please fill out all required information</p>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data" id="qrSubmissionForm" class="space-y-6">
                        <!-- Employee Selection -->
                        <div>
                            <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Select Your Employee ID <span class="text-red-500">*</span>
                            </label>
                            <select id="employee_id" 
                                    name="employee_id" 
                                    required
                                    onchange="loadEmployeeInfo()"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Select Employee ID --</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?php echo htmlspecialchars($emp['employee_id']); ?>">
                                        <?php echo htmlspecialchars($emp['employee_id'] . ' - ' . $emp['full_name_en']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Auto-filled Employee Information (Read-only) -->
                        <div id="employeeInfo" class="bg-blue-50 rounded-lg p-4 border border-blue-200 hidden">
                            <h3 class="font-semibold text-blue-900 mb-3">Employee Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                    <input type="text" 
                                           id="emp_full_name" 
                                           readonly
                                           class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-600">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                                    <input type="text" 
                                           id="emp_position" 
                                           readonly
                                           class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-600">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Level</label>
                                    <input type="text" 
                                           id="emp_level" 
                                           readonly
                                           class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-600">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Section</label>
                                    <input type="text" 
                                           id="emp_section" 
                                           readonly
                                           class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-600">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Service Category -->
                        <div>
                            <label for="service_category_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Service Category <span class="text-red-500">*</span>
                            </label>
                            <select id="service_category_id" 
                                    name="service_category_id" 
                                    required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['category_id']; ?>">
                                        <?php echo htmlspecialchars($cat['category_name_en']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Service Type -->
                        <div>
                            <label for="service_type_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Service Type <span class="text-red-500">*</span>
                            </label>
                            <select id="service_type_id" 
                                    name="service_type_id" 
                                    required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Select Type --</option>
                                <?php foreach ($types as $type): ?>
                                    <option value="<?php echo $type['type_id']; ?>">
                                        <?php echo htmlspecialchars($type['type_name_en']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Document Upload -->
                        <div>
                            <label for="document" class="block text-sm font-medium text-gray-700 mb-2">
                                Upload Document (Optional)
                            </label>
                            <input type="file" 
                                   id="document" 
                                   name="document" 
                                   accept=".pdf,.jpg,.jpeg,.png"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Accepted formats: PDF, JPG, PNG (Max 5MB)</p>
                        </div>
                        
                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Additional Notes
                            </label>
                            <textarea id="notes" 
                                      name="notes" 
                                      rows="4"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                      placeholder="Enter any additional information"></textarea>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="pt-4 border-t">
                            <button type="submit" 
                                    class="w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white py-3 rounded-lg hover:from-blue-600 hover:to-purple-700 transition font-semibold">
                                Submit Document
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
        
        <!-- Footer -->
        <footer class="bg-white border-t p-4 text-center text-sm text-gray-600">
            <p>&copy; <?php echo date('Y'); ?> Trax Inter Trade Co., Ltd. All rights reserved.</p>
        </footer>
    </div>

    <script>
        // Load employee information via AJAX
        function loadEmployeeInfo() {
            const employeeId = document.getElementById('employee_id').value;
            
            if (!employeeId) {
                document.getElementById('employeeInfo').classList.add('hidden');
                return;
            }
            
            // Fetch employee data
            fetch('/controllers/ajax_handler.php?action=get_employee_info&employee_id=' + encodeURIComponent(employeeId))
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        const emp = data.data;
                        
                        // Fill in the fields
                        document.getElementById('emp_full_name').value = emp.full_name_en || 'N/A';
                        document.getElementById('emp_position').value = emp.position_name || 'N/A';
                        document.getElementById('emp_level').value = emp.position_level || 'N/A';
                        document.getElementById('emp_section').value = emp.section_name || 'N/A';
                        
                        // Show the info section
                        document.getElementById('employeeInfo').classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load employee information');
                });
        }
        
        // Form validation
        document.getElementById('qrSubmissionForm').addEventListener('submit', function(e) {
            const employeeId = document.getElementById('employee_id').value;
            const categoryId = document.getElementById('service_category_id').value;
            const typeId = document.getElementById('service_type_id').value;
            
            if (!employeeId || !categoryId || !typeId) {
                e.preventDefault();
                alert('Please fill all required fields!');
                return false;
            }
            
            // Validate file size if file is selected
            const fileInput = document.getElementById('document');
            if (fileInput.files.length > 0) {
                const fileSize = fileInput.files[0].size;
                const maxSize = 5 * 1024 * 1024; // 5MB
                
                if (fileSize > maxSize) {
                    e.preventDefault();
                    alert('File size must not exceed 5MB!');
                    return false;
                }
            }
            
            return confirm('Are you sure you want to submit this document?');
        });
    </script>
</body>
</html>