<?php
// /views/qr_form/document_submit.php
// Document Submission Form (No login required)

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
        'title' => 'ระบบส่งเอกสาร',
        'subtitle' => 'กรุณากรอกข้อมูลให้ครบถ้วน',
        'employee_id' => 'รหัสพนักงาน',
        'employee_id_placeholder' => 'พิมพ์รหัสพนักงาน เช่น 000001',
        'employee_info' => 'ข้อมูลพนักงาน',
        'full_name' => 'ชื่อ-นามสกุล',
        'position' => 'ตำแหน่ง',
        'level' => 'ระดับ',
        'section' => 'แผนก',
        'service_category' => 'ประเภทเอกสาร',
        'service_type' => 'ประเภทการส่ง',
        'document' => 'อัปโหลดเอกสาร (ถ้าม)',
        'notes' => 'หมายเหตุ',
        'notes_placeholder' => 'กรอกข้อมูลเพิ่มเติม (ถ้ามี)',
        'submit' => 'ส่งเอกสาร',
        'back' => 'กลับหน้าแรก',
        'success' => 'ส่งเอกสารสำเร็จ!',
        'error' => 'กรุณากรอกข้อมูลให้ครบถ้วน',
        'select_category' => 'เลือกประเภทเอกสาร',
        'select_type' => 'เลือกประเภทการส่ง'
    ],
    'en' => [
        'title' => 'Document Submission',
        'subtitle' => 'Please fill all information',
        'employee_id' => 'Employee ID',
        'employee_id_placeholder' => 'Type Employee ID e.g. 000001',
        'employee_info' => 'Employee Information',
        'full_name' => 'Full Name',
        'position' => 'Position',
        'level' => 'Level',
        'section' => 'Section',
        'service_category' => 'Document Category',
        'service_type' => 'Submission Type',
        'document' => 'Upload Document (Optional)',
        'notes' => 'Notes',
        'notes_placeholder' => 'Additional information (Optional)',
        'submit' => 'Submit Document',
        'back' => 'Back to Home',
        'success' => 'Document submitted successfully!',
        'error' => 'Please fill all required fields',
        'select_category' => 'Select Document Category',
        'select_type' => 'Select Submission Type'
    ],
    'my' => [
        'title' => 'စာရွက်စာတမ်းတင်သွင်းခြင်း',
        'subtitle' => 'ကျေးဇူးပြု၍ အချက်အလက်အားလုံးဖြည့်ပါ',
        'employee_id' => 'ဝန်ထမ်းနံပါတ်',
        'employee_id_placeholder' => 'ဝန်ထမ်းနံပါတ်ရိုက်ထည့်ပါ ဥပမာ 000001',
        'employee_info' => 'ဝန်ထမ်းအချက်အလက်',
        'full_name' => 'အမည်အပြည့်အစုံ',
        'position' => 'ရာထူး',
        'level' => 'အဆင့်',
        'section' => 'ဌာန',
        'service_category' => 'စာရွက်အမျိုးအစား',
        'service_type' => 'တင်သွင်းမှုအမျိုးအစား',
        'document' => 'စာရွက်တင်ရန် (ရွေးချယ်)',
        'notes' => 'မှတ်ချက်',
        'notes_placeholder' => 'ထပ်လောင်းအချက်အလက် (ရွေးချယ်)',
        'submit' => 'စာရွက်တင်သွင်းမည်',
        'back' => 'ပင်မစာမျက်နှာ',
        'success' => 'စာရွက်တင်သွင်းပြီးပါပြီ!',
        'error' => 'လိုအပ်သောအချက်အလက်များဖြည့်ပါ',
        'select_category' => 'စာရွက်အမျိုးအစားရွေးပါ',
        'select_type' => 'တင်သွင်းမှုအမျိုးအစားရွေးပါ'
    ]
];

$t = $texts[$lang];

