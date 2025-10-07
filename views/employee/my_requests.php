<?php
// /views/employee/my_requests.php
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../db/Database.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

$auth = new AuthController();
$auth->requireLogin();

$pageTitle = 'My Requests';
require_once __DIR__ . '/../layout/header.php';

$db = Database::getInstance();

// Get all requests for current user
$leaveRequests = $db->fetchAll("SELECT *, 'Leave' as request_type FROM leave_requests WHERE employee_id = ? ORDER BY created_at DESC", [$_SESSION['user_id']]);
$certificateRequests = $db->fetchAll("SELECT *, 'Certificate' as request_type FROM certificate_requests WHERE employee_id = ? ORDER BY created_at DESC", [$_SESSION['user_id']]);
$idCardRequests = $db->fetchAll("SELECT *, 'ID Card' as request_type FROM id_card_requests WHERE employee_id = ? ORDER BY created_at DESC", [$_SESSION['user_id']]);

$allRequests = array_merge($leaveRequests, $certificateRequests, $idCardRequests);

// Sort by created_at
usort($allRequests, function ($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
?>

<div class="max-w-6xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">My Requests</h1>
        <p class="text-gray-600 mt-1">View all your submitted requests</p>
    </div>

    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($allRequests)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                No requests found. <a href="<?php echo BASE_URL; ?>/views/dashboard.php" class="text-blue-600 hover:underline">Create your first request</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($allRequests as $request): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <span class="font-medium text-gray-900"><?php echo htmlspecialchars($request['request_type']); ?></span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?php echo date('M d, Y H:i', strtotime($request['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium <?php
                                                                                            echo $request['status'] === 'Complete' ? 'bg-green-100 text-green-800' : ($request['status'] === 'In Progress' ? 'bg-blue-100 text-blue-800' : ($request['status'] === 'Cancelled' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'));
                                                                                            ?>">
                                        <?php echo htmlspecialchars($request['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">View Details</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- View Details Modal -->
            <div id="detailsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">Request Details</h3>
                        <button onclick="closeDetailsModal()" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div id="detailsContent">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
            </div>

            <script>
                function viewDetails(requestType, requestId) {
                    document.getElementById('detailsModal').classList.remove('hidden');
                    document.getElementById('detailsContent').innerHTML = '<div class="text-center py-8"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div><p class="mt-4 text-gray-600">Loading...</p></div>';

                    fetch('<?php echo BASE_URL; ?>/controllers/get_request_details.php?type=' + encodeURIComponent(requestType) + '&id=' + requestId)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                let html = '<div class="space-y-4">';

                                // Request Type
                                html += '<div><label class="font-semibold text-gray-700 dark:text-gray-300">Type:</label><p class="text-gray-900 dark:text-gray-100">' + requestType + '</p></div>';

                                // Status
                                let statusClass = data.data.status === 'Complete' ? 'bg-green-100 text-green-800' :
                                    (data.data.status === 'In Progress' ? 'bg-blue-100 text-blue-800' :
                                        (data.data.status === 'Cancelled' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'));
                                html += '<div><label class="font-semibold text-gray-700 dark:text-gray-300">Status:</label><p><span class="px-3 py-1 rounded-full text-xs font-medium ' + statusClass + '">' + data.data.status + '</span></p></div>';

                                // Date
                                html += '<div><label class="font-semibold text-gray-700 dark:text-gray-300">Submitted:</label><p class="text-gray-900 dark:text-gray-100">' + data.data.created_at + '</p></div>';

                                // Type-specific fields
                                if (requestType === 'Leave') {
                                    html += '<div><label class="font-semibold text-gray-700 dark:text-gray-300">Leave Type:</label><p class="text-gray-900 dark:text-gray-100">' + (data.data.leave_type || 'N/A') + '</p></div>';
                                    html += '<div><label class="font-semibold text-gray-700 dark:text-gray-300">Period:</label><p class="text-gray-900 dark:text-gray-100">' + (data.data.start_date || '') + ' to ' + (data.data.end_date || '') + ' (' + (data.data.total_days || 0) + ' days)</p></div>';
                                    html += '<div><label class="font-semibold text-gray-700 dark:text-gray-300">Reason:</label><p class="text-gray-900 dark:text-gray-100">' + (data.data.reason || 'N/A') + '</p></div>';
                                }

                                if (requestType === 'Certificate') {
                                    html += '<div><label class="font-semibold text-gray-700 dark:text-gray-300">Certificate No:</label><p class="text-gray-900 dark:text-gray-100">' + (data.data.certificate_no || 'Pending') + '</p></div>';
                                    html += '<div><label class="font-semibold text-gray-700 dark:text-gray-300">Purpose:</label><p class="text-gray-900 dark:text-gray-100">' + (data.data.purpose || 'N/A') + '</p></div>';
                                }

                                // Handler remarks
                                if (data.data.handler_remarks) {
                                    html += '<div><label class="font-semibold text-gray-700 dark:text-gray-300">Handler Remarks:</label><p class="text-gray-900 dark:text-gray-100">' + data.data.handler_remarks + '</p></div>';
                                }

                                html += '</div>';
                                document.getElementById('detailsContent').innerHTML = html;
                            } else {
                                document.getElementById('detailsContent').innerHTML = '<p class="text-red-600">Failed to load details: ' + data.message + '</p>';
                            }
                        })
                        .catch(error => {
                            document.getElementById('detailsContent').innerHTML = '<p class="text-red-600">Error loading details</p>';
                            console.error(error);
                        });
                }

                function closeDetailsModal() {
                    document.getElementById('detailsModal').classList.add('hidden');
                }

                // Close when clicking outside
                document.getElementById('detailsModal').addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeDetailsModal();
                    }
                });
            </script>

            <?php
            // แก้ไข button View Details ในตาราง
            // ค้นหา: <button class="text-blue-600...">View Details</button>
            // แทนที่ด้วย:
            ?>
            <button onclick="viewDetails('<?php echo htmlspecialchars($request['request_type']); ?>', <?php echo $request['request_id']; ?>)"
                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                View Details
            </button>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>