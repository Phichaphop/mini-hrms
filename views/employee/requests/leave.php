<?php
// /views/employee/requests/leave.php
// Leave Request Form

$pageTitle = get_text('leave_request');
require_once __DIR__ . '/../../layout/header.php';

$db = Database::getInstance();
$message = null;
$messageType = 'success';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leaveType = $_POST['leave_type'] ?? '';
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    $reason = $_POST['reason'] ?? '';
    
    // Validate
    if (empty($leaveType) || empty($startDate) || empty($endDate)) {
        $message = 'Please fill all required fields';
        $messageType = 'error';
    } else {
        // Calculate total days
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $totalDays = $end->diff($start)->days + 1;
        
        try {
            $sql = "INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, total_days, reason, status) 
                    VALUES (?, ?, ?, ?, ?, ?, 'New')";
            $db->query($sql, [$_SESSION['user_id'], $leaveType, $startDate, $endDate, $totalDays, $reason]);
            
            $message = 'Leave request submitted successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Failed to submit request: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Auto-fill employee data
$employee = $currentUser;
?>

<div class="max-w-4xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800"><?php echo get_text('leave_request'); ?></h1>
        <p class="text-gray-600 mt-1">Submit a new leave request</p>
    </div>
    
    <!-- Alert Messages -->
    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500'; ?>">
            <p class="<?php echo $messageType === 'success' ? 'text-green-700' : 'text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
            <?php if ($messageType === 'success'): ?>
                <a href="/views/employee/my_requests.php" class="text-sm text-green-600 hover:underline mt-2 inline-block">
                    View My Requests →
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Request Form -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <form method="POST" id="leaveRequestForm" class="space-y-6">
            <!-- Employee Information (Read-only) -->
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <h3 class="font-semibold text-blue-900 mb-3">Employee Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <?php echo get_text('employee_id'); ?>
                        </label>
                        <input type="text" 
                               value="<?php echo htmlspecialchars($employee['employee_id']); ?>" 
                               readonly
                               class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-600">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <?php echo get_text('full_name'); ?>
                        </label>
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
            
            <!-- Leave Details -->
            <div>
                <h3 class="font-semibold text-gray-900 mb-3">Leave Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="leave_type" class="block text-sm font-medium text-gray-700 mb-2">
                            Leave Type <span class="text-red-500">*</span>
                        </label>
                        <select id="leave_type" 
                                name="leave_type" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select leave type</option>
                            <option value="Annual Leave">Annual Leave</option>
                            <option value="Sick Leave">Sick Leave</option>
                            <option value="Personal Leave">Personal Leave</option>
                            <option value="Maternity Leave">Maternity Leave</option>
                            <option value="Paternity Leave">Paternity Leave</option>
                            <option value="Unpaid Leave">Unpaid Leave</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Start Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               id="start_date" 
                               name="start_date" 
                               required
                               min="<?php echo date('Y-m-d'); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                            End Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               id="end_date" 
                               name="end_date" 
                               required
                               min="<?php echo date('Y-m-d'); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="md:col-span-2">
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-sm text-gray-600">
                                <strong>Total Days:</strong> <span id="totalDays">0</span> day(s)
                            </p>
                        </div>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                            Reason
                        </label>
                        <textarea id="reason" 
                                  name="reason" 
                                  rows="4"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                  placeholder="Please provide a brief reason for your leave request"></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t">
                <button type="submit" 
                        class="flex-1 theme-bg text-white py-3 rounded-lg hover:opacity-90 transition font-semibold">
                    <?php echo get_text('submit'); ?>
                </button>
                <a href="/views/dashboard.php" 
                   class="flex-1 bg-gray-500 text-white py-3 rounded-lg hover:bg-gray-600 transition font-semibold text-center">
                    <?php echo get_text('cancel'); ?>
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    // Calculate total days
    function calculateTotalDays() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            
            if (diffDays >= 0) {
                document.getElementById('totalDays').textContent = diffDays;
            } else {
                document.getElementById('totalDays').textContent = 0;
            }
        }
    }
    
    document.getElementById('start_date').addEventListener('change', calculateTotalDays);
    document.getElementById('end_date').addEventListener('change', calculateTotalDays);
    
    // Form validation
    document.getElementById('leaveRequestForm').addEventListener('submit', function(e) {
        const startDate = new Date(document.getElementById('start_date').value);
        const endDate = new Date(document.getElementById('end_date').value);
        
        if (endDate < startDate) {
            e.preventDefault();
            alert('End date cannot be before start date!');
            return false;
        }
        
        const leaveType = document.getElementById('leave_type').value;
        if (!leaveType) {
            e.preventDefault();
            alert('Please select a leave type!');
            return false;
        }
        
        return confirm('Are you sure you want to submit this leave request?');
    });
</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>