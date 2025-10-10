<artifact identifier="document-submit-fixed" type="application/vnd.ant.code" language="php" title="HR Service Form - Fixed Version">
    <?php
    // /views/qr_form/document_submit.php - FIXED VERSION
    // HR Service Form with Rating, Datalist, Theme/Language Toggle
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
    // Get theme from URL or default to light
    $theme = $_GET['theme'] ?? 'light';
    if (!in_array($theme, ['light', 'dark'])) {
        $theme = 'light';
    }
    // Language texts
    $texts = [
        'th' => [
            'title' => 'HR Service',
            'subtitle' => 'กรุณากรอกข้อมูลให้ครบถ้วน',
            'employee_id' => 'รหัสพนักงาน',
            'employee_id_placeholder' => 'เลือกรหัสพนักงาน',
            'employee_info' => 'ข้อมูลพนักงาน',
            'full_name' => 'ชื่อ-นามสกุล',
            'position' => 'ตำแหน่ง',
            'department' => 'แผนก',
            'service_category' => 'ประเภทบริการ',
            'service_type' => 'ประเภทการส่ง',
            'document' => 'อัปโหลดเอกสาร (ถ้ามี)',
            'notes' => 'หมายเหตุ (ถ้ามี)',
            'notes_placeholder' => 'กรอกข้อมูลเพิ่มเติม',
            'rating' => 'ให้คะแนนความพึงพอใจ',
            'rating_desc' => 'กรุณาให้คะแนนความพึงพอใจ (1-5 ดาว)',
            'submit' => 'ส่งคำขอ',
            'clear' => 'ล้างข้อมูล',
            'success' => '✅ ส่งคำขอสำเร็จ!',
            'success_desc' => 'ระบบได้รับคำขอของคุณเรียบร้อยแล้ว',
            'error' => '❌ กรุณากรอกข้อมูลให้ครบถ้วน',
            'select_category' => 'เลือกประเภทบริการ',
            'select_type' => 'เลือกประเภทการส่ง',
            'employee_not_found' => 'ไม่พบข้อมูลพนักงาน',
            'required_fields' => 'กรุณากรอกข้อมูลที่มีเครื่องหมาย * ให้ครบถ้วน',
            'invalid_employee' => 'กรุณาเลือกรหัสพนักงานที่ถูกต้อง',
            'select_employee' => 'กรุณาเลือกรหัสพนักงาน',
            'please_rate' => 'กรุณาให้คะแนนความพึงพอใจ',
            'individual' => 'บุคคล',
            'group' => 'กลุ่ม/แผนก',
            'submit_new' => 'ส่งคำขอใหม่'
        ],
        'en' => [
            'title' => 'HR Service',
            'subtitle' => 'Please fill in all required information',
            'employee_id' => 'Employee ID',
            'employee_id_placeholder' => 'Select Employee ID',
            'employee_info' => 'Employee Information',
            'full_name' => 'Full Name',
            'position' => 'Position',
            'department' => 'Department',
            'service_category' => 'Service Category',
            'service_type' => 'Submission Type',
            'document' => 'Upload Document (Optional)',
            'notes' => 'Notes (Optional)',
            'notes_placeholder' => 'Additional information',
            'rating' => 'Satisfaction Rating',
            'rating_desc' => 'Please rate your satisfaction (1-5 stars)',
            'submit' => 'Submit Request',
            'clear' => 'Clear Form',
            'success' => '✅ Request submitted successfully!',
            'success_desc' => 'Your request has been received',
            'error' => '❌ Please fill in all required fields',
            'select_category' => 'Select Service Category',
            'select_type' => 'Select Submission Type',
            'employee_not_found' => 'Employee not found',
            'required_fields' => 'Please fill in all fields marked with *',
            'invalid_employee' => 'Please select a valid Employee ID',
            'select_employee' => 'Please select Employee ID',
            'please_rate' => 'Please provide satisfaction rating',
            'individual' => 'Individual',
            'group' => 'Group/Department',
            'submit_new' => 'Submit New Request'
        ],
        'my' => [
            'title' => 'HR ဝန်ဆောင်မှု',
            'subtitle' => 'ကျေးဇူးပြု၍ လိုအပ်သောအချက်အလက်အားလုံးဖြည့်ပါ',
            'employee_id' => 'ဝန်ထမ်းနံပါတ်',
            'employee_id_placeholder' => 'ဝန်ထမ်းနံပါတ်ရွေးပါ',
            'employee_info' => 'ဝန်ထမ်းအချက်အလက်',
            'full_name' => 'အမည်အပြည့်အစုံ',
            'position' => 'ရာထူး',
            'department' => 'ဌာန',
            'service_category' => 'ဝန်ဆောင်မှုအမျိုးအစား',
            'service_type' => 'တင်သွင်းမှုအမျိုးအစား',
            'document' => 'စာရွက်တင်ရန် (ရွေးချယ်)',
            'notes' => 'မှတ်ချက် (ရွေးချယ်)',
            'notes_placeholder' => 'ထပ်လောင်းအချက်အလက်',
            'rating' => 'စိတ်ကျေနပ်မှုအဆင့်သတ်မှတ်ချက်',
            'rating_desc' => 'ကျေးဇူးပြု၍ စိတ်ကျေနပ်မှုအဆင့်ပေးပါ (1-5 ကြယ်)',
            'submit' => 'တောင်းဆိုချက်တင်သွင်းမည်',
            'clear' => 'ရှင်းလင်းမည်',
            'success' => '✅ တောင်းဆိုချက်တင်သွင်းပြီးပါပြီ!',
            'success_desc' => 'သင့်တောင်းဆိုချက်လက်ခံရရှိပါပြီ',
            'error' => '❌ လိုအပ်သောအချက်အလက်များဖြည့်ပါ',
            'select_category' => 'ဝန်ဆောင်မှုအမျိုးအစားရွေးပါ',
            'select_type' => 'တင်သွင်းမှုအမျိုးအစားရွေးပါ',
            'employee_not_found' => 'ဝန်ထမ်းမတွေ့ရှိပါ',
            'required_fields' => 'ကျေးဇူးပြု၍ * အမှတ်အသားပါသောနေရာများဖြည့်ပါ',
            'invalid_employee' => 'မှန်ကန်သောဝန်ထမ်းနံပါတ်ရွေးပါ',
            'select_employee' => 'ကျေးဇူးပြု၍ ဝန်ထမ်းနံပါတ်ရွေးပါ',
            'please_rate' => 'ကျေးဇူးပြု၍ စိတ်ကျေနပ်မှုအဆင့်ပေးပါ',
            'individual' => 'တစ်ဦးချင်း',
            'group' => 'အဖွဲ့/ဌာန',
            'submit_new' => 'တောင်းဆိုချက်အသစ်တင်သွင်းမည်'
        ]
    ];
    $t = $texts[$lang]; // แก้ไขจุดที่ 1
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
        $rating = intval($_POST['rating'] ?? 0); // แก้ไขจุดที่ 3

        // Validate employee ID exists
        $validEmployee = false;
        foreach ($employees as $emp) {
            if ($emp['employee_id'] === $employeeId) {
                $validEmployee = true;
                break;
            }
        }

        if (empty($employeeId) || empty($categoryId) || empty($typeId) || $rating < 1 || $rating > 5) {
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
                (employee_id, service_category_id, service_type_id, document_path, notes, status, satisfaction_score) 
                VALUES (?, ?, ?, ?, ?, 'New', ?)";
                $db->query($sql, [$employeeId, $categoryId, $typeId, $documentPath, $notes, $rating]);

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
    <html lang="<?php echo $lang; ?>" class="<?php echo $theme === 'dark' ? 'dark' : ''; ?>">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $t['title']; ?> - HR Service</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                darkMode: 'class'
            }
        </script>
        <style>
            * {
                transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
            }

            .gradient-bg {
                background: linear-gradient(135deg, #0EA5E9 0%, #3B82F6 100%);
            }

            .dark .gradient-bg {
                background: linear-gradient(135deg, #0284C7 0%, #1D4ED8 100%);
            }

            .card-hover {
                transition: all 0.3s ease;
                cursor: pointer;
            }

            .card-hover:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3);
            }

            .dark .card-hover:hover {
                box-shadow: 0 10px 25px rgba(14, 165, 233, 0.5);
            }

            .card-selected {
                border-color: #0EA5E9 !important;
                background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%) !important;
                box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3) !important;
            }

            .dark .card-selected {
                background: linear-gradient(135deg, #1E3A8A 0%, #1E40AF 100%) !important;
                border-color: #3B82F6 !important;
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

            .dark .info-box {
                background: linear-gradient(135deg, #1E3A8A 0%, #1E40AF 100%);
                border-left: 4px solid #3B82F6;
            }

            /* Star Rating Styles */
            .star-rating {
                display: flex;
                gap: 8px;
                justify-content: center;
            }

            .star {
                font-size: 48px;
                cursor: pointer;
                color: #D1D5DB;
                transition: all 0.2s ease;
                user-select: none;
            }

            .star:hover,
            .star.active {
                color: #FBBF24;
                transform: scale(1.2);
            }

            .star.active {
                animation: pulse 0.3s ease;
            }

            @keyframes pulse {

                0%,
                100% {
                    transform: scale(1.2);
                }

                50% {
                    transform: scale(1.4);
                }
            }

            /* Theme Toggle */
            .theme-toggle {
                position: relative;
                width: 60px;
                height: 30px;
                background: #D1D5DB;
                border-radius: 15px;
                cursor: pointer;
                transition: background 0.3s;
            }

            .dark .theme-toggle {
                background: #4B5563;
            }

            .theme-toggle-slider {
                position: absolute;
                top: 3px;
                left: 3px;
                width: 24px;
                height: 24px;
                background: white;
                border-radius: 50%;
                transition: transform 0.3s;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 14px;
            }

            .dark .theme-toggle-slider {
                transform: translateX(30px);
            }

            /* Custom Alert Box - ขนาดเท่า Form */
            .custom-alert {
                animation: slideDown 0.5s ease-out;
            }

            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-20px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        </style>
    </head>

    <body class="bg-gradient-to-br from-blue-50 to-white dark:from-gray-900 dark:to-gray-800 min-h-screen">
        <!-- Header -->
        <header class="gradient-bg text-white shadow-lg sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold">🏢 <?php echo $t['title']; ?></h1>
                        <p class="text-sm md:text-base opacity-90 mt-1"><?php echo $t['subtitle']; ?></p>
                    </div>
                    <div class="flex items-center gap-4">
                        <!-- Theme Toggle -->
                        <div class="theme-toggle" onclick="toggleTheme()" title="Toggle Dark/Light Mode">
                            <div class="theme-toggle-slider">
                                <?php echo $theme === 'dark' ? '🌙' : '☀️'; ?>
                            </div>
                        </div>

                        <!-- Language Flags -->
                        <div class="flex gap-2">
                            <a href="?lang=th&theme=<?php echo $theme; ?>" class="px-3 py-2 rounded-lg transition text-xl <?php echo $lang === 'th' ? 'bg-white text-blue-600' : 'bg-blue-600 hover:bg-blue-500'; ?>">
                                🇹🇭
                            </a>
                            <a href="?lang=en&theme=<?php echo $theme; ?>" class="px-3 py-2 rounded-lg transition text-xl <?php echo $lang === 'en' ? 'bg-white text-blue-600' : 'bg-blue-600 hover:bg-blue-500'; ?>">
                                🇬🇧
                            </a>
                            <a href="?lang=my&theme=<?php echo $theme; ?>" class="px-3 py-2 rounded-lg transition text-xl <?php echo $lang === 'my' ? 'bg-white text-blue-600' : 'bg-blue-600 hover:bg-blue-500'; ?>">
                                🇲🇲
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 py-6 md:py-8">
            <?php if ($message): ?>
                <!-- Custom Alert Box - ขนาดเท่า Form -->
                <div class="custom-alert mb-6 rounded-2xl shadow-xl overflow-hidden border <?php echo $messageType === 'success' ? 'border-green-200 dark:border-green-800' : 'border-red-200 dark:border-red-800'; ?>">
                    <div class="<?php echo $messageType === 'success' ? 'bg-gradient-to-r from-green-500 to-emerald-600' : 'bg-gradient-to-r from-red-500 to-rose-600'; ?> text-white p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="text-4xl">
                                    <?php echo $messageType === 'success' ? '✅' : '❌'; ?>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold"><?php echo htmlspecialchars($message); ?></h3>
                                    <p class="text-sm opacity-90 mt-1">
                                        <?php echo $messageType === 'success' ? $t['success_desc'] : $t['required_fields']; ?>
                                    </p>
                                </div>
                            </div>
                            <button onclick="closeAlert()" class="text-white hover:bg-white/20 rounded-full p-2 transition">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <?php if ($messageType === 'success'): ?>
                        <div class="bg-white dark:bg-gray-800 p-6 border-t border-green-200 dark:border-green-800">
                            <button onclick="resetForm()" class="w-full bg-gradient-to-r from-green-500 to-emerald-600 text-white py-3 px-6 rounded-xl hover:from-green-600 hover:to-emerald-700 transition font-bold shadow-lg">
                                ✨ <?php echo $t['submit_new']; ?>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden border border-blue-100 dark:border-gray-700">
                <!-- Form Header -->
                <div class="gradient-bg text-white p-6">
                    <h2 class="text-2xl font-bold"><?php echo $t['title']; ?></h2>
                    <p class="mt-2 opacity-90"><?php echo $t['subtitle']; ?></p>
                </div>

                <!-- Form Body -->
                <form method="POST" enctype="multipart/form-data" id="submissionForm" class="p-6 space-y-6">
                    <!-- Employee Selection with Datalist -->
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b-2 border-blue-100 dark:border-gray-700">
                            1️⃣ <?php echo $t['employee_id']; ?> <span class="text-red-500">*</span>
                        </h3>
                        <input type="text"
                            id="employee_id"
                            name="employee_id"
                            required
                            list="employeeList"
                            autocomplete="off"
                            oninput="loadEmployeeInfo()"
                            class="w-full px-4 py-3 border-2 border-blue-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition dark:bg-gray-700 dark:text-white"
                            placeholder="<?php echo $t['employee_id_placeholder']; ?>">
                        <datalist id="employeeList">
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?php echo htmlspecialchars($emp['employee_id']); ?>">
                                    <?php echo htmlspecialchars($emp['employee_id'] . ' - ' . ($emp['full_name_' . $lang] ?? $emp['full_name_en'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </datalist>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">💡 พิมพ์หรือเลือกรหัสพนักงานจากรายการ</p>
                    </div>

                    <!-- Employee Info Display -->
                    <div id="employeeInfo" class="hidden">
                        <div class="info-box rounded-xl p-6 shadow-sm">
                            <h3 class="font-bold text-gray-800 dark:text-gray-100 mb-4">✓ <?php echo $t['employee_info']; ?></h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1"><?php echo $t['full_name']; ?></label>
                                    <input type="text" id="emp_full_name" readonly class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-blue-200 dark:border-gray-600 rounded-lg text-gray-800 dark:text-white font-semibold">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1"><?php echo $t['position']; ?></label>
                                    <input type="text" id="emp_position" readonly class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-blue-200 dark:border-gray-600 rounded-lg text-gray-800 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1"><?php echo $t['department']; ?></label>
                                    <input type="text" id="emp_department" readonly class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-blue-200 dark:border-gray-600 rounded-lg text-gray-800 dark:text-white">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Service Category Selection -->
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b-2 border-blue-100 dark:border-gray-700">
                            2️⃣ <?php echo $t['service_category']; ?> <span class="text-red-500">*</span>
                        </h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            <?php foreach ($categories as $cat): ?>
                                <div class="card-hover relative bg-white dark:bg-gray-700 rounded-xl p-4 shadow-md border-2 border-blue-100 dark:border-gray-600 hover:border-blue-300"
                                    onclick="selectCard(this, 'service_category_id', <?php echo $cat['category_id']; ?>)">
                                    <input type="radio"
                                        name="service_category_id"
                                        value="<?php echo $cat['category_id']; ?>"
                                        class="hidden">
                                    <div class="text-center">
                                        <div class="text-4xl mb-3">📄</div>
                                        <p class="font-semibold text-gray-800 dark:text-gray-100 text-sm">
                                            <?php echo htmlspecialchars($cat['category_name_' . $lang] ?? $cat['category_name_en']); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Service Type Selection - Default to Individual -->
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b-2 border-blue-100 dark:border-gray-700">
                            3️⃣ <?php echo $t['service_type']; ?> <span class="text-red-500">*</span>
                        </h3>
                        <div class="grid grid-cols-2 gap-4">
                            <?php
                            $typeLabels = ['individual' => $t['individual'], 'group' => $t['group']];
                            $icons = ['👤', '👥'];
                            $i = 0;
                            foreach ($types as $type):
                                $isDefault = ($i === 0); // First one is default (Individual)
                            ?>
                                <div class="card-hover relative bg-white dark:bg-gray-700 rounded-xl p-5 shadow-md border-2 border-blue-100 dark:border-gray-600 hover:border-blue-300 <?php echo $isDefault ? 'card-selected' : ''; ?>"
                                    onclick="selectCard(this, 'service_type_id', <?php echo $type['type_id']; ?>)">
                                    <input type="radio"
                                        name="service_type_id"
                                        value="<?php echo $type['type_id']; ?>" <?php echo $isDefault ? 'checked' : ''; ?>
                                        class="hidden">
                                    <div class="text-center">
                                        <div class="text-4xl mb-3"><?php echo $icons[$i]; ?></div>
                                        <p class="font-semibold text-gray-800 dark:text-gray-100">
                                            <?php echo htmlspecialchars($type['type_name_' . $lang] ?? $type['type_name_en']); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php
                                $i++;
                            endforeach;
                            ?>
                        </div>
                    </div>

                    <!-- Document Upload -->
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b-2 border-blue-100 dark:border-gray-700">
                            4️⃣ <?php echo $t['document']; ?>
                        </h3>
                        <div class="border-2 border-dashed border-blue-200 dark:border-gray-600 rounded-xl p-6 text-center hover:border-blue-400 dark:hover:border-blue-500 transition bg-blue-50/30 dark:bg-gray-700/30">
                            <input type="file"
                                id="document"
                                name="document"
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                onchange="showFileName()"
                                class="hidden">
                            <label for="document" class="cursor-pointer">
                                <div class="text-5xl mb-3">📎</div>
                                <p class="text-gray-700 dark:text-gray-300 font-semibold mb-2"><?php echo $t['document']; ?></p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">PDF, JPG, PNG, DOC, DOCX (Max 5MB)</p>
                                <p id="fileName" class="text-sm text-blue-600 dark:text-blue-400 mt-2 font-semibold"></p>
                            </label>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b-2 border-blue-100 dark:border-gray-700">
                            5️⃣ <?php echo $t['notes']; ?>
                        </h3>
                        <textarea id="notes"
                            name="notes"
                            rows="4"
                            class="w-full px-4 py-3 border-2 border-blue-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition dark:bg-gray-700 dark:text-white"
                            placeholder="<?php echo $t['notes_placeholder']; ?>"></textarea>
                    </div>

                    <!-- Star Rating -->
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b-2 border-blue-100 dark:border-gray-700">
                            6️⃣ <?php echo $t['rating']; ?> <span class="text-red-500">*</span>
                        </h3>
                        <div class="bg-gradient-to-br from-yellow-50 to-orange-50 dark:from-gray-700 dark:to-gray-600 rounded-xl p-8 text-center">
                            <p class="text-gray-700 dark:text-gray-300 mb-4 font-semibold"><?php echo $t['rating_desc']; ?></p>
                            <div class="star-rating" id="starRating">
                                <span class="star" data-value="1" onclick="setRating(1)">★</span>
                                <span class="star" data-value="2" onclick="setRating(2)">★</span>
                                <span class="star" data-value="3" onclick="setRating(3)">★</span>
                                <span class="star" data-value="4" onclick="setRating(4)">★</span>
                                <span class="star" data-value="5" onclick="setRating(5)">★</span>
                            </div>
                            <input type="hidden" id="rating" name="rating" value="0" required>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-4" id="ratingText">กรุณาเลือกคะแนน</p>
                        </div>
                    </div>

                    <!-- Required Fields Notice -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-400 p-4 rounded-lg">
                        <p class="text-sm text-blue-800 dark:text-blue-300">
                            <strong>⚠️ <?php echo $t['required_fields']; ?></strong>
                        </p>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t-2 border-blue-100 dark:border-gray-700">
                        <button type="submit"
                            class="flex-1 bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 text-white py-4 rounded-xl hover:from-blue-600 hover:to-blue-700 dark:hover:from-blue-700 dark:hover:to-blue-800 transition font-bold text-lg shadow-xl">
                            ✓ <?php echo $t['submit']; ?>
                        </button>
                        <button type="button"
                            onclick="resetForm()"
                            class="flex-1 bg-gray-400 dark:bg-gray-600 text-white py-4 rounded-xl hover:bg-gray-500 dark:hover:bg-gray-700 transition font-bold text-lg shadow-xl">
                            ↻ <?php echo $t['clear']; ?>
                        </button>
                    </div>
                </form>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white dark:bg-gray-800 border-t border-blue-100 dark:border-gray-700 mt-8 py-6">
            <div class="max-w-7xl mx-auto px-4 text-center">
                <p class="text-gray-600 dark:text-gray-400 text-sm">© <?php echo date('Y'); ?> Trax Inter Trade Co., Ltd. | Powered by HR Service</p>
            </div>
        </footer>

        <script>
            // Valid employee IDs list
            const validEmployeeIds = [
                <?php
                $ids = array_map(function ($emp) {
                    return "'" . $emp['employee_id'] . "'";
                }, $employees);
                echo implode(',', $ids);
                ?>
            ];

            // Current language and theme
            const currentLang = '<?php echo $lang; ?>';
            const currentTheme = '<?php echo $theme; ?>';

            // Toggle Theme
            function toggleTheme() {
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                window.location.href = '?lang=' + currentLang + '&theme=' + newTheme;
            }

            // Close Alert
            function closeAlert() {
                const alert = document.querySelector('.custom-alert');
                if (alert) {
                    alert.style.animation = 'slideUp 0.3s ease-out';
                    setTimeout(() => alert.remove(), 300);
                }
            }

            // Load Employee Info
            function loadEmployeeInfo() {
                const employeeId = document.getElementById('employee_id').value.trim();

                if (!employeeId || !validEmployeeIds.includes(employeeId)) {
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
                            document.getElementById('employeeInfo').classList.add('hidden');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('employeeInfo').classList.add('hidden');
                    });
            }

            // Star Rating System
            let selectedRating = 0;
            const ratingTexts = {
                th: ['', 'แย่มาก', 'ไม่ดี', 'ปานกลาง', 'ดี', 'ดีมาก'],
                en: ['', 'Very Poor', 'Poor', 'Fair', 'Good', 'Excellent'],
                my: ['', 'အလွန်ညံ့', 'ညံ့', 'အလယ်အလတ်', 'ကောင်း', 'အလွန်ကောင်း']
            };

            function setRating(rating) {
                selectedRating = rating;
                document.getElementById('rating').value = rating;

                // Update star display
                const stars = document.querySelectorAll('.star');
                stars.forEach((star, index) => {
                    if (index < rating) {
                        star.classList.add('active');
                    } else {
                        star.classList.remove('active');
                    }
                });

                // Update text
                const ratingText = ratingTexts[currentLang][rating] || '';
                document.getElementById('ratingText').textContent = ratingText + ' (' + rating + '/5)';
            }

            // Select Card
            function selectCard(element, inputName, value) {
                document.querySelectorAll(`input[name="${inputName}"]`).forEach(input => {
                    input.closest('.card-hover').classList.remove('card-selected');
                });

                element.classList.add('card-selected');

                const radio = element.querySelector(`input[name="${inputName}"]`);
                radio.checked = true;
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
                const messages = {
                    th: 'ต้องการล้างข้อมูลทั้งหมดหรือไม่?',
                    en: 'Do you want to clear all data?',
                    my: 'အချက်အလက်အားလုံးရှင်းလင်းလိုပါသလား?'
                };

                if (confirm(messages[currentLang])) {
                    // Close alert if exists
                    closeAlert();

                    document.getElementById('submissionForm').reset();
                    document.getElementById('employeeInfo').classList.add('hidden');
                    document.getElementById('fileName').textContent = '';

                    // Reset star rating
                    selectedRating = 0;
                    document.getElementById('rating').value = '0';
                    document.querySelectorAll('.star').forEach(star => {
                        star.classList.remove('active');
                    });
                    document.getElementById('ratingText').textContent = 'กรุณาเลือกคะแนน';

                    // Remove all selected states
                    document.querySelectorAll('.card-selected').forEach(card => {
                        card.classList.remove('card-selected');
                    });

                    // Re-select default service type (Individual - first option)
                    const firstTypeCard = document.querySelector('input[name="service_type_id"]').closest('.card-hover');
                    if (firstTypeCard) {
                        firstTypeCard.classList.add('card-selected');
                        firstTypeCard.querySelector('input').checked = true;
                    }

                    // Scroll to top
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                }
            }

            // Form Validation
            document.getElementById('submissionForm').addEventListener('submit', function(e) {
                const messages = {
                    th: {
                        selectEmployee: 'กรุณาเลือกรหัสพนักงาน',
                        invalidEmployee: 'กรุณาเลือกรหัสพนักงานที่ถูกต้อง',
                        selectCategory: 'กรุณาเลือกประเภทบริการ',
                        selectType: 'กรุณาเลือกประเภทการส่ง',
                        selectRating: 'กรุณาให้คะแนนความพึงพอใจ',
                        fileTooLarge: 'ขนาดไฟล์ต้องไม่เกิน 5MB!'
                    },
                    en: {
                        selectEmployee: 'Please select Employee ID',
                        invalidEmployee: 'Please select a valid Employee ID',
                        selectCategory: 'Please select Service Category',
                        selectType: 'Please select Submission Type',
                        selectRating: 'Please provide satisfaction rating',
                        fileTooLarge: 'File size must not exceed 5MB!'
                    },
                    my: {
                        selectEmployee: 'ကျေးဇူးပြု၍ ဝန်ထမ်းနံပါတ်ရွေးပါ',
                        invalidEmployee: 'မှန်ကန်သောဝန်ထမ်းနံပါတ်ရွေးပါ',
                        selectCategory: 'ဝန်ဆောင်မှုအမျိုးအစားရွေးပါ',
                        selectType: 'တင်သွင်းမှုအမျိုးအစားရွေးပါ',
                        selectRating: 'စိတ်ကျေနပ်မှုအဆင့်ပေးပါ',
                        fileTooLarge: 'ဖိုင်အရွယ်အစားသည် 5MB ထက်မပိုရပါ!'
                    }
                };

                const msg = messages[currentLang];
                const employeeId = document.getElementById('employee_id').value.trim();
                const categoryId = document.querySelector('input[name="service_category_id"]:checked');
                const typeId = document.querySelector('input[name="service_type_id"]:checked');
                const rating = parseInt(document.getElementById('rating').value);

                // Validate employee ID
                if (!employeeId) {
                    e.preventDefault();
                    alert(msg.selectEmployee);
                    return false;
                }

                if (!validEmployeeIds.includes(employeeId)) {
                    e.preventDefault();
                    alert(msg.invalidEmployee);
                    return false;
                }

                // Validate category
                if (!categoryId) {
                    e.preventDefault();
                    alert(msg.selectCategory);
                    return false;
                }

                // Validate type
                if (!typeId) {
                    e.preventDefault();
                    alert(msg.selectType);
                    return false;
                }

                // Validate rating
                if (rating < 1 || rating > 5) {
                    e.preventDefault();
                    alert(msg.selectRating);
                    return false;
                }

                // Check file size if uploaded
                const fileInput = document.getElementById('document');
                if (fileInput.files.length > 0) {
                    const fileSize = fileInput.files[0].size;
                    const maxSize = 5 * 1024 * 1024;
                    if (fileSize > maxSize) {
                        e.preventDefault();
                        alert(msg.fileTooLarge);
                        return false;
                    }
                }

                return true;
            });

            // Animation for slideUp
            const style = document.createElement('style');
            style.textContent = `
        @keyframes slideUp {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(-20px);
            }
        }
    `;
            document.head.appendChild(style);
        </script>