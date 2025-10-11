<?php
// /views/employee/my_requests.php - COMPLETE BEAUTIFUL VERSION
// View all requests submitted by current user with modern UI

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../db/Database.php';
require_once __DIR__ . '/../../db/Localization.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

$auth = new AuthController();
$auth->requireLogin();

$pageTitle = get_text('my_requests');
require_once __DIR__ . '/../layout/header.php';

$db = Database::getInstance();

// All request tables
$requestTables = [
    'leave_requests' => [
        'name' => 'Leave Request',
        'icon' => '🏖️',
        'color' => 'blue'
    ],
    'certificate_requests' => [
        'name' => 'Certificate Request',
        'icon' => '📜',
        'color' => 'green'
    ],
    'id_card_requests' => [
        'name' => 'ID Card Request',
        'icon' => '🆔',
        'color' => 'purple'
    ],
    'shuttle_bus_requests' => [
        'name' => 'Shuttle Bus Request',
        'icon' => '🚌',
        'color' => 'yellow'
    ],
    'locker_usage_requests' => [
        'name' => 'Locker Request',
        'icon' => '🔒',
        'color' => 'indigo'
    ],
    'supplies_requests' => [
        'name' => 'Supplies Request',
        'icon' => '📦',
        'color' => 'pink'
    ],
    'skill_test_requests' => [
        'name' => 'Skill Test Request',
        'icon' => '📝',
        'color' => 'orange'
    ],
    'document_submissions' => [
        'name' => 'Document Submission',
        'icon' => '📄',
        'color' => 'teal'
    ]
];

// Fetch all requests
$allRequests = [];

foreach ($requestTables as $table => $info) {
    try {
        $sql = "SELECT *, '{$info['name']}' as request_type, 
                       '{$info['icon']}' as request_icon,
                       '{$info['color']}' as request_color,
                       '$table' as source_table 
                FROM `$table` 
                WHERE employee_id = ? 
                ORDER BY created_at DESC";
        
        $requests = $db->fetchAll($sql, [$_SESSION['user_id']]);
        $allRequests = array_merge($allRequests, $requests);
    } catch (Exception $e) {
        error_log("Error fetching from $table: " . $e->getMessage());
    }
}

// Sort by created_at DESC
usort($allRequests, function ($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

// Calculate statistics
$stats = [
    'total' => count($allRequests),
    'new' => count(array_filter($allRequests, fn($r) => $r['status'] === 'New')),
    'in_progress' => count(array_filter($allRequests, fn($r) => $r['status'] === 'In Progress')),
    'complete' => count(array_filter($allRequests, fn($r) => $r['status'] === 'Complete')),
    'cancelled' => count(array_filter($allRequests, fn($r) => $r['status'] === 'Cancelled'))
];

// Pagination
$perPage = 10;
$totalRequests = count($allRequests);
$totalPages = ceil($totalRequests / $perPage);
$currentPage = isset($_GET['page']) ? max(1, min($totalPages, intval($_GET['page']))) : 1;
$offset = ($currentPage - 1) * $perPage;
$paginatedRequests = array_slice($allRequests, $offset, $perPage);

// Filter by status
$filterStatus = $_GET['status'] ?? '';
if (!empty($filterStatus) && in_array($filterStatus, ['New', 'In Progress', 'Complete', 'Cancelled'])) {
    $paginatedRequests = array_filter($paginatedRequests, fn($r) => $r['status'] === $filterStatus);
}
?>

<style>
/* Custom Animations */
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

.animate-slideInUp {
    animation: slideInUp 0.5s ease-out;
}

.animate-fadeIn {
    animation: fadeIn 0.3s ease-out;
}

.request-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.request-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
}

.dark .request-card:hover {
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.3);
}

.stat-card {
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
}

.badge-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>

