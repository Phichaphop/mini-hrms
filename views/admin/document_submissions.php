<?php
// /views/admin/document_submissions.php
// Manage Document Submissions (Admin/Officer Only)

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../db/Database.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

$auth = new AuthController();
$auth->requireRole(['Admin', 'Officer']);

$pageTitle = 'Document Submissions';
require_once __DIR__ . '/../layout/header.php';

$db = Database::getInstance();
$message = null;
$messageType = 'success';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $submissionId = $_POST['submission_id'] ?? '';
        $newStatus = $_POST['status'] ?? '';
        $remarks = $_POST['remarks'] ?? '';
        
        try {
            $sql = "UPDATE document_submissions 
                    SET status = ?, handler_id = ?, handler_remarks = ? 
                    WHERE submission_id = ?";
            $db->query($sql, [$newStatus, $_SESSION['user_id'], $remarks, $submissionId]);
            
            $message = 'Status updated successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Failed to update: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($_POST['action'] === 'delete' && $auth->hasRole('Admin')) {
        $submissionId = $_POST['submission_id'] ?? '';
        
        try {
            // Get file path before deleting
            $doc = $db->fetchOne("SELECT document_path FROM document_submissions WHERE submission_id = ?", [$submissionId]);
            
            if ($doc && $doc['document_path']) {
                $filePath = __DIR__ . '/../../' . $doc['document_path'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            $db->query("DELETE FROM document_submissions WHERE submission_id = ?", [$submissionId]);
            
            $message = 'Submission deleted successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Failed to delete: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get filter parameters
$filterStatus = $_GET['status'] ?? '';
$filterCategory = $_GET['category'] ?? '';
$searchTerm = $_GET['search'] ?? '';

// Build query
$conditions = ['1=1'];
$params = [];

if (!empty($filterStatus)) {
    $conditions[] = "ds.status = ?";
    $params[] = $filterStatus;
}

if (!empty($filterCategory)) {
    $conditions[] = "ds.service_category_id = ?";
    $params[] = $filterCategory;
}

if (!empty($searchTerm)) {
    $conditions[] = "(e.employee_id LIKE ? OR e.full_name_en LIKE ? OR e.full_name_th LIKE ?)";
    $searchParam = "%$searchTerm%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$whereClause = implode(' AND ', $conditions);

// Fetch submissions
$sql = "SELECT ds.*, 
        e.employee_id, e.full_name_en, e.full_name_th,
        sc.category_name_en, sc.category_name_th, sc.category_name_my,
        st.type_name_en, st.type_name_th, st.type_name_my,
        h.full_name_en as handler_name
        FROM document_submissions ds
        LEFT JOIN employees e ON ds.employee_id = e.employee_id
        LEFT JOIN service_category_master sc ON ds.service_category_id = sc.category_id
        LEFT JOIN service_type_master st ON ds.service_type_id = st.type_id
        LEFT JOIN employees h ON ds.handler_id = h.employee_id
        WHERE $whereClause
        ORDER BY ds.created_at DESC";

$submissions = $db->fetchAll($sql, $params);

// Get categories for filter
$categories = $db->fetchAll("SELECT * FROM service_category_master ORDER BY category_name_en");

// Statistics
$totalSubmissions = count($submissions);
$newCount = count(array_filter($submissions, fn($s) => $s['status'] === 'New'));
$inProgressCount = count(array_filter($submissions, fn($s) => $s['status'] === 'In Progress'));
$completeCount = count(array_filter($submissions, fn($s) => $s['status'] === 'Complete'));

// Pagination
$perPage = 20;
$totalPages = ceil($totalSubmissions / $perPage);
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($currentPage - 1) * $perPage;
$paginatedSubmissions = array_slice($submissions, $offset, $perPage);
?>

<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Document Submissions</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Manage all document submission requests</p>
    </div>
    
    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500' : 'bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500'; ?>">
            <p class="<?php echo $messageType === 'success' ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        </div>
    <?php endif; ?>
    
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">Total Submissions</div>
            <div class="text-2xl font-bold text-gray-800 dark:text-gray-100"><?php echo $totalSubmissions; ?></div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">New</div>
            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400"><?php echo $newCount; ?></div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">In Progress</div>
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400"><?php echo $inProgressCount; ?></div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">Completed</div>
            <div class="text-2xl font-bold text-green-600 dark:text-green-400"><?php echo $completeCount; ?></div>
        </div>
    </div>
    
    <!-- Filter Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="text" 
                   name="search" 
                   placeholder="Search by Employee ID or Name..." 
                   value="<?php echo htmlspecialchars($searchTerm); ?>"
                   class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
            
            <select name="category" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['category_id']; ?>" <?php echo $filterCategory == $cat['category_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['category_name_en']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <select name="status" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
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
    
    <!-- Submissions Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Rating</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                    <?php if (empty($paginatedSubmissions)): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                No submissions found
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($paginatedSubmissions as $sub): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                    #<?php echo $sub['submission_id']; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">
                                            <?php echo htmlspecialchars($sub['full_name_en']); ?>
                                        </div>
                                        <div class="text-gray-500 dark:text-gray-400">
                                            <?php echo htmlspecialchars($sub['employee_id']); ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    <?php echo htmlspecialchars($sub['category_name_en']); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    <?php echo htmlspecialchars($sub['type_name_en']); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    <?php echo date('M d, Y H:i', strtotime($sub['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($sub['satisfaction_score'] > 0): ?>
                                        <div class="flex items-center">
                                            <span class="text-yellow-400">
                                                <?php echo str_repeat('⭐', $sub['satisfaction_score']); ?>
                                            </span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">
                                                (<?php echo $sub['satisfaction_score']; ?>/5)
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs">Not rated</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium <?php
                                        echo $sub['status'] === 'Complete' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 
                                            ($sub['status'] === 'In Progress' ? 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200' : 
                                            ($sub['status'] === 'Cancelled' ? 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' : 
                                            'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200'));
                                    ?>">
                                        <?php echo htmlspecialchars($sub['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex space-x-2">
                                        <button onclick='viewDetails(<?php echo json_encode($sub); ?>)' 
                                                class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm font-medium">
                                            View
                                        </button>
                                        <button onclick='openUpdateModal(<?php echo json_encode($sub); ?>)' 
                                                class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 text-sm font-medium">
                                            Edit
                                        </button>
                                        <?php if ($auth->hasRole('Admin')): ?>
                                            <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this submission?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="submission_id" value="<?php echo $sub['submission_id']; ?>">
                                                <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-sm font-medium">
                                                    Delete
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="bg-gray-50 dark:bg-gray-700 px-6 py-3 border-t dark:border-gray-600 flex justify-between items-center">
                <div class="text-sm text-gray-700 dark:text-gray-300">
                    Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $perPage, $totalSubmissions); ?> of <?php echo $totalSubmissions; ?> results
                </div>
                <div class="flex gap-2">
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=<?php echo $currentPage - 1; ?><?php echo !empty($filterStatus) ? "&status=$filterStatus" : ''; ?><?php echo !empty($filterCategory) ? "&category=$filterCategory" : ''; ?><?php echo !empty($searchTerm) ? "&search=$searchTerm" : ''; ?>" 
                           class="px-3 py-1 border dark:border-gray-600 rounded hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-white">
                            Previous
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo !empty($filterStatus) ? "&status=$filterStatus" : ''; ?><?php echo !empty($filterCategory) ? "&category=$filterCategory" : ''; ?><?php echo !empty($searchTerm) ? "&search=$searchTerm" : ''; ?>" 
                           class="px-3 py-1 border dark:border-gray-600 rounded <?php echo $i === $currentPage ? 'bg-blue-600 text-white' : 'hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-white'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?php echo $currentPage + 1; ?><?php echo !empty($filterStatus) ? "&status=$filterStatus" : ''; ?><?php echo !empty($filterCategory) ? "&category=$filterCategory" : ''; ?><?php echo !empty($searchTerm) ? "&search=$searchTerm" : ''; ?>" 
                           class="px-3 py-1 border dark:border-gray-600 rounded hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-white">
                            Next
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- View Details Modal -->
<div id="detailsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg max-w-3xl w-full p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">Submission Details</h3>
            <button onclick="closeDetailsModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div id="detailsContent" class="space-y-4">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div id="updateModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg max-w-md w-full p-6">
        <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">Update Status</h3>
        <form method="POST">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="submission_id" id="modal_submission_id">
            
            <div class="mb-4">
                <label for="modal_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                <select id="modal_status" name="status" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="New">New</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Complete">Complete</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label for="modal_remarks" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Handler Remarks</label>
                <textarea id="modal_remarks" name="remarks" rows="3" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"></textarea>
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
function viewDetails(submission) {
    const modal = document.getElementById('detailsModal');
    const content = document.getElementById('detailsContent');
    
    let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
    
    // Basic Info
    html += '<div class="col-span-2 bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">';
    html += '<h4 class="font-semibold text-gray-800 dark:text-gray-100 mb-2">Basic Information</h4>';
    html += '<div class="space-y-2 text-sm">';
    html += '<div><span class="text-gray-600 dark:text-gray-400">Submission ID:</span> <span class="font-semibold dark:text-gray-100">#' + submission.submission_id + '</span></div>';
    html += '<div><span class="text-gray-600 dark:text-gray-400">Employee:</span> <span class="font-semibold dark:text-gray-100">' + submission.full_name_en + ' (' + submission.employee_id + ')</span></div>';
    html += '<div><span class="text-gray-600 dark:text-gray-400">Category:</span> <span class="font-semibold dark:text-gray-100">' + submission.category_name_en + '</span></div>';
    html += '<div><span class="text-gray-600 dark:text-gray-400">Type:</span> <span class="font-semibold dark:text-gray-100">' + submission.type_name_en + '</span></div>';
    html += '<div><span class="text-gray-600 dark:text-gray-400">Submitted:</span> <span class="font-semibold dark:text-gray-100">' + new Date(submission.created_at).toLocaleString() + '</span></div>';
    html += '</div></div>';
    
    // Status Info
    html += '<div class="col-span-2 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">';
    html += '<h4 class="font-semibold text-gray-800 dark:text-gray-100 mb-2">Status Information</h4>';
    html += '<div class="space-y-2 text-sm">';
    html += '<div><span class="text-gray-600 dark:text-gray-400">Status:</span> <span class="px-2 py-1 rounded text-xs font-medium ' + getStatusClass(submission.status) + '">' + submission.status + '</span></div>';
    if (submission.handler_name) {
        html += '<div><span class="text-gray-600 dark:text-gray-400">Handler:</span> <span class="font-semibold dark:text-gray-100">' + submission.handler_name + '</span></div>';
    }
    if (submission.handler_remarks) {
        html += '<div><span class="text-gray-600 dark:text-gray-400">Remarks:</span> <span class="dark:text-gray-100">' + submission.handler_remarks + '</span></div>';
    }
    html += '</div></div>';
    
    // Notes
    if (submission.notes) {
        html += '<div class="col-span-2 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">';
        html += '<h4 class="font-semibold text-gray-800 dark:text-gray-100 mb-2">Notes</h4>';
        html += '<p class="text-sm text-gray-700 dark:text-gray-300">' + submission.notes + '</p>';
        html += '</div>';
    }
    
    // Rating
    if (submission.satisfaction_score > 0) {
        html += '<div class="col-span-2 bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg">';
        html += '<h4 class="font-semibold text-gray-800 dark:text-gray-100 mb-2">Satisfaction Rating</h4>';
        html += '<div class="flex items-center gap-2">';
        html += '<span class="text-2xl">' + '⭐'.repeat(submission.satisfaction_score) + '</span>';
        html += '<span class="text-sm text-gray-600 dark:text-gray-400">(' + submission.satisfaction_score + '/5)</span>';
        html += '</div>';
        if (submission.satisfaction_feedback) {
            html += '<p class="text-sm text-gray-700 dark:text-gray-300 mt-2">' + submission.satisfaction_feedback + '</p>';
        }
        html += '</div>';
    }
    
    // Document
    if (submission.document_path) {
        html += '<div class="col-span-2 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">';
        html += '<h4 class="font-semibold text-gray-800 dark:text-gray-100 mb-2">Attached Document</h4>';
        html += '<a href="<?php echo BASE_URL; ?>' + submission.document_path + '" target="_blank" class="inline-flex items-center text-blue-600 dark:text-blue-400 hover:underline">';
        html += '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';
        html += 'Download Document</a>';
        html += '</div>';
    }
    
    html += '</div>';
    
    content.innerHTML = html;
    modal.classList.remove('hidden');
}

function getStatusClass(status) {
    switch(status) {
        case 'Complete': return 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200';
        case 'In Progress': return 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200';
        case 'Cancelled': return 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200';
        default: return 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200';
    }
}

function closeDetailsModal() {
    document.getElementById('detailsModal').classList.add('hidden');
}

function openUpdateModal(submission) {
    document.getElementById('modal_submission_id').value = submission.submission_id;
    document.getElementById('modal_status').value = submission.status;
    document.getElementById('modal_remarks').value = submission.handler_remarks || '';
    document.getElementById('updateModal').classList.remove('hidden');
}

function closeUpdateModal() {
    document.getElementById('updateModal').classList.add('hidden');
}

// Close modals when clicking outside
document.getElementById('detailsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDetailsModal();
    }
});

document.getElementById('updateModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeUpdateModal();
    }
});

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDetailsModal();
        closeUpdateModal();
    }
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>