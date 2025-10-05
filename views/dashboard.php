<?php
// /views/dashboard.php
// Main Dashboard

// โหลด dependencies ก่อนเสมอ
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../db/Database.php';
require_once __DIR__ . '/../db/Localization.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// สร้าง auth instance และเช็ค login
$auth = new AuthController();
$auth->requireLogin();

// Get current user ก่อน
$currentUser = $auth->getCurrentUser();

// Define page title AFTER loading Localization
$pageTitle = get_text('dashboard');

// โหลด header
require_once __DIR__ . '/layout/header.php';

$db = Database::getInstance();

// Get current user
$currentUser = $auth->getCurrentUser();

// Get statistics
try {
    $totalEmployees = $db->fetchOne("SELECT COUNT(*) as count FROM employees WHERE status = 'Active'")['count'];
    $totalRequests = $db->fetchOne("
        SELECT 
            (SELECT COUNT(*) FROM leave_requests WHERE employee_id = ?) +
            (SELECT COUNT(*) FROM certificate_requests WHERE employee_id = ?) +
            (SELECT COUNT(*) FROM id_card_requests WHERE employee_id = ?) +
            (SELECT COUNT(*) FROM shuttle_bus_requests WHERE employee_id = ?) +
            (SELECT COUNT(*) FROM locker_usage_requests WHERE employee_id = ?) +
            (SELECT COUNT(*) FROM supplies_requests WHERE employee_id = ?) +
            (SELECT COUNT(*) FROM skill_test_requests WHERE employee_id = ?) +
            (SELECT COUNT(*) FROM qr_document_submissions WHERE employee_id = ?) as total
    ", array_fill(0, 8, $_SESSION['user_id']))['total'];
    
    $pendingRequests = $db->fetchOne("
        SELECT 
            (SELECT COUNT(*) FROM leave_requests WHERE employee_id = ? AND status IN ('New', 'In Progress')) +
            (SELECT COUNT(*) FROM certificate_requests WHERE employee_id = ? AND status IN ('New', 'In Progress')) +
            (SELECT COUNT(*) FROM id_card_requests WHERE employee_id = ? AND status IN ('New', 'In Progress')) +
            (SELECT COUNT(*) FROM shuttle_bus_requests WHERE employee_id = ? AND status IN ('New', 'In Progress')) +
            (SELECT COUNT(*) FROM locker_usage_requests WHERE employee_id = ? AND status IN ('New', 'In Progress')) +
            (SELECT COUNT(*) FROM supplies_requests WHERE employee_id = ? AND status IN ('New', 'In Progress')) +
            (SELECT COUNT(*) FROM skill_test_requests WHERE employee_id = ? AND status IN ('New', 'In Progress')) +
            (SELECT COUNT(*) FROM qr_document_submissions WHERE employee_id = ? AND status IN ('New', 'In Progress')) as total
    ", array_fill(0, 8, $_SESSION['user_id']))['total'];
    
    // Get recent requests
    $recentLeaveRequests = $db->fetchAll("
        SELECT 'Leave' as type, request_id, status, created_at 
        FROM leave_requests 
        WHERE employee_id = ? 
        ORDER BY created_at DESC 
        LIMIT 5
    ", [$_SESSION['user_id']]);
    
} catch (Exception $e) {
    $totalEmployees = 0;
    $totalRequests = 0;
    $pendingRequests = 0;
    $recentLeaveRequests = [];
}
?>

<div class="space-y-6">
    <!-- Welcome Banner -->
    <div class="theme-bg rounded-lg shadow-lg p-6 text-white">
        <h1 class="text-3xl font-bold mb-2"><?php echo get_text('welcome'); ?>, <?php echo htmlspecialchars($currentUser['full_name_en']); ?>!</h1>
        <p class="opacity-90">Employee ID: <?php echo htmlspecialchars($currentUser['employee_id']); ?> | <?php echo htmlspecialchars($currentUser['position_name'] ?? 'N/A'); ?></p>
    </div>
    
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Requests -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm"><?php echo get_text('requests'); ?></p>
                    <p class="text-3xl font-bold text-gray-800 mt-1"><?php echo $totalRequests; ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Pending Requests -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm"><?php echo get_text('status_in_progress'); ?></p>
                    <p class="text-3xl font-bold text-orange-600 mt-1"><?php echo $pendingRequests; ?></p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Total Employees (Admin/Officer only) -->
        <?php if ($auth->hasRole(['Admin', 'Officer'])): ?>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm"><?php echo get_text('employees'); ?></p>
                    <p class="text-3xl font-bold text-green-600 mt-1"><?php echo $totalEmployees; ?></p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm"><?php echo get_text('status_complete'); ?></p>
                    <p class="text-3xl font-bold text-green-600 mt-1"><?php echo $totalRequests - $pendingRequests; ?></p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4"><?php echo get_text('new_request'); ?></h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="<?php echo BASE_URL; ?>/views/employee/requests/leave.php" class="flex flex-col items-center p-4 border-2 border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mb-2">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-center"><?php echo get_text('leave_request'); ?></span>
            </a>
            
            <a href="<?php echo BASE_URL; ?>/views/employee/requests/certificate.php" class="flex flex-col items-center p-4 border-2 border-gray-200 rounded-lg hover:border-green-500 hover:bg-green-50 transition">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mb-2">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-center"><?php echo get_text('certificate_request'); ?></span>
            </a>
            
            <a href="<?php echo BASE_URL; ?>/views/employee/requests/id_card.php" class="flex flex-col items-center p-4 border-2 border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition">
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mb-2">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-center"><?php echo get_text('id_card_request'); ?></span>
            </a>
            
            <a href="<?php echo BASE_URL; ?>/views/employee/requests/locker.php" class="flex flex-col items-center p-4 border-2 border-gray-200 rounded-lg hover:border-orange-500 hover:bg-orange-50 transition">
                <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center mb-2">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-center"><?php echo get_text('locker_request'); ?></span>
            </a>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800">Recent Activity</h2>
            <a href="<?php echo BASE_URL; ?>/views/employee/my_requests.php" class="text-sm theme-text hover:underline">View All</a>
        </div>
        
        <?php if (empty($recentLeaveRequests)): ?>
            <p class="text-gray-500 text-center py-8"><?php echo get_text('no_data'); ?></p>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($recentLeaveRequests as $request): ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800"><?php echo htmlspecialchars($request['type']); ?> Request</p>
                                <p class="text-xs text-gray-500"><?php echo date('M d, Y H:i', strtotime($request['created_at'])); ?></p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-medium 
                            <?php 
                                echo $request['status'] === 'Complete' ? 'bg-green-100 text-green-800' : 
                                    ($request['status'] === 'In Progress' ? 'bg-blue-100 text-blue-800' : 
                                    ($request['status'] === 'Cancelled' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'));
                            ?>">
                            <?php echo get_text('status_' . strtolower(str_replace(' ', '_', $request['status']))); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>