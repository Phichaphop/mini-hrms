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
usort($allRequests, function($a, $b) {
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
                                        echo $request['status'] === 'Complete' ? 'bg-green-100 text-green-800' : 
                                            ($request['status'] === 'In Progress' ? 'bg-blue-100 text-blue-800' : 
                                            ($request['status'] === 'Cancelled' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'));
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
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>