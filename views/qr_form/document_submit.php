<?php
// /views/qr_form/document_submit.php - UPDATED VERSION
// HR Service Form (No login required)

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../db/Database.php';
require_once __DIR__ . '/../../models/Employee.php';

$db = Database::getInstance();
$employeeModel = new Employee();

// Get language from URL or default to Thai
$lang = $_GET['lang'] ?? 'th';
if (!in_array($lang, ['th', 'en', 'my'])) {
    $lang = 'th';
}

// Language texts
$texts = [
    'th' => [
        'title' => 'ระบบบริการ HR',
        'subtitle' => 'กรุณากรอกข้อมูลให้ครบถ้วน',
        'employee_id' => 'รหัสพนักงาน',
        'employee_id_placeholder' => 'พิมพ์เพื่อค้นหารหัสพนักงาน',
        'search' => 'ค้นหา',
        'employee_info' => 'ข้อมูลพนักงาน',
        'full_name' => 'ชื่อ-นามสกุล',
        'position' => 'ตำแหน่ง',
        'department' => 'แผนก',
        'service_category' => 'ประเภทบริการ',
        'service_type' => 'ประเภทการส่ง',
        'document' => 'อัปโหลดเอกสาร (ถ้ามี)',
        'notes' => 'หมายเหตุ',
        'notes_placeholder' => 'กรอกข้อมูลเพิ่มเติม (ถ้ามี)',
        'submit' => 'ส่งคำขอ',
        'clear' => 'ล้างข้อมูล',
        'success' => '✅ ส่งคำขอสำเร็จ!',
        'error' => '❌ กรุณากรอกข้อมูลให้ครบถ้วน',
        'select_category' => 'เลือกประเภทบริการ',
        'select_type' => 'เลือกประเภทการส่ง',
        'employee_not_found' => 'ไม่พบข้อมูลพนักงาน',
        'required_fields' => 'กรุณากรอกข้อมูลที่มีเครื่องหมาย * ให้ครบถ้วน',
        'invalid_employee' => 'กรุณาเลือกรหัสพนักงานจากรายการที่แสดง'
    ],
    'en' => [
        'title' => 'HR Service',
        'subtitle' => 'Please fill in all required information',
        'employee_id' => 'Employee ID',
        'employee_id_placeholder' => 'Type to search employee ID',
        'search' => 'Search',
        'employee_info' => 'Employee Information',
        'full_name' => 'Full Name',
        'position' => 'Position',
        'department' => 'Department',
        'service_category' => 'Service Category',
        'service_type' => 'Submission Type',
        'document' => 'Upload Document (Optional)',
        'notes' => 'Notes',
        'notes_placeholder' => 'Additional information (Optional)',
        'submit' => 'Submit Request',
        'clear' => 'Clear Form',
        'success' => '✅ Request submitted successfully!',
        'error' => '❌ Please fill in all required fields',
        'select_category' => 'Select Service Category',
        'select_type' => 'Select Submission Type',
        'employee_not_found' => 'Employee not found',
        'required_fields' => 'Please fill in all fields marked with *',
        'invalid_employee' => 'Please select employee ID from the list'
    ],
    'my' => [
        'title' => 'HR ဝန်ဆောင်မှု',
        'subtitle' => 'ကျေးဇူးပြု၍ လိုအပ်သောအချက်အလက်အားလုံးဖြည့်ပါ',
        'employee_id' => 'ဝန်ထမ်းနံပါတ်',
        'employee_id_placeholder' => 'ဝန်ထမ်းနံပါတ်ရှာရန်ရိုက်ပါ',
        'search' => 'ရှာဖွေမည်',
        'employee_info' => 'ဝန်ထမ်းအချက်အလက်',
        'full_name' => 'အမည်အပြည့်အစုံ',
        'position' => 'ရာထူး',
        'department' => 'ဌာန',
        'service_category' => 'ဝန်ဆောင်မှုအမျိုးအစား',
        'service_type' => 'တင်သွင်းမှုအမျိုးအစား',
        'document' => 'စာရွက်တင်ရန် (ရွေးချယ်)',
        'notes' => 'မှတ်ချက်',
        'notes_placeholder' => 'ထပ်လောင်းအချက်အလက် (ရွေးချယ်)',
        'submit' => 'တောင်းဆိုချက်တင်သွင်းမည်',
        'clear' => 'ရှင်းလင်းမည်',
        'success' => '✅ တောင်းဆိုချက်တင်သွင်းပြီးပါပြီ!',
        'error' => '❌ လိုအပ်သောအချက်အလက်များဖြည့်ပါ',
        'select_category' => 'ဝန်ဆောင်မှုအမျိုးအစားရွေးပါ',
        'select_type' => 'တင်သွင်းမှုအမျိုးအစားရွေးပါ',
        'employee_not_found' => 'ဝန်ထမ်းမတွေ့ရှိပါ',
        'required_fields' => 'ကျေးဇူးပြု၍ * အမှတ်အသားပါသောနေရာများဖြည့်ပါ',
        'invalid_employee' => 'စာရင်းမှဝန်ထမ်းနံပါတ်ရွေးပါ'
    ]
];

