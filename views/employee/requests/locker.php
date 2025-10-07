<?php
// /views/employee/requests/locker.php
// Locker Usage Request Form

require_once __DIR__ . '/../../../config/db_config.php';
require_once __DIR__ . '/../../../db/Database.php';
require_once __DIR__ . '/../../../db/Localization.php';
require_once __DIR__ . '/../../../controllers/AuthController.php';

$auth = new AuthController();
$auth->requireLogin();

$pageTitle = get_text('locker_request');
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
    $preferredLocation = $_POST['preferred_location'] ?? '';
    $reason = $_POST['reason'] ?? '';

    if (empty($preferredLocation) || empty($reason)) {
        $message = 'Please fill all required fields';
        $messageType = 'error';
    } else {
        try {
            $sql = "INSERT INTO locker_usage_requests (employee_id, preferred_location, reason, status) 
                    VALUES (?, ?, ?, 'New')";
            $db->query($sql, [$_SESSION['user_id'], $preferredLocation, $reason]);

            $message = 'Locker request submitted successfully!';
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

// Get available lockers by location
$availableLockers = $db->fetchAll("
    SELECT location, COUNT(*) as available_count 
    FROM locker_master 
    WHERE status = 'Available' 
    GROUP BY location 
    ORDER BY location
");

// Get all locations (both available and occupied)
$allLocations = $db->fetchAll("
    SELECT DISTINCT location 
    FROM locker_master 
    ORDER BY location
");
?>

<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800"><?php echo get_text('locker_request'); ?></h1>
        <p class="text-gray-600 mt-1">Request locker usage</p>
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

    <!-- Available Lockers Info -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <?php foreach ($availableLockers as $locker): ?>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">📍 <?php echo htmlspecialchars($locker['location']); ?></p>
                        <p class="text-2xl font-bold text-green-600"><?php echo $locker['available_count']; ?></p>
                        <p class="text-xs text-gray-500">Available</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-6">
        <form method="POST" id="lockerForm" class="space-y-6">
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

            <!-- Locker Request Details -->
            <div>
                <h3 class="font-semibold text-gray-900 mb-3">Request Details</h3>
                <div class="space-y-4">
                    <div>
                        <label for="preferred_location" class="block text-sm font-medium text-gray-700 mb-2">
                            Preferred Location <span class="text-red-500">*</span>
                        </label>
                        <select id="preferred_location"
                            name="preferred_location"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select preferred location</option>
                            <?php foreach ($allLocations as $loc): ?>
                                <option value="<?php echo htmlspecialchars($loc['location']); ?>">
                                    <?php echo htmlspecialchars($loc['location']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                            Reason for Request <span class="text-red-500">*</span>
                        </label>
                        <textarea id="reason"
                            name="reason"
                            rows="4"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Please provide reason for locker usage (e.g., Store personal items, Safety equipment storage, etc.)"></textarea>
                    </div>

                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>Locker Usage Policy:</strong>
                                </p>
                                <ul class="list-disc list-inside text-sm text-yellow-700 mt-1 space-y-1">
                                    <li>Locker assignment is subject to availability</li>
                                    <li>You are responsible for locker key/lock</li>
                                    <li>Do not store valuables or prohibited items</li>
                                    <li>Clean out locker when leaving company</li>
                                    <li>Management reserves right to inspect lockers</li>
                                </ul>
                            </div>
                        </div>
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

    <!-- Locker Map Illustration -->
    <div class="mt-6 bg-white rounded-lg shadow-lg p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Locker Locations</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($allLocations as $loc): ?>
                <div class="border-2 border-gray-200 rounded-lg p-4 hover:border-blue-400 transition">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-semibold text-gray-800">📍 <?php echo htmlspecialchars($loc['location']); ?></h4>
                        <?php
                        $locAvailable = array_filter($availableLockers, fn($l) => $l['location'] === $loc['location']);
                        $count = !empty($locAvailable) ? array_values($locAvailable)[0]['available_count'] : 0;
                        ?>
                        <span class="px-2 py-1 rounded text-xs font-medium <?php echo $count > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                            <?php echo $count; ?> Available
                        </span>
                    </div>
                    <div class="grid grid-cols-5 gap-1">
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                            <div class="w-full aspect-square bg-gray-200 rounded flex items-center justify-center text-xs">
                                🔒
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    document.getElementById('lockerForm').addEventListener('submit', function(e) {
        const location = document.getElementById('preferred_location').value;
        const reason = document.getElementById('reason').value.trim();

        if (!location) {
            e.preventDefault();
            alert('Please select a preferred location');
            return false;
        }

        if (reason.length < 20) {
            e.preventDefault();
            alert('Please provide more details about the reason (minimum 20 characters)');
            return false;
        }

        return confirm('Are you sure you want to submit this locker request?');
    });
</script>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>