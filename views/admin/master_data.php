<?php
// /views/admin/master_data.php
// Master Data Management (Admin Only)

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../db/Database.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

$auth = new AuthController();
$auth->requireRole('Admin');

$pageTitle = 'Master Data Management';
require_once __DIR__ . '/../layout/header.php';

$db = Database::getInstance();

$message = null;
$messageType = 'success';

// Get selected table
$selectedTable = $_GET['table'] ?? 'prefix_master';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $table = $_POST['table'] ?? '';
    
    if ($action === 'add') {
        try {
            if ($table === 'prefix_master') {
                $sql = "INSERT INTO prefix_master (prefix_name_th, prefix_name_en, prefix_name_my) VALUES (?, ?, ?)";
                $db->query($sql, [$_POST['prefix_name_th'], $_POST['prefix_name_en'], $_POST['prefix_name_my']]);
            } elseif ($table === 'function_master') {
                $sql = "INSERT INTO function_master (function_name) VALUES (?)";
                $db->query($sql, [$_POST['function_name']]);
            } elseif ($table === 'division_master') {
                $sql = "INSERT INTO division_master (division_name) VALUES (?)";
                $db->query($sql, [$_POST['division_name']]);
            } elseif ($table === 'department_master') {
                $sql = "INSERT INTO department_master (department_name, division_id) VALUES (?, ?)";
                $db->query($sql, [$_POST['department_name'], $_POST['division_id'] ?: null]);
            } elseif ($table === 'section_master') {
                $sql = "INSERT INTO section_master (section_name, department_id) VALUES (?, ?)";
                $db->query($sql, [$_POST['section_name'], $_POST['department_id'] ?: null]);
            } elseif ($table === 'position_master') {
                $sql = "INSERT INTO position_master (position_name) VALUES (?)";
                $db->query($sql, [$_POST['position_name']]);
            } elseif ($table === 'service_category_master') {
                $sql = "INSERT INTO service_category_master (category_name_th, category_name_en, category_name_my) VALUES (?, ?, ?)";
                $db->query($sql, [$_POST['category_name_th'], $_POST['category_name_en'], $_POST['category_name_my']]);
            } elseif ($table === 'service_type_master') {
                $sql = "INSERT INTO service_type_master (type_name_th, type_name_en, type_name_my) VALUES (?, ?, ?)";
                $db->query($sql, [$_POST['type_name_th'], $_POST['type_name_en'], $_POST['type_name_my']]);
            } elseif ($table === 'doc_type_master') {
                $sql = "INSERT INTO doc_type_master (doc_type_name) VALUES (?)";
                $db->query($sql, [$_POST['doc_type_name']]);
            }
            
            $message = 'Record added successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Failed to add record: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'delete') {
        try {
            $id = $_POST['id'] ?? '';
            $idColumn = $_POST['id_column'] ?? '';
            
            $sql = "DELETE FROM {$table} WHERE {$idColumn} = ?";
            $db->query($sql, [$id]);
            
            $message = 'Record deleted successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Failed to delete record: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
    
    $selectedTable = $table;
}

// Master data tables configuration
$tables = [
    'prefix_master' => [
        'name' => 'Prefix',
        'id_column' => 'prefix_id',
        'columns' => ['prefix_id', 'prefix_name_th', 'prefix_name_en', 'prefix_name_my']
    ],
    'function_master' => [
        'name' => 'Function',
        'id_column' => 'function_id',
        'columns' => ['function_id', 'function_name']
    ],
    'division_master' => [
        'name' => 'Division',
        'id_column' => 'division_id',
        'columns' => ['division_id', 'division_name']
    ],
    'department_master' => [
        'name' => 'Department',
        'id_column' => 'department_id',
        'columns' => ['department_id', 'department_name', 'division_id']
    ],
    'section_master' => [
        'name' => 'Section',
        'id_column' => 'section_id',
        'columns' => ['section_id', 'section_name', 'department_id']
    ],
    'position_master' => [
        'name' => 'Position',
        'id_column' => 'position_id',
        'columns' => ['position_id', 'position_name']
    ],
    'service_category_master' => [
        'name' => 'Service Category',
        'id_column' => 'category_id',
        'columns' => ['category_id', 'category_name_th', 'category_name_en', 'category_name_my']
    ],
    'service_type_master' => [
        'name' => 'Service Type',
        'id_column' => 'type_id',
        'columns' => ['type_id', 'type_name_th', 'type_name_en', 'type_name_my']
    ],
    'doc_type_master' => [
        'name' => 'Document Type',
        'id_column' => 'doc_type_id',
        'columns' => ['doc_type_id', 'doc_type_name']
    ]
];

// Fetch data for selected table
$tableConfig = $tables[$selectedTable];
$data = $db->fetchAll("SELECT * FROM {$selectedTable} ORDER BY {$tableConfig['id_column']}");

// Get related data for dropdowns
$divisions = $db->fetchAll("SELECT * FROM division_master ORDER BY division_name");
$departments = $db->fetchAll("SELECT * FROM department_master ORDER BY department_name");
?>

<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Master Data Management</h1>
        <p class="text-gray-600 mt-1">Manage dropdown values and reference data</p>
    </div>
    
    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500'; ?>">
            <p class="<?php echo $messageType === 'success' ? 'text-green-700' : 'text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        </div>
    <?php endif; ?>
    
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Table Selector Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-lg p-4">
                <h3 class="font-semibold text-gray-800 mb-3">Select Table</h3>
                <ul class="space-y-1">
                    <?php foreach ($tables as $key => $config): ?>
                        <li>
                            <a href="?table=<?php echo $key; ?>" 
                               class="block px-3 py-2 rounded-lg <?php echo $selectedTable === $key ? 'bg-blue-600 text-white' : 'hover:bg-gray-100 text-gray-700'; ?>">
                                <?php echo $config['name']; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="lg:col-span-3">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-800"><?php echo $tableConfig['name']; ?> Data</h2>
                    <button onclick="openAddModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                        + Add New
                    </button>
                </div>
                
                <!-- Data Table -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <?php foreach ($tableConfig['columns'] as $col): ?>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        <?php echo str_replace('_', ' ', $col); ?>
                                    </th>
                                <?php endforeach; ?>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (empty($data)): ?>
                                <tr>
                                    <td colspan="<?php echo count($tableConfig['columns']) + 1; ?>" class="px-6 py-8 text-center text-gray-500">
                                        No data found
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($data as $row): ?>
                                    <tr class="hover:bg-gray-50">
                                        <?php foreach ($tableConfig['columns'] as $col): ?>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                <?php echo htmlspecialchars($row[$col] ?? 'N/A'); ?>
                                            </td>
                                        <?php endforeach; ?>
                                        <td class="px-6 py-4">
                                            <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this record?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="table" value="<?php echo $selectedTable; ?>">
                                                <input type="hidden" name="id" value="<?php echo $row[$tableConfig['id_column']]; ?>">
                                                <input type="hidden" name="id_column" value="<?php echo $tableConfig['id_column']; ?>">
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
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
        </div>
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-2xl w-full p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Add New <?php echo $tableConfig['name']; ?></h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="table" value="<?php echo $selectedTable; ?>">
            
            <div class="space-y-4">
                <?php if ($selectedTable === 'prefix_master'): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Thai Name</label>
                        <input type="text" name="prefix_name_th" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">English Name</label>
                        <input type="text" name="prefix_name_en" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Myanmar Name</label>
                        <input type="text" name="prefix_name_my" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                <?php elseif ($selectedTable === 'function_master'): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Function Name</label>
                        <input type="text" name="function_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                <?php elseif ($selectedTable === 'division_master'): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Division Name</label>
                        <input type="text" name="division_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                <?php elseif ($selectedTable === 'department_master'): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Department Name</label>
                        <input type="text" name="department_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Division</label>
                        <select name="division_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Division (Optional)</option>
                            <?php foreach ($divisions as $div): ?>
                                <option value="<?php echo $div['division_id']; ?>"><?php echo htmlspecialchars($div['division_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                <?php elseif ($selectedTable === 'section_master'): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Section Name</label>
                        <input type="text" name="section_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                        <select name="department_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Department (Optional)</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['department_id']; ?>"><?php echo htmlspecialchars($dept['department_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                <?php elseif ($selectedTable === 'position_master'): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Position Name</label>
                        <input type="text" name="position_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                <?php elseif ($selectedTable === 'service_category_master'): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Thai Name</label>
                        <input type="text" name="category_name_th" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">English Name</label>
                        <input type="text" name="category_name_en" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Myanmar Name</label>
                        <input type="text" name="category_name_my" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                <?php elseif ($selectedTable === 'service_type_master'): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Thai Name</label>
                        <input type="text" name="type_name_th" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">English Name</label>
                        <input type="text" name="type_name_en" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Myanmar Name</label>
                        <input type="text" name="type_name_my" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                <?php elseif ($selectedTable === 'doc_type_master'): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Document Type Name</label>
                        <input type="text" name="doc_type_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="flex gap-3 mt-6">
                <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
                    Add
                </button>
                <button type="button" onclick="closeAddModal()" class="flex-1 bg-gray-500 text-white py-2 rounded-lg hover:bg-gray-600 transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddModal() {
        document.getElementById('addModal').classList.remove('hidden');
    }
    
    function closeAddModal() {
        document.getElementById('addModal').classList.add('hidden');
    }
    
    // Close modal when clicking outside
    document.getElementById('addModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeAddModal();
        }
    });
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>