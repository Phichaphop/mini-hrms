<?php
// /views/admin/locker_management.php

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../db/Database.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

$auth = new AuthController();
$auth->requireRole('Admin');

$pageTitle = 'Locker Management';
require_once __DIR__ . '/../layout/header.php';

$db = Database::getInstance();
$message = null;
$messageType = 'success';

// Handle CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'add') {
            $sql = "INSERT INTO locker_master (locker_number, status, location) VALUES (?, ?, ?)";
            $db->query($sql, [$_POST['locker_number'], $_POST['status'], $_POST['location']]);
            $message = 'Locker added successfully!';
        } elseif ($action === 'update') {
            $sql = "UPDATE locker_master SET locker_number = ?, status = ?, location = ? WHERE locker_id = ?";
            $db->query($sql, [$_POST['locker_number'], $_POST['status'], $_POST['location'], $_POST['locker_id']]);
            $message = 'Locker updated successfully!';
        } elseif ($action === 'delete') {
            $sql = "DELETE FROM locker_master WHERE locker_id = ?";
            $db->query($sql, [$_POST['locker_id']]);
            $message = 'Locker deleted successfully!';
        }
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

$lockers = $db->fetchAll("
    SELECT l.*, e.full_name_en as owner_name 
    FROM locker_master l 
    LEFT JOIN employees e ON l.current_owner_id = e.employee_id 
    ORDER BY l.locker_number
");
?>

<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Locker Management</h1>
        <button onclick="openAddModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
            + Add Locker
        </button>
    </div>
    
    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500'; ?>">
            <p class="<?php echo $messageType === 'success' ? 'text-green-700' : 'text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        </div>
    <?php endif; ?>
    
    <!-- Lockers Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Locker Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Current Owner</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                <?php foreach ($lockers as $locker): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100"><?php echo htmlspecialchars($locker['locker_number']); ?></td>
                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300"><?php echo htmlspecialchars($locker['location']); ?></td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-medium <?php 
                                echo $locker['status'] === 'Available' ? 'bg-green-100 text-green-800' : 
                                    ($locker['status'] === 'Occupied' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800');
                            ?>">
                                <?php echo htmlspecialchars($locker['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300"><?php echo htmlspecialchars($locker['owner_name'] ?? '-'); ?></td>
                        <td class="px-6 py-4">
                            <button onclick='openEditModal(<?php echo json_encode($locker); ?>)' class="text-blue-600 hover:text-blue-800 mr-3">Edit</button>
                            <form method="POST" class="inline" onsubmit="return confirm('Delete this locker?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="locker_id" value="<?php echo $locker['locker_id']; ?>">
                                <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="lockerModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg max-w-md w-full p-6">
        <h3 id="modalTitle" class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">Add Locker</h3>
        <form method="POST">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="locker_id" id="locker_id">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Locker Number</label>
                    <input type="text" name="locker_number" id="locker_number" required class<div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Location</label>
                <input type="text" name="location" id="location" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                <select name="status" id="status" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="Available">Available</option>
                    <option value="Occupied">Occupied</option>
                    <option value="Maintenance">Maintenance</option>
                </select>
            </div>
        </div>
        
        <div class="flex gap-3 mt-6">
            <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">Save</button>
            <button type="button" onclick="closeModal()" class="flex-1 bg-gray-500 text-white py-2 rounded-lg hover:bg-gray-600 transition">Cancel</button>
        </div>
    </form>
</div>
</div>
<script>
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Locker';
    document.getElementById('formAction').value = 'add';
    document.getElementById('locker_id').value = '';
    document.getElementById('locker_number').value = '';
    document.getElementById('location').value = '';
    document.getElementById('status').value = 'Available';
    document.getElementById('lockerModal').classList.remove('hidden');
}

function openEditModal(locker) {
    document.getElementById('modalTitle').textContent = 'Edit Locker';
    document.getElementById('formAction').value = 'update';
    document.getElementById('locker_id').value = locker.locker_id;
    document.getElementById('locker_number').value = locker.locker_number;
    document.getElementById('location').value = locker.location;
    document.getElementById('status').value = locker.status;
    document.getElementById('lockerModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('lockerModal').classList.add('hidden');
}

document.getElementById('lockerModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>

### เพิ่ม Menu ใน header.php:
```php
<?php if ($auth->hasRole('Admin')): ?>
<li>
    <a href="<?php echo BASE_URL; ?>/views/admin/locker_management.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
        <span>Locker Management</span>
    </a>
</li>
<?php endif; ?>