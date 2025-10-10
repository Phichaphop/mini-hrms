<?php
// /views/employee/requests/leave.php
// Leave Request Form - FIXED VERSION

require_once __DIR__ . '/../../../config/db_config.php';
require_once __DIR__ . '/../../../db/Database.php';
require_once __DIR__ . '/../../../db/Localization.php';
require_once __DIR__ . '/../../../controllers/AuthController.php';
require_once __DIR__ . '/../../../includes/dropdown_helper.php';

$auth = new AuthController();
$auth->requireLogin();

$pageTitle = 'Leave Request';
require_once __DIR__ . '/../../layout/header.php';

$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();
$message = null;
$messageType = 'success';

// Get position and department with N/A fallback
$positionName = 'N/A';
$departmentName = 'N/A';

if (!empty($currentUser['position_id'])) {
    $posResult = $db->fetchOne("SELECT position_name FROM position_master WHERE position_id = ?", [$currentUser['position_id']]);
    $positionName = $posResult['position_name'] ?? 'N/A';
}

if (!empty($currentUser['department_id'])) {
    $deptResult = $db->fetchOne("SELECT department_name FROM department_master WHERE department_id = ?", [$currentUser['department_id']]);
    $departmentName = $deptResult['department_name'] ?? 'N/A';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $leaveTypeId = $_POST['leave_type_id'] ?? ''; // ✅ แก้เป็น leave_type_id
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        $reason = trim($_POST['reason'] ?? '');
        
        if (empty($leaveTypeId) || empty($startDate) || empty($endDate)) {
            throw new Exception('Please fill in all required fields');
        }
        
        // Calculate total days
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $interval = $start->diff($end);
        $totalDays = $interval->days + 1;
        
        if ($totalDays <= 0) {
            throw new Exception('End date must be after or equal to start date');
        }
        
        // ✅ แก้ SQL Query - ใช้ leave_type_id
        $sql = "INSERT INTO leave_requests (employee_id, leave_type_id, start_date, end_date, total_days, reason, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'New', NOW())";
        
        $db->query($sql, [$_SESSION['user_id'], $leaveTypeId, $startDate, $endDate, $totalDays, $reason]);
        
        $message = 'Leave request submitted successfully!';
        $messageType = 'success';
        
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}
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

<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Leave Request</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Submit your leave application</p>
    </div>
    
    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500' : 'bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500'; ?>">
            <p class="<?php echo $messageType === 'success' ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        </div>
    <?php endif; ?>
    
    <form id="leaveRequestForm" method="POST" class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 space-y-6">
        <!-- Employee Information (Read-only) -->
        <div>
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                Employee Information
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Employee ID</label>
                    <input type="text" value="<?php echo htmlspecialchars($currentUser['employee_id']); ?>" readonly class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-600 dark:text-gray-400">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full Name</label>
                    <input type="text" value="<?php echo htmlspecialchars($currentUser['full_name_en']); ?>" readonly class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-600 dark:text-gray-400">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Position</label>
                    <input type="text" value="<?php echo htmlspecialchars($positionName); ?>" readonly class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-600 dark:text-gray-400">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Department</label>
                    <input type="text" value="<?php echo htmlspecialchars($departmentName); ?>" readonly class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-600 dark:text-gray-400">
                </div>
            </div>
        </div>
        
        <!-- Leave Details -->
        <div>
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                Leave Details
            </h2>
            <div class="space-y-4">
                <div>
                    <label for="leave_type_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Leave Type <span class="text-red-500">*</span>
                    </label>
                    <select id="leave_type_id" 
                            name="leave_type_id" 
                            required 
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <?php echo getLeaveTypeOptions($db); ?>
                    </select>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Start Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               id="start_date" 
                               name="start_date" 
                               required 
                               min="<?php echo date('Y-m-d'); ?>"
                               onchange="calculateDays()"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            End Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               id="end_date" 
                               name="end_date" 
                               required 
                               min="<?php echo date('Y-m-d'); ?>"
                               onchange="calculateDays()"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
                
                <div id="totalDaysDisplay" class="hidden bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
                    <p class="text-sm text-blue-800 dark:text-blue-300">
                        <span class="font-semibold">Total Days:</span> <span id="totalDaysValue">0</span> day(s)
                    </p>
                </div>
                
                <div>
                    <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Reason <span class="text-red-500">*</span>
                    </label>
                    <textarea id="reason" 
                              name="reason" 
                              rows="4" 
                              required
                              class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                              placeholder="Please provide detailed reason for your leave..."></textarea>
                </div>
            </div>
        </div>
        
        <!-- Submit Button -->
        <div class="flex gap-3">
            <a href="<?php echo BASE_URL; ?>/views/employee/my_requests.php" class="flex-1 bg-gray-500 text-white text-center py-3 rounded-lg hover:bg-gray-600 transition font-semibold">
                Cancel
            </a>
            <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-3 rounded-lg hover:from-blue-700 hover:to-blue-800 transition font-semibold shadow-lg">
                📝 Submit Request
            </button>
        </div>
    </form>
</div>

<script>
let selectedRating = 0;

// Calculate total days
function calculateDays() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    if (startDate && endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        const diffTime = end - start;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        
        if (diffDays > 0) {
            document.getElementById('totalDaysValue').textContent = diffDays;
            document.getElementById('totalDaysDisplay').classList.remove('hidden');
        } else {
            document.getElementById('totalDaysDisplay').classList.add('hidden');
            alert('End date must be after or equal to start date');
            document.getElementById('end_date').value = '';
        }
    }
}

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

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>