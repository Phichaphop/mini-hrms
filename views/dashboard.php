<?php
// /views/dashboard.php
// Dashboard - COMPLETE VERSION with Fixed Welcome Message

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../db/Database.php';
require_once __DIR__ . '/../db/Localization.php';
require_once __DIR__ . '/../controllers/AuthController.php';

$auth = new AuthController();
$auth->requireLogin();

$pageTitle = get_text('dashboard');
require_once __DIR__ . '/layout/header.php';

$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();

// Get statistics
$totalEmployees = 0;
$myRequests = 0;
$pendingRequests = 0;

try {
    // Total employees (only for Admin/Officer)
    if ($auth->hasRole(['Admin', 'Officer'])) {
        $result = $db->fetchOne("SELECT COUNT(*) as total FROM employees WHERE status = 'Active'");
        $totalEmployees = $result['total'] ?? 0;
    }
    
    // My requests
    $tables = ['leave_requests', 'certificate_requests', 'id_card_requests', 'shuttle_bus_requests', 
               'locker_usage_requests', 'supplies_requests', 'skill_test_requests'];
    
    foreach ($tables as $table) {
        $result = $db->fetchOne("SELECT COUNT(*) as total FROM $table WHERE employee_id = ?", [$_SESSION['user_id']]);
        $myRequests += $result['total'] ?? 0;
    }
    
    // Pending requests (only for Admin/Officer)
    if ($auth->hasRole(['Admin', 'Officer'])) {
        foreach ($tables as $table) {
            $result = $db->fetchOne("SELECT COUNT(*) as total FROM $table WHERE status IN ('New', 'In Progress')");
            $pendingRequests += $result['total'] ?? 0;
        }
    }
    
    // Get recent requests
    $recentRequests = [];
    foreach ($tables as $table) {
        $typeName = str_replace('_', ' ', ucfirst(str_replace('_requests', '', $table)));
        $requests = $db->fetchAll(
            "SELECT '$typeName' as type, request_id, status, created_at 
             FROM $table 
             WHERE employee_id = ? 
             ORDER BY created_at DESC 
             LIMIT 5", 
            [$_SESSION['user_id']]
        );
        $recentRequests = array_merge($recentRequests, $requests);
    }
    
    // Sort by date and limit to 5
    usort($recentRequests, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    $recentRequests = array_slice($recentRequests, 0, 5);
    
} catch (Exception $e) {
    error_log('Dashboard error: ' . $e->getMessage());
}

// Get department and position with fallback
$departmentName = 'N/A';
$positionName = 'N/A';

if (!empty($currentUser['department_id'])) {
    $deptResult = $db->fetchOne("SELECT department_name FROM department_master WHERE department_id = ?", [$currentUser['department_id']]);
    $departmentName = $deptResult['department_name'] ?? 'N/A';
}

if (!empty($currentUser['position_id'])) {
    $posResult = $db->fetchOne("SELECT position_name FROM position_master WHERE position_id = ?", [$currentUser['position_id']]);
    $positionName = $posResult['position_name'] ?? 'N/A';
}
?>

<div class="max-w-7xl mx-auto">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 dark:from-blue-800 dark:to-blue-900 rounded-lg shadow-lg p-8 mb-6">
        <div class="flex items-center justify-between">
            <div class="text-white">
                <h1 class="text-3xl font-bold mb-2">
                    👋 <?php echo get_text('welcome'); ?>, <?php echo htmlspecialchars($currentUser['full_name_en']); ?>!
                </h1>
                <p class="text-blue-100 text-lg">
                    <?php echo get_text('employee_id'); ?>: <span class="font-semibold"><?php echo htmlspecialchars($currentUser['employee_id']); ?></span>
                    <span class="mx-2">|</span>
                    <?php echo htmlspecialchars($positionName); ?>
                    <span class="mx-2">|</span>
                    <?php echo htmlspecialchars($departmentName); ?>
                </p>
            </div>
            <div class="hidden md:block">
                <svg class="w-24 h-24 text-blue-200 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </div>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <?php if ($auth->hasRole(['Admin', 'Officer'])): ?>
        <!-- Total Employees -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Total Employees</p>
                    <p class="text-3xl font-bold text-gray-800 dark:text-gray-100"><?php echo number_format($totalEmployees); ?></p>
                </div>
                <div class="bg-blue-100 dark:bg-blue-900 p-3 rounded-full">
                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- My Requests -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1"><?php echo get_text('my_requests'); ?></p>
                    <p class="text-3xl font-bold text-gray-800 dark:text-gray-100"><?php echo number_format($myRequests); ?></p>
                </div>
                <div class="bg-green-100 dark:bg-green-900 p-3 rounded-full">
                    <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <?php if ($auth->hasRole(['Admin', 'Officer'])): ?>
        <!-- Pending Requests -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Pending Requests</p>
                    <p class="text-3xl font-bold text-gray-800 dark:text-gray-100"><?php echo number_format($pendingRequests); ?></p>
                </div>
                <div class="bg-yellow-100 dark:bg-yellow-900 p-3 rounded-full">
                    <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Quick Actions -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">Quick Actions</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="<?php echo BASE_URL; ?>/views/employee/requests/leave.php" class="flex flex-col items-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition">
                <svg class="w-8 h-8 text-blue-600 dark:text-blue-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="text-sm font-medium text-gray-800 dark:text-gray-100">Leave Request</span>
            </a>
            
            <a href="<?php echo BASE_URL; ?>/views/employee/requests/certificate.php" class="flex flex-col items-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition">
                <svg class="w-8 h-8 text-green-600 dark:text-green-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
                <span class="text-sm font-medium text-gray-800 dark:text-gray-100">Certificate</span>
            </a>
            
            <a href="<?php echo BASE_URL; ?>/views/employee/requests/id_card.php" class="flex flex-col items-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/30 transition">
                <svg class="w-8 h-8 text-purple-600 dark:text-purple-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                </svg>
                <span class="text-sm font-medium text-gray-800 dark:text-gray-100">ID Card</span>
            </a>
            
            <a href="<?php echo BASE_URL; ?>/views/employee/my_requests.php" class="flex flex-col items-center p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg hover:bg-orange-100 dark:hover:bg-orange-900/30 transition">
                <svg class="w-8 h-8 text-orange-600 dark:text-orange-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span class="text-sm font-medium text-gray-800 dark:text-gray-100">View All</span>
            </a>
        </div>
    </div>
    
    <!-- Recent Requests -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">Recent Requests</h2>
        
        <?php if (empty($recentRequests)): ?>
            <div class="text-center py-8">
                <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-gray-500 dark:text-gray-400">No requests yet</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                        <?php foreach ($recentRequests as $request): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 text-sm font-medium text-gray-800 dark:text-gray-100">
                                    <?php echo htmlspecialchars($request['type']); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium <?php 
                                        echo $request['status'] === 'Complete' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 
                                            ($request['status'] === 'In Progress' ? 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200' : 
                                            ($request['status'] === 'Cancelled' ? 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' : 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200'));
                                    ?>">
                                        <?php echo htmlspecialchars($request['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                    <?php echo date('M d, Y', strtotime($request['created_at'])); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>