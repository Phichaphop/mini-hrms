<?php
// /views/admin/all_requests.php
// Manage All Requests (Admin/Officer Only)

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../db/Database.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

$auth = new AuthController();
$auth->requireRole(['Admin', 'Officer']);

$pageTitle = 'Manage All Requests';
require_once __DIR__ . '/../layout/header.php';

$db = Database::getInstance();

$message = null;
$messageType = 'success';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $requestType = $_POST['request_type'] ?? '';
    $requestId = $_POST['request_id'] ?? '';
    $newStatus = $_POST['status'] ?? '';
    $remarks = $_POST['remarks'] ?? '';
    
    $tableMap = [
        'Leave' => 'leave_requests',
        'Certificate' => 'certificate_requests',
        'ID Card' => 'id_card_requests',
        'Shuttle Bus' => 'shuttle_bus_requests',
        'Locker' => 'locker_usage_requests',
        'Supplies' => 'supplies_requests',
        'Skill Test' => 'skill_test_requests',
        'QR Document' => 'qr_document_submissions'
    ];
    
    if (isset($tableMap[$requestType])) {
        try {
            $sql = "UPDATE {$tableMap[$requestType]} 
                    SET status = ?, handler_id = ?, handler_remarks = ? 
                    WHERE request_id = ?";
            $db->query($sql, [$newStatus, $_SESSION['user_id'], $remarks, $requestId]);
            
            $message = 'Request status updated successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Failed to update status: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get filter parameters
$filterStatus = $_GET['status'] ?? '';
$filterType = $_GET['type'] ?? '';

// Build query
$conditions = [];
$params = [];

if (!empty($filterStatus)) {
    $statusCondition = "status = ?";
    $params[] = $filterStatus;
}

// Fetch all requests from different tables
$allRequests = [];

$requestTypes = [
    'leave_requests' => 'Leave',
    'certificate_requests' => 'Certificate',
    'id_card_requests' => 'ID Card',
    'shuttle_bus_requests' => 'Shuttle Bus',
    'locker_usage_requests' => 'Locker',
    'supplies_requests' => 'Supplies',
    'skill_test_requests' => 'Skill Test',
    'qr_document_submissions' => 'QR Document'
];

foreach ($requestTypes as $table => $type) {
    if (empty($filterType) || $filterType === $type) {
        $whereClause = !empty($filterStatus) ? "WHERE status = ?" : "";
        $queryParams = !empty($filterStatus) ? [$filterStatus] : [];
        
        $sql = "SELECT r.*, e.full_name_en, e.employee_id as emp_id, '{$type}' as request_type 
                FROM {$table} r 
                LEFT JOIN employees e ON r.employee_id = e.employee_id 
                {$whereClause}
                ORDER BY r.created_at DESC";
        
        $results = $db->fetchAll($sql, $queryParams);
        $allRequests = array_merge($allRequests, $results);
    }
}

// Sort all requests by date
usort($allRequests, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

// Pagination
$perPage = 20;
$totalRequests = count($allRequests);
$totalPages = ceil($totalRequests / $perPage);
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($currentPage - 1) * $perPage;
$paginatedRequests = array_slice($allRequests, $offset, $perPage);
?>

<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Manage All Requests</h1>
        <p class="text-gray-600 mt-1">View and update request status</p>
    </div>
    
    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500'; ?>">
            <p class="<?php echo $messageType === 'success' ? 'text-green-700' : 'text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        </div>
    <?php endif; ?>
    
    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-lg p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <select name="type" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Request Types</option>
                <?php foreach ($requestTypes as $type): ?>
                    <option value="<?php echo $type; ?>" <?php echo $filterType === $type ? 'selected' : ''; ?>>
                        <?php echo $type; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Status</option>
                <option value="New" <?php echo $filterStatus === 'New' ? 'selected' : ''; ?>>New</option>
                <option value="In Progress" <?php echo $filterStatus === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                <option value="Complete" <?php echo $filterStatus === 'Complete' ? 'selected' : ''; ?>>Complete</option>
                <option value="Cancelled" <?php echo $filterStatus === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
            
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                Filter
            </button>
        </form>
    </div>
    
    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Total Requests</div>
            <div class="text-2xl font-bold text-gray-800"><?php echo $totalRequests; ?></div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">New</div>
            <div class="text-2xl font-bold text-yellow-600">
                <?php echo count(array_filter($allRequests, fn($r) => $r['status'] === 'New')); ?>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">In Progress</div>
            <div class="text-2xl font-bold text-blue-600">
                <?php echo count(array_filter($allRequests, fn($r) => $r['status'] === 'In Progress')); ?>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Completed</div>
            <div class="text-2xl font-bold text-green-600">
                <?php echo count(array_filter($allRequests, fn($r) => $r['status'] === 'Complete')); ?>
            </div>
        </div>
    </div>
    
    <!-- Requests Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($paginatedRequests)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                No requests found
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($paginatedRequests as $request): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <span class="font-medium text-gray-900"><?php echo htmlspecialchars($request['request_type']); ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm">
                                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($request['full_name_en']); ?></div>
                                        <div class="text-gray-500"><?php echo htmlspecialchars($request['emp_id']); ?></div>
                                    </div>
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
                                    <button onclick="openUpdateModal('<?php echo htmlspecialchars($request['request_type']); ?>', <?php echo $request['request_id']; ?>, '<?php echo htmlspecialchars($request['status']); ?>')" 
                                            class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        Update Status
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="bg-gray-50 px-6 py-3 border-t flex justify-between items-center">
                <div class="text-sm text-gray-700">
                    Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $perPage, $totalRequests); ?> of <?php echo $totalRequests; ?> results
                </div>
                <div class="flex gap-2">
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=<?php echo $currentPage - 1; ?><?php echo !empty($filterStatus) ? "&status=$filterStatus" : ''; ?><?php echo !empty($filterType) ? "&type=$filterType" : ''; ?>" 
                           class="px-3 py-1 border rounded hover:bg-gray-100">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo !empty($filterStatus) ? "&status=$filterStatus" : ''; ?><?php echo !empty($filterType) ? "&type=$filterType" : ''; ?>" 
                           class="px-3 py-1 border rounded <?php echo $i === $currentPage ? 'bg-blue-600 text-white' : 'hover:bg-gray-100'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?php echo $currentPage + 1; ?><?php echo !empty($filterStatus) ? "&status=$filterStatus" : ''; ?><?php echo !empty($filterType) ? "&type=$filterType" : ''; ?>" 
                           class="px-3 py-1 border rounded hover:bg-gray-100">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Update Status Modal -->
<div id="updateModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-md w-full p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Update Request Status</h3>
        <form method="POST">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="request_type" id="modal_request_type">
            <input type="hidden" name="request_id" id="modal_request_id">
            
            <div class="mb-4">
                <label for="modal_status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="modal_status" name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="New">New</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Complete">Complete</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label for="modal_remarks" class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
                <textarea id="modal_remarks" name="remarks" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
                    Update
                </button>
                <button type="button" onclick="closeUpdateModal()" class="flex-1 bg-gray-500 text-white py-2 rounded-lg hover:bg-gray-600 transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openUpdateModal(requestType, requestId, currentStatus) {
        document.getElementById('modal_request_type').value = requestType;
        document.getElementById('modal_request_id').value = requestId;
        document.getElementById('modal_status').value = currentStatus;
        document.getElementById('updateModal').classList.remove('hidden');
    }
    
    function closeUpdateModal() {
        document.getElementById('updateModal').classList.add('hidden');
    }
    
    // Close modal when clicking outside
    document.getElementById('updateModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeUpdateModal();
        }
    });
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>