<?php
// /views/employee/requests/leave.php
// Leave Request Form

require_once __DIR__ . '/../../../config/db_config.php';
require_once __DIR__ . '/../../../db/Database.php';
require_once __DIR__ . '/../../../db/Localization.php';
require_once __DIR__ . '/../../../controllers/AuthController.php';

$auth = new AuthController();
$auth->requireLogin();

$pageTitle = get_text('leave_request');
$currentUser = $auth->getCurrentUser();

require_once __DIR__ . '/../../layout/header.php';

?>

<!-- Rating Modal -->
<div id="ratingModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg max-w-md w-full p-6">
        <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">
            ⭐ Please Rate Your Previous Request
        </h3>

        <div class="mb-4">
            <p class="text-gray-600 dark:text-gray-400 mb-2">
                You have a completed request that needs your feedback.
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-500" id="requestInfo"></p>
        </div>

        <form id="ratingForm" class="space-y-4">
            <input type="hidden" id="rating_table" name="table">
            <input type="hidden" id="rating_request_id" name="request_id">

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    How satisfied are you with the service? <span class="text-red-500">*</span>
                </label>
                <div class="flex justify-center space-x-2" id="starRating">
                    <button type="button" onclick="setRating(1)" class="star text-4xl text-gray-300 hover:text-yellow-400 transition">★</button>
                    <button type="button" onclick="setRating(2)" class="star text-4xl text-gray-300 hover:text-yellow-400 transition">★</button>
                    <button type="button" onclick="setRating(3)" class="star text-4xl text-gray-300 hover:text-yellow-400 transition">★</button>
                    <button type="button" onclick="setRating(4)" class="star text-4xl text-gray-300 hover:text-yellow-400 transition">★</button>
                    <button type="button" onclick="setRating(5)" class="star text-4xl text-gray-300 hover:text-yellow-400 transition">★</button>
                </div>
                <input type="hidden" id="rating_score" name="score" required>
                <p class="text-center text-sm text-gray-500 mt-2" id="ratingText">Click to rate</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Additional Feedback (Optional)
                </label>
                <textarea name="feedback"
                    rows="3"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                    placeholder="Tell us more about your experience..."></textarea>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
                    Submit Rating
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let selectedRating = 0;

    // Check for pending ratings on page load
    document.addEventListener('DOMContentLoaded', function() {
        checkPendingRating();
    });

    function checkPendingRating() {
        fetch('<?php echo BASE_URL; ?>/controllers/check_pending_rating.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.has_pending) {
                    // Show rating modal
                    document.getElementById('rating_table').value = data.pending.table;
                    document.getElementById('rating_request_id').value = data.pending.request_id;
                    document.getElementById('requestInfo').textContent =
                        data.pending.type + ' from ' + new Date(data.pending.created_at).toLocaleDateString();
                    document.getElementById('ratingModal').classList.remove('hidden');

                    // Disable form submission
                    const submitBtn = document.querySelector('#leaveRequestForm button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                        submitBtn.title = 'Please rate your previous request first';
                    }
                }
            })
            .catch(error => console.error('Error checking ratings:', error));
    }

    function setRating(rating) {
        selectedRating = rating;
        document.getElementById('rating_score').value = rating;

        // Update star colors
        const stars = document.querySelectorAll('#starRating .star');
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('text-gray-300');
                star.classList.add('text-yellow-400');
            } else {
                star.classList.remove('text-yellow-400');
                star.classList.add('text-gray-300');
            }
        });

        // Update text
        const texts = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
        document.getElementById('ratingText').textContent = texts[rating];
    }

    // Handle rating form submission
    document.getElementById('ratingForm').addEventListener('submit', function(e) {
        e.preventDefault();

        if (selectedRating === 0) {
            alert('Please select a rating!');
            return;
        }

        const formData = new FormData(this);

        fetch('<?php echo BASE_URL; ?>/controllers/submit_rating.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    document.getElementById('ratingModal').classList.add('hidden');

                    // Re-enable form
                    const submitBtn = document.querySelector('#leaveRequestForm button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        submitBtn.title = '';
                    }
                } else {
                    alert('Failed to submit rating: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error submitting rating. Please try again.');
                console.error(error);
            });
    });
</script>

<?php

$db = Database::getInstance();
$message = null;
$messageType = 'success';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leaveType = $_POST['leave_type'] ?? '';
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    $reason = $_POST['reason'] ?? '';

    if (empty($leaveType) || empty($startDate) || empty($endDate)) {
        $message = 'Please fill all required fields';
        $messageType = 'error';
    } else {
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

$employee = $currentUser;

// ✅ แก้ไขตรงนี้ - ตรวจสอบให้แน่ใจว่ามีข้อมูล
$positionName = 'N/A';
$departmentName = 'N/A';

if (!empty($employee['position_id'])) {
    $posResult = $db->fetchOne("SELECT position_name FROM position_master WHERE position_id = ?", [$employee['position_id']]);
    $positionName = $posResult['position_name'] ?? 'N/A';
}

if (!empty($employee['department_id'])) {
    $deptResult = $db->fetchOne("SELECT department_name FROM department_master WHERE department_id = ?", [$employee['department_id']]);
    $departmentName = $deptResult['department_name'] ?? 'N/A';
}

?>

<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800"><?php echo get_text('leave_request'); ?></h1>
        <p class="text-gray-600 mt-1">Submit a new leave request</p>
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
                            value="<?php echo htmlspecialchars($positionName); ?>"
                            readonly
                            class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-600">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                        <input type="text"
                            value="<?php echo htmlspecialchars($departmentName); ?>"
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
                    class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-3 rounded-lg hover:from-blue-700 hover:to-blue-800 transition font-semibold shadow-lg">
                    <?php echo get_text('submit'); ?>
                </button>
                <a href="<?php echo BASE_URL; ?>/views/dashboard.php"
                    class="w-full bg-gray-500 text-white py-3 rounded-lg hover:bg-gray-600 transition font-semibold text-center">
                    <?php echo get_text('cancel'); ?>
                </a>
            </div>
        </form>
    </div>
</div>

<script>
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