$message = null;
$messageType = 'success';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeId = $_POST['employee_id'] ?? '';
    $categoryId = $_POST['service_category_id'] ?? '';
    $typeId = $_POST['service_type_id'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    if (empty($employeeId) || empty($categoryId) || empty($typeId)) {
        $message = $t['error'];
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

$employees = $employeeModel->getAllForDropdown();
$categories = $db->fetchAll("SELECT * FROM service_category_master ORDER BY category_name_en");
$types = $db->fetchAll("SELECT * FROM service_type_master ORDER BY type_name_en");
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t['title']; ?> - Mini HRMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .category-card {
            transition: all 0.3s ease;
            cursor: pointer;
            border: 3px solid transparent;
        }
        .category-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .category-card.selected {
            border-color: #3B82F6;
            background: linear-gradient(135deg, #EBF4FF 0%, #E0F2FE 100%);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="min-h-screen flex flex-col">
        <header class="gradient-bg text-white p-4 shadow-lg">
            <div class="max-w-6xl mx-auto flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold">Mini HRMS</h1>
                    <p class="text-sm opacity-90"><?php echo $t['title']; ?></p>
                </div>
                <div class="flex gap-2">
                    <a href="?lang=th" class="px-3 py-2 rounded <?php echo $lang === 'th' ? 'bg-white text-blue-600' : 'bg-blue-700 text-white hover:bg-blue-600'; ?>">
                        🇹🇭 ไทย
                    </a>
                    <a href="?lang=en" class="px-3 py-2 rounded <?php echo $lang === 'en' ? 'bg-white text-blue-600' : 'bg-blue-700 text-white hover:bg-blue-600'; ?>">
                        🇬🇧 EN
                    </a>
                    <a href="?lang=my" class="px-3 py-2 rounded <?php echo $lang === 'my' ? 'bg-white text-blue-600' : 'bg-blue-700 text-white hover:bg-blue-600'; ?>">
                        🇲🇲 MY
                    </a>
                </div>
            </div>
        </header>
        
        <main class="flex-1 p-4 md:p-8">
            <div class="max-w-6xl mx-auto">
                <?php if ($message): ?>
                    <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500'; ?>">
                        <p class="<?php echo $messageType === 'success' ? 'text-green-700' : 'text-red-700'; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </p>
                    </div>
                <?php endif; ?>
                
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-800"><?php echo $t['title']; ?></h2>
                        <p class="text-gray-600 mt-1"><?php echo $t['subtitle']; ?></p>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data" id="submissionForm" class="space-y-6">
                        <div>
                            <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">
                                <?php echo $t['employee_id']; ?> <span class="text-red-500">*</span>
                            </label>
                            <input list="employees" 
                                   id="employee_id" 
                                   name="employee_id" 
                                   required
                                   onchange="loadEmployeeInfo()"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                   placeholder="<?php echo $t['employee_id_placeholder']; ?>">
                            <datalist id="employees">
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?php echo htmlspecialchars($emp['employee_id']); ?>">
                                        <?php echo htmlspecialchars($emp['full_name_en']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        
                        <div id="employeeInfo" class="bg-blue-50 rounded-lg p-4 border border-blue-200 hidden">
                            <h3 class="font-semibold text-blue-900 mb-3"><?php echo $t['employee_info']; ?></h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo $t['full_name']; ?></label>
                                    <input type="text" id="emp_full_name" readonly class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-600">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo $t['position']; ?></label>
                                    <input type="text" id="emp_position" readonly class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-600">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo $t['level']; ?></label>
                                    <input type="text" id="emp_level" readonly class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-600">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo $t['section']; ?></label>
                                    <input type="text" id="emp_section" readonly class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-600">
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                <?php echo $t['service_category']; ?> <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <?php foreach ($categories as $cat): ?>
                                    <div class="category-card bg-white rounded-lg p-4 shadow hover:shadow-lg border-2" 
                                         onclick="selectCategory(this, <?php echo $cat['category_id']; ?>)">
                                        <input type="radio" 
                                               name="service_category_id" 
                                               value="<?php echo $cat['category_id']; ?>" 
                                               class="hidden"
                                               required>
                                        <div class="text-center">
                                            <div class="text-3xl mb-2">📄</div>
                                            <p class="font-semibold text-gray-800">
                                                <?php echo htmlspecialchars($cat['category_name_' . $lang]); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                <?php echo $t['service_type']; ?> <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php foreach ($types as $type): ?>
                                    <div class="category-card bg-white rounded-lg p-4 shadow hover:shadow-lg border-2"
                                         onclick="selectType(this, <?php echo $type['type_id']; ?>)">
                                        <input type="radio" 
                                               name="service_type_id" 
                                               value="<?php echo $type['type_id']; ?>" 
                                               class="hidden"
                                               required>
                                        <div class="text-center">
                                            <div class="text-3xl mb-2">👤</div>
                                            <p class="font-semibold text-gray-800">
                                                <?php echo htmlspecialchars($type['type_name_' . $lang]); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div>
                            <label for="document" class="block text-sm font-medium text-gray-700 mb-2">
                                <?php echo $t['document']; ?>
                            </label>
                            <input type="file" 
                                   id="document" 
                                   name="document" 
                                   accept=".pdf,.jpg,.jpeg,.png"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-1">PDF, JPG, PNG (Max 5MB)</p>
                        </div>
                        
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                <?php echo $t['notes']; ?>
                            </label>
                            <textarea id="notes" 
                                      name="notes" 
                                      rows="4"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                      placeholder="<?php echo $t['notes_placeholder']; ?>"></textarea>
                        </div>
                        
                        <div class="flex gap-3 pt-4 border-t">
                            <button type="submit" class="flex-1 bg-gradient-to-r from-blue-500 to-purple-600 text-white py-3 rounded-lg hover:from-blue-600 hover:to-purple-700 transition font-semibold">
                                <?php echo $t['submit']; ?>
                            </button>
                            <a href="<?php echo BASE_URL; ?>/index.php" class="flex-1 bg-gray-500 text-white py-3 rounded-lg hover:bg-gray-600 transition font-semibold text-center">
                                <?php echo $t['back']; ?>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
        
        <footer class="bg-white border-t p-4 text-center text-sm text-gray-600">
            <p>&copy; <?php echo date('Y'); ?> Trax Inter Trade Co., Ltd.</p>
        </footer>
    </div>

    <script>
        function loadEmployeeInfo() {
            const employeeId = document.getElementById('employee_id').value;
            if (!employeeId) {
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
                        document.getElementById('emp_level').value = emp.position_level || 'N/A';
                        document.getElementById('emp_section').value = emp.section_name || 'N/A';
                        document.getElementById('employeeInfo').classList.remove('hidden');
                    }
                })
                .catch(error => console.error('Error:', error));
        }
        
        function selectCategory(element, categoryId) {
            document.querySelectorAll('.category-card').forEach(card => {
                if (card.querySelector('input[name="service_category_id"]')) {
                    card.classList.remove('selected');
                }
            });
            element.classList.add('selected');
            element.querySelector('input').checked = true;
        }
        
        function selectType(element, typeId) {
            document.querySelectorAll('.category-card').forEach(card => {
                if (card.querySelector('input[name="service_type_id"]')) {
                    card.classList.remove('selected');
                }
            });
            element.classList.add('selected');
            element.querySelector('input').checked = true;
        }
        
        document.getElementById('submissionForm').addEventListener('submit', function(e) {
            const employeeId = document.getElementById('employee_id').value;
            const categoryId = document.querySelector('input[name="service_category_id"]:checked');
            const typeId = document.querySelector('input[name="service_type_id"]:checked');
            
            if (!employeeId || !categoryId || !typeId) {
                e.preventDefault();
                alert('<?php echo $t['error']; ?>');
                return false;
            }
            
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
        });
    </script>
</body>
</html>