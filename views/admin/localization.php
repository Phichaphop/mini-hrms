<?php
// /views/admin/localization.php
// Localization Text Management (Admin Only)

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../db/Database.php';
require_once __DIR__ . '/../../db/Localization.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

$auth = new AuthController();
$auth->requireRole('Admin');

$pageTitle = 'Localization Management';
require_once __DIR__ . '/../layout/header.php';

$db = Database::getInstance();
$localization = new Localization();

$message = null;
$messageType = 'success';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'update') {
        $keyId = $_POST['key_id'] ?? '';
        $thText = $_POST['th_text'] ?? '';
        $enText = $_POST['en_text'] ?? '';
        $myText = $_POST['my_text'] ?? '';
        $category = $_POST['category'] ?? '';
        
        $result = $localization->saveText($keyId, $thText, $enText, $myText, $category);
        
        if ($result) {
            $message = ($action === 'add' ? 'Text added' : 'Text updated') . ' successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to save text';
            $messageType = 'error';
        }
    } elseif ($action === 'delete') {
        $keyId = $_POST['key_id'] ?? '';
        $result = $localization->deleteText($keyId);
        
        if ($result) {
            $message = 'Text deleted successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to delete text';
            $messageType = 'error';
        }
    }
}

// Get filter category
$filterCategory = $_GET['category'] ?? '';

// Fetch all localization data
$whereClause = !empty($filterCategory) ? "WHERE category = ?" : "";
$params = !empty($filterCategory) ? [$filterCategory] : [];
$texts = $db->fetchAll("SELECT * FROM localization_master {$whereClause} ORDER BY category, key_id", $params);

// Get all categories
$categories = $db->fetchAll("SELECT DISTINCT category FROM localization_master ORDER BY category");
?>

<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Localization Management</h1>
        <p class="text-gray-600 mt-1">Manage UI text translations (Thai, English, Burmese)</p>
    </div>
    
    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500'; ?>">
            <p class="<?php echo $messageType === 'success' ? 'text-green-700' : 'text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        </div>
    <?php endif; ?>
    
    <!-- Filter and Add Button -->
    <div class="bg-white rounded-lg shadow-lg p-4 mb-6 flex justify-between items-center">
        <form method="GET" class="flex gap-4">
            <select name="category" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat['category']); ?>" <?php echo $filterCategory === $cat['category'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['category']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                Filter
            </button>
        </form>
        
        <button onclick="openAddModal()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
            + Add New Text
        </button>
    </div>
    
    <!-- Localization Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Key ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Thai (TH)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">English (EN)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Burmese (MY)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($texts)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                No localization texts found
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($texts as $text): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($text['key_id']); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <?php echo htmlspecialchars($text['th_text'] ?? ''); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <?php echo htmlspecialchars($text['en_text'] ?? ''); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <?php echo htmlspecialchars($text['my_text'] ?? ''); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">
                                        <?php echo htmlspecialchars($text['category']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm space-x-2">
                                    <button onclick='openEditModal(<?php echo json_encode($text); ?>)' class="text-blue-600 hover:text-blue-800 font-medium">
                                        Edit
                                    </button>
                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this text?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="key_id" value="<?php echo htmlspecialchars($text['key_id']); ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-800 font-medium">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Stats -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Total Texts</div>
            <div class="text-2xl font-bold text-gray-800"><?php echo count($texts); ?></div>
        </div>
        <?php foreach ($categories as $cat): ?>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($cat['category']); ?></div>
                <div class="text-2xl font-bold text-blue-600">
                    <?php echo count(array_filter($texts, fn($t) => $t['category'] === $cat['category'])); ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="textModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-3xl w-full p-6">
        <h3 id="modalTitle" class="text-xl font-bold text-gray-800 mb-4">Add New Text</h3>
        <form method="POST" id="textForm">
            <input type="hidden" name="action" id="formAction" value="add">
            
            <div class="space-y-4">
                <div>
                    <label for="key_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Key ID <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="key_id" name="key_id" required 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="e.g., dashboard, login, submit">
                    <p class="text-xs text-gray-500 mt-1">Unique identifier for this text (no spaces, use underscore)</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="th_text" class="block text-sm font-medium text-gray-700 mb-2">
                            Thai Text (TH) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="th_text" name="th_text" required 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                               placeholder="ข้อความภาษาไทย">
                    </div>
                    
                    <div>
                        <label for="en_text" class="block text-sm font-medium text-gray-700 mb-2">
                            English Text (EN) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="en_text" name="en_text" required 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                               placeholder="English text">
                    </div>
                    
                    <div>
                        <label for="my_text" class="block text-sm font-medium text-gray-700 mb-2">
                            Burmese Text (MY) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="my_text" name="my_text" required 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                               placeholder="မြန်မာစာသား">
                    </div>
                </div>
                
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                        Category <span class="text-red-500">*</span>
                    </label>
                    <select id="category" name="category" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Category</option>
                        <option value="auth">Authentication</option>
                        <option value="menu">Menu</option>
                        <option value="general">General</option>
                        <option value="field">Form Fields</option>
                        <option value="request">Requests</option>
                        <option value="status">Status</option>
                        <option value="action">Actions</option>
                        <option value="message">Messages</option>
                        <option value="setting">Settings</option>
                    </select>
                </div>
            </div>
            
            <div class="flex gap-3 mt-6">
                <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
                    Save
                </button>
                <button type="button" onclick="closeModal()" class="flex-1 bg-gray-500 text-white py-2 rounded-lg hover:bg-gray-600 transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'Add New Text';
        document.getElementById('formAction').value = 'add';
        document.getElementById('key_id').readOnly = false;
        document.getElementById('textForm').reset();
        document.getElementById('textModal').classList.remove('hidden');
    }
    
    function openEditModal(data) {
        document.getElementById('modalTitle').textContent = 'Edit Text';
        document.getElementById('formAction').value = 'update';
        document.getElementById('key_id').value = data.key_id;
        document.getElementById('key_id').readOnly = true;
        document.getElementById('th_text').value = data.th_text || '';
        document.getElementById('en_text').value = data.en_text || '';
        document.getElementById('my_text').value = data.my_text || '';
        document.getElementById('category').value = data.category || '';
        document.getElementById('textModal').classList.remove('hidden');
    }
    
    function closeModal() {
        document.getElementById('textModal').classList.add('hidden');
    }
    
    // Close modal when clicking outside
    document.getElementById('textModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>