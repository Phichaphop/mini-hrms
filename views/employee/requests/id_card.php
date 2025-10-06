<?php
// /views/employee/requests/id_card.php
// ID Card Request Form

require_once __DIR__ . '/../../../config/db_config.php';
require_once __DIR__ . '/../../../db/Database.php';
require_once __DIR__ . '/../../../db/Localization.php';
require_once __DIR__ . '/../../../controllers/AuthController.php';

$auth = new AuthController();
$auth->requireLogin();

$pageTitle = get_text('id_card_request');
$currentUser = $auth->getCurrentUser();

require_once __DIR__ . '/../../layout/header.php';

$db = Database::getInstance();
$message = null;
$messageType = 'success';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = $_POST['reason'] ?? '';
    
    if (empty($reason)) {
        $message = 'Please specify the reason for ID card request';
        $messageType = 'error';
    } else {
        try {
            $sql = "INSERT INTO id_card_requests (employee_id, reason, status) 
                    VALUES (?, ?, 'New')";
            $db->query($sql, [$_SESSION['user_id'], $reason]);
            
            $message = 'ID Card request submitted successfully!';
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
        <h1 class="text-2xl font-bold text-gray-800"><?php echo get_text('id_card_request'); ?></h1>
        <p class="text-gray-600 mt-1">Request new or replacement employee ID card</p>
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
        <form method="POST" id="idCardForm" class="space-y-6">
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
                </div>
            </div>
            
            <!-- ID Card Request Details -->
            <div>
                <h3 class="font-semibold text-gray-900 mb-3">Request Details</h3>
                <div class="space-y-4">
                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                            Reason for Request <span class="text-red-500">*</span>
                        </label>
                        <select id="reason" 
                                name="reason" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select reason</option>
                            <option value="New Employee">New Employee - First ID Card</option>
                            <option value="Lost">Lost ID Card</option>
                            <option value="Stolen">Stolen ID Card</option>
                            <option value="Damaged">Damaged ID Card</option>
                            <option value="Information Update">Information Update Required</option>
                            <option value="Expired">Expired ID Card</option>
                            <option value="Other">Other Reason</option>
                        </select>
                    </div>
                    
                    <div id="otherReasonDiv" class="hidden">
                        <label for="other_reason" class="block text-sm font-medium text-gray-700 mb-2">
                            Please specify other reason
                        </label>
                        <textarea id="other_reason" 
                                  rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                  placeholder="Please provide details..."></textarea>
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
                                    <strong>Important:</strong>
                                </p>
                                <ul class="list-disc list-inside text-sm text-yellow-700 mt-1">
                                    <li>ID card processing takes 5-7 business days</li>
                                    <li>If requesting replacement for lost/stolen card, a fee may apply</li>
                                    <li>Please return your old card if requesting replacement for damage</li>
                                    <li>You will need to provide a recent photo (if not on file)</li>
                                </ul>
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
    
    <!-- ID Card Preview -->
    <div class="mt-6 bg-white rounded-lg shadow-lg p-6">
        <h3 class="font-semibold text-gray-900 mb-4">ID Card Preview</h3>
        <div class="max-w-sm mx-auto bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg p-6 text-white shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <div class="text-xs font-semibold">EMPLOYEE ID CARD</div>
                <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-blue-600 font-bold">
                    <?php echo strtoupper(substr($employee['full_name_en'], 0, 1)); ?>
                </div>
            </div>
            <div class="space-y-2 text-sm">
                <div>
                    <div class="text-xs opacity-75">ID</div>
                    <div class="font-semibold"><?php echo htmlspecialchars($employee['employee_id']); ?></div>
                </div>
                <div>
                    <div class="text-xs opacity-75">Name</div>
                    <div class="font-semibold"><?php echo htmlspecialchars($employee['full_name_en']); ?></div>
                </div>
                <div>
                    <div class="text-xs opacity-75">Position</div>
                    <div class="font-semibold"><?php echo htmlspecialchars($employee['position_name'] ?? 'N/A'); ?></div>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-white/30 text-xs text-center">
                Trax Inter Trade Co., Ltd.
            </div>
        </div>
        <p class="text-xs text-gray-500 text-center mt-4">* This is a preview only</p>
    </div>
</div>

<script>
    const reasonSelect = document.getElementById('reason');
    const otherReasonDiv = document.getElementById('otherReasonDiv');
    
    reasonSelect.addEventListener('change', function() {
        if (this.value === 'Other') {
            otherReasonDiv.classList.remove('hidden');
            document.getElementById('other_reason').required = true;
        } else {
            otherReasonDiv.classList.add('hidden');
            document.getElementById('other_reason').required = false;
        }
    });
    
    document.getElementById('idCardForm').addEventListener('submit', function(e) {
        const reason = document.getElementById('reason').value;
        
        if (!reason) {
            e.preventDefault();
            alert('Please select a reason for the ID card request');
            return false;
        }
        
        if (reason === 'Other') {
            const otherReason = document.getElementById('other_reason').value.trim();
            if (otherReason.length < 10) {
                e.preventDefault();
                alert('Please provide more details (minimum 10 characters)');
                return false;
            }
        }
        
        return confirm('Are you sure you want to submit this ID card request?');
    });
</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>