<div class="max-w-7xl mx-auto animate-slideInUp">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-800 dark:text-gray-100 mb-2">
            📋 <?php echo get_text('my_requests'); ?>
        </h1>
        <p class="text-lg text-gray-600 dark:text-gray-400">
            View and track all your submitted requests
        </p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        <!-- Total -->
        <div class="stat-card bg-gradient-to-br from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <div class="text-blue-100 text-sm font-medium">Total</div>
                <div class="bg-white/20 p-2 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-bold"><?php echo $stats['total']; ?></div>
        </div>

        <!-- New -->
        <a href="?status=New" class="stat-card bg-gradient-to-br from-yellow-500 to-yellow-600 dark:from-yellow-600 dark:to-yellow-700 rounded-xl shadow-lg p-6 text-white hover:from-yellow-600 hover:to-yellow-700 dark:hover:from-yellow-700 dark:hover:to-yellow-800 transition">
            <div class="flex items-center justify-between mb-2">
                <div class="text-yellow-100 text-sm font-medium">New</div>
                <div class="bg-white/20 p-2 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-bold">
                <?php echo $stats['new']; ?>
                <?php if ($stats['new'] > 0): ?>
                    <span class="badge-pulse inline-block ml-2 w-3 h-3 bg-white rounded-full"></span>
                <?php endif; ?>
            </div>
        </a>

        <!-- In Progress -->
        <a href="?status=In Progress" class="stat-card bg-gradient-to-br from-purple-500 to-purple-600 dark:from-purple-600 dark:to-purple-700 rounded-xl shadow-lg p-6 text-white hover:from-purple-600 hover:to-purple-700 dark:hover:from-purple-700 dark:hover:to-purple-800 transition">
            <div class="flex items-center justify-between mb-2">
                <div class="text-purple-100 text-sm font-medium">In Progress</div>
                <div class="bg-white/20 p-2 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-bold"><?php echo $stats['in_progress']; ?></div>
        </a>

        <!-- Complete -->
        <a href="?status=Complete" class="stat-card bg-gradient-to-br from-green-500 to-green-600 dark:from-green-600 dark:to-green-700 rounded-xl shadow-lg p-6 text-white hover:from-green-600 hover:to-green-700 dark:hover:from-green-700 dark:hover:to-green-800 transition">
            <div class="flex items-center justify-between mb-2">
                <div class="text-green-100 text-sm font-medium">Complete</div>
                <div class="bg-white/20 p-2 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-bold"><?php echo $stats['complete']; ?></div>
        </a>

        <!-- Cancelled -->
        <a href="?status=Cancelled" class="stat-card bg-gradient-to-br from-red-500 to-red-600 dark:from-red-600 dark:to-red-700 rounded-xl shadow-lg p-6 text-white hover:from-red-600 hover:to-red-700 dark:hover:from-red-700 dark:hover:to-red-800 transition">
            <div class="flex items-center justify-between mb-2">
                <div class="text-red-100 text-sm font-medium">Cancelled</div>
                <div class="bg-white/20 p-2 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-bold"><?php echo $stats['cancelled']; ?></div>
        </a>
    </div>

    <!-- Filter Bar -->
    <?php if (!empty($filterStatus)): ?>
        <div class="mb-6 flex items-center justify-between bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <span class="text-blue-700 dark:text-blue-300 font-medium">
                    Filtered by: <strong><?php echo htmlspecialchars($filterStatus); ?></strong>
                </span>
            </div>
            <a href="?" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium">
                Clear Filter ✕
            </a>
        </div>
    <?php endif; ?>

    <!-- Requests List -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl overflow-hidden border border-gray-200 dark:border-gray-700">
        <!-- Table Header -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-700 dark:to-blue-800 px-6 py-5">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-white">Request History</h2>
                <div class="text-blue-100 text-sm">
                    Showing <?php echo count($paginatedRequests); ?> of <?php echo $totalRequests; ?> requests
                </div>
            </div>
        </div>

        <?php if (empty($paginatedRequests)): ?>
            <!-- Empty State -->
            <div class="text-center py-20 px-4">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-full mb-6">
                    <svg class="w-12 h-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-700 dark:text-gray-300 mb-3">
                    <?php echo !empty($filterStatus) ? 'No ' . $filterStatus . ' requests found' : 'No requests yet'; ?>
                </h3>
                <p class="text-gray-500 dark:text-gray-400 mb-8 max-w-md mx-auto">
                    <?php echo !empty($filterStatus) 
                        ? 'Try selecting a different status filter above'
                        : 'You haven\'t submitted any requests yet. Start by creating your first request!'; ?>
                </p>
                <div class="flex gap-3 justify-center">
                    <?php if (!empty($filterStatus)): ?>
                        <a href="?" 
                           class="inline-flex items-center px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition shadow-lg font-medium">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            Clear Filter
                        </a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/views/dashboard.php" 
                           class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow-lg font-medium">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Create Request
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <!-- Requests Cards -->
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                <?php foreach ($paginatedRequests as $request): ?>
                    <div class="request-card p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                            <!-- Left Side: Icon + Info -->
                            <div class="flex items-start gap-4 flex-1">
                                <!-- Icon -->
                                <div class="flex-shrink-0">
                                    <?php
                                    $statusColors = [
                                        'New' => 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900 dark:text-yellow-400',
                                        'In Progress' => 'bg-purple-100 text-purple-600 dark:bg-purple-900 dark:text-purple-400',
                                        'Complete' => 'bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-400',
                                        'Cancelled' => 'bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-400'
                                    ];
                                    $color = $statusColors[$request['status']] ?? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400';
                                    ?>
                                    <div class="w-14 h-14 <?php echo $color; ?> rounded-xl flex items-center justify-center text-2xl shadow-md">
                                        <?php echo $request['request_icon']; ?>
                                    </div>
                                </div>

                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-1">
                                        <?php echo htmlspecialchars($request['request_type']); ?>
                                    </h3>
                                    <div class="flex flex-wrap items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                        <span class="inline-flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <?php echo date('M d, Y', strtotime($request['created_at'])); ?>
                                        </span>
                                        <span class="text-gray-300 dark:text-gray-600">•</span>
                                        <span class="inline-flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <?php echo date('H:i', strtotime($request['created_at'])); ?>
                                        </span>
                                        <span class="text-gray-300 dark:text-gray-600">•</span>
                                        <span class="inline-flex items-center font-medium">
                                            #<?php echo str_pad($request['request_id'], 5, '0', STR_PAD_LEFT); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Side: Status + Action -->
                            <div class="flex items-center gap-3 lg:flex-shrink-0">
                                <!-- Status Badge -->
                                <span class="px-4 py-2 rounded-full text-sm font-semibold whitespace-nowrap <?php 
                                    echo $request['status'] === 'Complete' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                        ($request['status'] === 'In Progress' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : 
                                        ($request['status'] === 'Cancelled' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'));
                                ?>">
                                    <?php echo htmlspecialchars($request['status']); ?>
                                </span>

                                <!-- View Details Button -->
                                <button onclick='viewDetails("<?php echo htmlspecialchars($request['source_table']); ?>", <?php echo $request['request_id']; ?>)' 
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 dark:bg-blue-700 text-white rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 transition font-medium shadow-md hover:shadow-lg whitespace-nowrap">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Details
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 border-t border-gray-200 dark:border-gray-600">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            Showing <span class="font-semibold"><?php echo $offset + 1; ?></span> to 
                            <span class="font-semibold"><?php echo min($offset + $perPage, $totalRequests); ?></span> of 
                            <span class="font-semibold"><?php echo $totalRequests; ?></span> results
                        </div>
                        
                        <div class="flex flex-wrap gap-2">
                            <?php if ($currentPage > 1): ?>
                                <a href="?page=<?php echo $currentPage - 1; ?><?php echo !empty($filterStatus) ? '&status=' . urlencode($filterStatus) : ''; ?>" 
                                   class="px-4 py-2 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-500 transition text-gray-700 dark:text-gray-200 font-medium">
                                    ← Previous
                                </a>
                            <?php endif; ?>

                            <?php 
                            $start = max(1, $currentPage - 2);
                            $end = min($totalPages, $currentPage + 2);
                            
                            if ($start > 1): ?>
                                <a href="?page=1<?php echo !empty($filterStatus) ? '&status=' . urlencode($filterStatus) : ''; ?>" 
                                   class="px-4 py-2 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-500 transition text-gray-700 dark:text-gray-200 font-medium">
                                    1
                                </a>
                                <?php if ($start > 2): ?>
                                    <span class="px-4 py-2 text-gray-500 dark:text-gray-400">...</span>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $start; $i <= $end; $i++): ?>
                                <a href="?page=<?php echo $i; ?><?php echo !empty($filterStatus) ? '&status=' . urlencode($filterStatus) : ''; ?>" 
                                   class="px-4 py-2 rounded-lg transition font-medium <?php 
                                       echo $i === $currentPage 
                                           ? 'bg-blue-600 text-white shadow-lg' 
                                           : 'bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 hover:bg-gray-50 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200'; 
                                   ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($end < $totalPages): ?>
                                <?php if ($end < $totalPages - 1): ?>
                                    <span class="px-4 py-2 text-gray-500 dark:text-gray-400">...</span>
                                <?php endif; ?>
                                <a href="?page=<?php echo $totalPages; ?><?php echo !empty($filterStatus) ? '&status=' . urlencode($filterStatus) : ''; ?>" 
                                   class="px-4 py-2 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-500 transition text-gray-700 dark:text-gray-200 font-medium">
                                    <?php echo $totalPages; ?>
                                </a>
                            <?php endif; ?>

                            <?php if ($currentPage < $totalPages): ?>
                                <a href="?page=<?php echo $currentPage + 1; ?><?php echo !empty($filterStatus) ? '&status=' . urlencode($filterStatus) : ''; ?>" 
                                   class="px-4 py-2 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-500 transition text-gray-700 dark:text-gray-200 font-medium">
                                    Next →
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?> <!-- ปิด pagination -->
        <?php endif; ?> <!-- ปิด empty / not empty -->
    </div> <!-- ปิดกล่องหลัก -->
</div> <!-- ปิด container -->
</div> <!-- ปิด max-w-7xl -->