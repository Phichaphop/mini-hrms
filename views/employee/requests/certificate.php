<?php
// /views/employee/requests/certificate.php
// Certificate Request Form

require_once __DIR__ . '/../../../config/db_config.php';
require_once __DIR__ . '/../../../db/Database.php';
require_once __DIR__ . '/../../../db/Localization.php';
require_once __DIR__ . '/../../../controllers/AuthController.php';

$auth = new AuthController();
$auth->requireLogin();

$pageTitle = get_text('certificate_request');
$currentUser = $auth->getCurrentUser();

require_once __DIR__ . '/../../layout/header.php';

$db = Database::getInstance();
$message = null;
$messageType = 'success';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $purpose = $_POST['purpose'] ?? '';
    
    if (empty($purpose)) {
        $message = 'Please specify the purpose of certificate';
        $messageType = 'error';
    } else {
        try {
            // Generate certificate number
            $year = date('Y');
            $month = date('m');
            $count = $db->fetchOne("SELECT COUNT(*) as count FROM certificate_requests WHERE YEAR(created_at) = ?", [$year])['count'];
            $certificateNo = "CERT-{$year}{$month}-" . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
            
            $sql = "INSERT INTO certificate_requests (employee_id, certificate_no, purpose, status) 
                    VALUES (?, ?, ?, 'New')";
            $db->query($sql, [$_SESSION['user_id'], $certificateNo, $purpose]);
            
            $message = 'Certificate request submitted successfully! Certificate No: ' . $certificateNo;
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Failed to submit request: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

$employee = $currentUser;
?>

<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800"><?php echo get_text('certificate_request'); ?></h1>
        <p class="text-gray-600 mt-1">Request employment certificate</p>
    </div>
    
    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500'; ?>">
            <p class="<?php echo $messageType === 'success' ? 'text-green-700' : 'text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
            <?php if ($messageType === 'success'): ?>
                <a href="<?php echo BASE_URL; ?>/views/employee/my_requests.php" class="text-sm text-green-600 hover:underline mt-2 inline-block">
                    View My Requests →
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="bg-white rounded-lg shadow-lg p-6">
        <form method="POST" id="certificateForm" class="space-y-6">
            <!-- Employee Information (Read-only) -->
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <h3 class="font-semibold text-blue-900 mb-3">Employee Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Employee ID</label>
                        <input type="text" 
                               value="<?php echo htmlspecialchars($employee['employee_id']); ?>" 
                               readonly
                               class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-600">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" 
                               value="<?php echo htmlspecialchars($employee['full_name_en']); ?>" 
                               readonly
                               class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-600">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                        <input type="text" 
                               value="<?php echo htmlspecialchars($employee['position_name'] ?? 'N/A'); ?>" 
                               readonly
                               class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-600">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                        <input type="text" 
                               value="<?php echo htmlspecialchars($employee['department_name'] ?? 'N/A'); ?>" 
                               readonly
                               class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-600">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date of Hire</label>
                        <input type="text" 
                               value="<?php echo $employee['date_of_hire'] ? date('M d, Y', strtotime($employee['date_of_hire'])) : 'N/A'; ?>" 
                               readonly
                               class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-600">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Years of Service</label>
                        <input type="text" 
                               value="<?php echo htmlspecialchars($employee['year_of_service'] ?? '0'); ?> years" 
                               readonly
                               class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-600">
                    </div>
                </div>
            </div>
            
            <!-- Certificate Details -->
            <div>
                <h3 class="font-semibold text-gray-900 mb-3">Certificate Request Details</h3>
                <div class="space-y-4">
                    <div>
                        <label for="purpose" class="block text-sm font-medium text-gray-700 mb-2">
                            Purpose of Certificate <span class="text-red-500">*</span>
                        </label>
                        <textarea id="purpose" 
                                  name="purpose" 
                                  rows="4"
                                  required
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                  placeholder="Please specify the purpose (e.g., Bank loan application, Visa application, etc.)"></textarea>
                    </div>
                    
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>Note:</strong> Certificate processing may take 3-5 business days. You will be notified once your certificate is ready.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t">
                <button type="submit" 
                        class="flex-1 theme-bg text-white py-3 rounded-lg hover:opacity-90 transition font-semibold">
                    <?php echo get_text('submit'); ?>
                </button>
                <a href="<?php echo BASE_URL; ?>/views/dashboard.php" 
                   class="flex-1 bg-gray-500 text-white py-3 rounded-lg hover:bg-gray-600 transition font-semibold text-center">
                    <?php echo get_text('cancel'); ?>
                </a>
            </div>
        </form>
    </div>
    
    <!-- Information Card -->
    <div class="mt-6 bg-blue-50 rounded-lg p-6 border border-blue-200">
        <h3 class="font-semibold text-blue-900 mb-3">Certificate Information</h3>
        <div class="space-y-2 text-sm text-blue-800">
            <p><strong>What is included:</strong></p>
            <ul class="list-disc list-inside ml-4 space-y-1">
                <li>Employee name and ID</li>
                <li>Current position and department</li>
                <li>Date of employment</li>
                <li>Employment status</li>
                <li>Company seal and signature</li>
            </ul>
        </div>
    </div>
</div>

<script>
    document.getElementById('certificateForm').addEventListener('submit', function(e) {
        const purpose = document.getElementById('purpose').value.trim();
        
        if (purpose.length < 10) {
            e.preventDefault();
            alert('Please provide more details about the purpose (minimum 10 characters)');
            return false;
        }
        
        return confirm('Are you sure you want to submit this certificate request?');
    });
</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>