$t = $texts[$lang];

$message = null;
$messageType = 'success';

// Get all employees for datalist
$employees = $db->fetchAll("SELECT employee_id, full_name_en, full_name_th FROM employees WHERE status = 'Active' ORDER BY employee_id");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeId = $_POST['employee_id'] ?? '';
    $categoryId = $_POST['service_category_id'] ?? '';
    $typeId = $_POST['service_type_id'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    // Validate employee ID exists in database
    $validEmployee = false;
    foreach ($employees as $emp) {
        if ($emp['employee_id'] === $employeeId) {
            $validEmployee = true;
            break;
        }
    }
    
    if (empty($employeeId) || empty($categoryId) || empty($typeId)) {
        $message = $t['error'];
        $messageType = 'error';
    } elseif (!$validEmployee) {
        $message = $t['invalid_employee'];
        $messageType = 'error';
    } else {
        try {
            $documentPath = null;
            if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../assets/uploads/document_submissions/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileName = uniqid() . '_' . basename($_FILES['document']['name']);
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['document']['tmp_name'], $targetPath)) {
                    $documentPath = '/assets/uploads/document_submissions/' . $fileName;
                }
            }
            
            $sql = "INSERT INTO document_submissions 
                    (employee_id, service_category_id, service_type_id, document_path, notes, status) 
                    VALUES (?, ?, ?, ?, ?, 'New')";
            $db->query($sql, [$employeeId, $categoryId, $typeId, $documentPath, $notes]);
            
            $message = $t['success'];
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

$categories = $db->fetchAll("SELECT * FROM service_category_master ORDER BY category_name_en");
$types = $db->fetchAll("SELECT * FROM service_type_master ORDER BY type_name_en");
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t['title']; ?> - HR Service</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-bg { 
            background: linear-gradient(135deg, #0EA5E9 0%, #3B82F6 100%); 
        }
        
        .card-hover {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .card-hover:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.2); 
        }
        
        .card-selected {
            border-color: #0EA5E9 !important;
            background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%) !important;
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3) !important;
        }
        
        .card-selected::after {
            content: '✓';
            position: absolute;
            top: 10px;
            right: 10px;
            background: #0EA5E9;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
        }
        
        .info-box {
            background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%);
            border-left: 4px solid #0EA5E9;
        }

        /* Datalist styling */
        #employee_id {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%230EA5E9'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 20px;
        }

        #employee_id:focus {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%233B82F6'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'/%3E%3C/svg%3E");
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-white min-h-screen">
    <!-- Header -->
    <header class="gradient-bg text-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold">🏢 HR Service</h1>
                    <p class="text-sm md:text-base opacity-90 mt-1"><?php echo $t['title']; ?></p>
                </div>
                <div class="flex gap-2">
                    <a href="?lang=th" class="px-3 py-2 rounded-lg transition <?php echo $lang === 'th' ? 'bg-white text-blue-600 font-semibold' : 'bg-blue-600 text-white hover:bg-blue-500'; ?>">
                        🇹🇭 TH
                    </a>
                    <a href="?lang=en" class="px-3 py-2 rounded-lg transition <?php echo $lang === 'en' ? 'bg-white text-blue-600 font-semibold' : 'bg-blue-600 text-white hover:bg-blue-500'; ?>">
                        🇬🇧 EN
                    </a>
                    <a href="?lang=my" class="px-3 py-2 rounded-lg transition <?php echo $lang === 'my' ? 'bg-white text-blue-600 font-semibold' : 'bg-blue-600 text-white hover:bg-blue-500'; ?>">
                        🇲🇲 MY
                    </a>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-6 md:py-8">
        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg animate-pulse <?php echo $messageType === 'success' ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500'; ?>">
                <p class="<?php echo $messageType === 'success' ? 'text-green-700' : 'text-red-700'; ?> font-semibold">
                    <?php echo htmlspecialchars($message); ?>
                </p>
                <?php if ($messageType === 'success'): ?>
                    <button onclick="resetForm()" class="mt-2 text-sm text-green-600 hover:text-green-800 underline">
                        ส่งคำขอใหม่ →
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-blue-100">
            <!-- Form Header -->
            <div class="gradient-bg text-white p-6">
                <h2 class="text-2xl font-bold"><?php echo $t['title']; ?></h2>
                <p class="mt-2 opacity-90"><?php echo $t['subtitle']; ?></p>
            </div>
            
            <!-- Form Body -->
            <form method="POST" enctype="multipart/form-data" id="submissionForm" class="p-6 space-y-6">
                <!-- Employee Search Section -->
                <div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b-2 border-blue-100">
                        1️⃣ <?php echo $t['employee_id']; ?> <span class="text-red-500">*</span>
                    </h3>
                    <div class="flex gap-3">
                        <div class="flex-1">
                            <input type="text" 
                                   id="employee_id" 
                                   name="employee_id" 
                                   required
                                   list="employeeList"
                                   autocomplete="off"
                                   class="w-full px-4 py-3 pr-12 border-2 border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                   placeholder="<?php echo $t['employee_id_placeholder']; ?>">
                            <datalist id="employeeList">
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?php echo htmlspecialchars($emp['employee_id']); ?>">
                                        <?php echo htmlspecialchars($emp['employee_id'] . ' - ' . ($emp['full_name_' . $lang] ?? $emp['full_name_en'])); ?>
                                    </option>
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <button type="button" 
                                onclick="loadEmployeeInfo()" 
                                class="px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition font-semibold shadow-lg">
                            🔍 <?php echo $t['search']; ?>
                        </button>
                    </div>
                    <p class="text-sm text-gray-500 mt-2">💡 พิมพ์รหัสพนักงานหรือชื่อเพื่อค้นหา</p>
                </div>
                
                <!-- Employee Info Display -->
                <div id="employeeInfo" class="hidden">
                    <div class="info-box rounded-xl p-6 shadow-sm">
                        <h3 class="font-bold text-gray-800 mb-4">✓ <?php echo $t['employee_info']; ?></h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1"><?php echo $t['full_name']; ?></label>
                                <input type="text" id="emp_full_name" readonly class="w-full px-4 py-2 bg-white border border-blue-200 rounded-lg text-gray-800 font-semibold">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1"><?php echo $t['position']; ?></label>
                                <input type="text" id="emp_position" readonly class="w-full px-4 py-2 bg-white border border-blue-200 rounded-lg text-gray-800">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1"><?php echo $t['department']; ?></label>
                                <input type="text" id="emp_department" readonly class="w-full px-4 py-2 bg-white border border-blue-200 rounded-lg text-gray-800">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Service Category Selection -->
                <div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b-2 border-blue-100">
                        2️⃣ <?php echo $t['service_category']; ?> <span class="text-red-500">*</span>
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <?php foreach ($categories as $cat): ?>
                            <div class="card-hover relative bg-white rounded-xl p-4 shadow-md border-2 border-blue-100 hover:border-blue-300" 
                                 onclick="selectCard(this, 'service_category_id', <?php echo $cat['category_id']; ?>)">
                                <input type="radio" 
                                       name="service_category_id" 
                                       value="<?php echo $cat['category_id']; ?>" 
                                       class="hidden">
                                <div class="text-center">
                                    <div class="text-4xl mb-3">📄</div>
                                    <p class="font-semibold text-gray-800 text-sm">
                                        <?php echo htmlspecialchars($cat['category_name_' . $lang] ?? $cat['category_name_en']); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Service Type Selection -->
                <div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b-2 border-blue-100">
                        3️⃣ <?php echo $t['service_type']; ?> <span class="text-red-500">*</span>
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <?php foreach ($types as $type): ?>
                            <div class="card-hover relative bg-white rounded-xl p-5 shadow-md border-2 border-blue-100 hover:border-blue-300"
                                 onclick="selectCard(this, 'service_type_id', <?php echo $type['type_id']; ?>)">
                                <input type="radio" 
                                       name="service_type_id" 
                                       value="<?php echo $type['type_id']; ?>" 
                                       class="hidden">
                                <div class="text-center">
                                    <div class="text-4xl mb-3">👤</div>
                                    <p class="font-semibold text-gray-800">
                                        <?php echo htmlspecialchars($type['type_name_' . $lang] ?? $type['type_name_en']); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Document Upload -->
                <div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b-2 border-blue-100">
                        4️⃣ <?php echo $t['document']; ?>
                    </h3>
                    <div class="border-2 border-dashed border-blue-200 rounded-xl p-6 text-center hover:border-blue-400 transition bg-blue-50/30">
                        <input type="file" 
                               id="document" 
                               name="document" 
                               accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                               onchange="showFileName()"
                               class="hidden">
                        <label for="document" class="cursor-pointer">
                            <div class="text-5xl mb-3">📎</div>
                            <p class="text-gray-700 font-semibold mb-2"><?php echo $t['document']; ?></p>
                            <p class="text-sm text-gray-500">PDF, JPG, PNG, DOC, DOCX (Max 5MB)</p>
                            <p id="fileName" class="text-sm text-blue-600 mt-2 font-semibold"></p>
                        </label>
                    </div>
                </div>
                
                <!-- Notes -->
                <div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b-2 border-blue-100">
                        5️⃣ <?php echo $t['notes']; ?>
                    </h3>
                    <textarea id="notes" 
                              name="notes" 
                              rows="4"
                              class="w-full px-4 py-3 border-2 border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                              placeholder="<?php echo $t['notes_placeholder']; ?>"></textarea>
                </div>
                
                <!-- Required Fields Notice -->
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg">
                    <p class="text-sm text-blue-800">
                        <strong>⚠️ <?php echo $t['required_fields']; ?></strong>
                    </p>
                </div>
                
                <!-- Form Actions -->
                <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t-2 border-blue-100">
                    <button type="submit" 
                            class="flex-1 bg-gradient-to-r from-blue-500 to-blue-600 text-white py-4 rounded-xl hover:from-blue-600 hover:to-blue-700 transition font-bold text-lg shadow-xl">
                        ✓ <?php echo $t['submit']; ?>
                    </button>
                    <button type="button" 
                            onclick="resetForm()" 
                            class="flex-1 bg-gray-400 text-white py-4 rounded-xl hover:bg-gray-500 transition font-bold text-lg shadow-xl">
                        ↻ <?php echo $t['clear']; ?>
                    </button>
                </div>
            </form>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="bg-white border-t border-blue-100 mt-8 py-6">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-gray-600 text-sm">© <?php echo date('Y'); ?> Trax Inter Trade Co., Ltd. | Powered by HR Service</p>
        </div>
    </footer>

    <script>
        // Valid employee IDs list
        const validEmployeeIds = [
            <?php 
                $ids = array_map(function($emp) {
                    return "'" . $emp['employee_id'] . "'";
                }, $employees);
                echo implode(',', $ids);
            ?>
        ];

        // Load Employee Info
        function loadEmployeeInfo() {
            const employeeId = document.getElementById('employee_id').value.trim();
            
            if (!employeeId) {
                alert('<?php echo $t['employee_id_placeholder']; ?>');
                return;
            }

            // Validate if employee ID is in the list
            if (!validEmployeeIds.includes(employeeId)) {
                alert('<?php echo $t['invalid_employee']; ?>');
                document.getElementById('employeeInfo').classList.add('hidden');
                return;
            }
            
            fetch('<?php echo BASE_URL; ?>/controllers/ajax_handler.php?action=get_employee_info&employee_id=' + encodeURIComponent(employeeId))
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        const emp = data.data;
                        document.getElementById('emp_full_name').value = emp.full_name_en || 'N/A';
                        document.getElementById('emp_position').value = emp.position_name || 'N/A';
                        document.getElementById('emp_department').value = emp.department_name || 'N/A';
                        document.getElementById('employeeInfo').classList.remove('hidden');
                    } else {
                        alert('<?php echo $t['employee_not_found']; ?>');
                        document.getElementById('employeeInfo').classList.add('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading employee information');
                });
        }
        
        // Auto-load when selecting from datalist
        document.getElementById('employee_id').addEventListener('input', function() {
            const value = this.value.trim();
            if (validEmployeeIds.includes(value)) {
                loadEmployeeInfo();
            }
        });

        // Select Card
        function selectCard(element, inputName, value) {
            document.querySelectorAll(`input[name="${inputName}"]`).forEach(input => {
                input.closest('.card-hover').classList.remove('card-selected');
            });
            
            element.classList.add('card-selected');
            
            const radio = element.querySelector(`input[name="${inputName}"]`);
            radio.checked = true;
            radio.setCustomValidity('');
        }
        
        // Show selected file name
        function showFileName() {
            const fileInput = document.getElementById('document');
            const fileNameDisplay = document.getElementById('fileName');
            
            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                fileNameDisplay.textContent = `✓ Selected: ${file.name} (${fileSize} MB)`;
                
                // Check file size
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must not exceed 5MB!');
                    fileInput.value = '';
                    fileNameDisplay.textContent = '';
                }
            }
        }
        
        // Reset Form
        function resetForm() {
            if (confirm('ต้องการล้างข้อมูลทั้งหมดหรือไม่?')) {
                document.getElementById('submissionForm').reset();
                document.getElementById('employeeInfo').classList.add('hidden');
                document.getElementById('fileName').textContent = '';
                
                // Remove all selected states
                document.querySelectorAll('.card-selected').forEach(card => {
                    card.classList.remove('card-selected');
                });
            }
        }
        
        // Form Validation
        document.getElementById('submissionForm').addEventListener('submit', function(e) {
            const employeeId = document.getElementById('employee_id').value.trim();
            const categoryId = document.querySelector('input[name="service_category_id"]:checked');
            const typeId = document.querySelector('input[name="service_type_id"]:checked');
            
            // Validate employee ID is in the list
            if (!validEmployeeIds.includes(employeeId)) {
                e.preventDefault();
                alert('<?php echo $t['invalid_employee']; ?>');
                return false;
            }
            
            if (!employeeId) {
                e.preventDefault();
                alert('<?php echo $t['employee_id_placeholder']; ?>');
                return false;
            }
            
            if (!categoryId) {
                e.preventDefault();
                alert('<?php echo $t['select_category']; ?>');
                return false;
            }
            
            if (!typeId) {
                e.preventDefault();
                alert('<?php echo $t['select_type']; ?>');
                return false;
            }
            
            // Check file size if uploaded
            const fileInput = document.getElementById('document');
            if (fileInput.files.length > 0) {
                const fileSize = fileInput.files[0].size;
                const maxSize = 5 * 1024 * 1024;
                if (fileSize > maxSize) {
                    e.preventDefault();
                    alert('File size must not exceed 5MB!');
                    return false;
                }
            }
            
            return true;
        });
        
        // Auto-load employee info when pressing Enter
        document.getElementById('employee_id').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                loadEmployeeInfo();
            }
        });
    </script>
</body>
</html>