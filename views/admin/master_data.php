<?php
// /views/admin/master_data.php
// Master Data Management - COMPLETE VERSION with New Tables

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../db/Database.php';
require_once __DIR__ . '/../../db/Localization.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

$auth = new AuthController();
$auth->requireRole('Admin');

$pageTitle = 'Master Data Management';
require_once __DIR__ . '/../layout/header.php';

$db = Database::getInstance();
$message = null;
$messageType = 'success';

// Get current table from URL
$currentTable = $_GET['table'] ?? 'prefix_master';

// Define all master tables with their configurations
$masterTables = [
    'prefix_master' => [
        'name' => 'Prefix',
        'icon' => '👤',
        'columns' => ['prefix_id', 'prefix_name_th', 'prefix_name_en', 'prefix_name_my'],
        'labels' => ['ID', 'Thai', 'English', 'Myanmar'],
        'primary_key' => 'prefix_id',
        'editable' => ['prefix_name_th', 'prefix_name_en', 'prefix_name_my']
    ],
    'function_master' => [
        'name' => 'Function',
        'icon' => '🏢',
        'columns' => ['function_id', 'function_name'],
        'labels' => ['ID', 'Function Name'],
        'primary_key' => 'function_id',
        'editable' => ['function_name']
    ],
    'division_master' => [
        'name' => 'Division',
        'icon' => '🏛️',
        'columns' => ['division_id', 'division_name'],
        'labels' => ['ID', 'Division Name'],
        'primary_key' => 'division_id',
        'editable' => ['division_name']
    ],
    'department_master' => [
        'name' => 'Department',
        'icon' => '🏬',
        'columns' => ['department_id', 'department_name', 'division_id'],
        'labels' => ['ID', 'Department Name', 'Division ID'],
        'primary_key' => 'department_id',
        'editable' => ['department_name', 'division_id']
    ],
    'section_master' => [
        'name' => 'Section',
        'icon' => '📁',
        'columns' => ['section_id', 'section_name', 'department_id'],
        'labels' => ['ID', 'Section Name', 'Department ID'],
        'primary_key' => 'section_id',
        'editable' => ['section_name', 'department_id']
    ],
    'position_master' => [
        'name' => 'Position',
        'icon' => '💼',
        'columns' => ['position_id', 'position_name'],
        'labels' => ['ID', 'Position Name'],
        'primary_key' => 'position_id',
        'editable' => ['position_name']
    ],
    'position_level_master' => [
        'name' => 'Position Level',
        'icon' => '📊',
        'columns' => ['level_id', 'level_name'],
        'labels' => ['ID', 'Level Name'],
        'primary_key' => 'level_id',
        'editable' => ['level_name']
    ],
    'education_level_master' => [
        'name' => 'Education Level',
        'icon' => '🎓',
        'columns' => ['education_id', 'education_name'],
        'labels' => ['ID', 'Education Level'],
        'primary_key' => 'education_id',
        'editable' => ['education_name']
    ],
    'operation_master' => [
        'name' => 'Operation',
        'icon' => '⚙️',
        'columns' => ['operation_id', 'operation_name'],
        'labels' => ['ID', 'Operation Name'],
        'primary_key' => 'operation_id',
        'editable' => ['operation_name']
    ],
    'hiring_type_master' => [
        'name' => 'Hiring Type',
        'icon' => '📝',
        'columns' => ['hiring_type_id', 'hiring_type_name_th', 'hiring_type_name_en', 'hiring_type_name_my'],
        'labels' => ['ID', 'Thai', 'English', 'Myanmar'],
        'primary_key' => 'hiring_type_id',
        'editable' => ['hiring_type_name_th', 'hiring_type_name_en', 'hiring_type_name_my']
    ],
    'customer_zone_master' => [
        'name' => 'Customer Zone',
        'icon' => '🌍',
        'columns' => ['zone_id', 'zone_name'],
        'labels' => ['ID', 'Zone Name'],
        'primary_key' => 'zone_id',
        'editable' => ['zone_name']
    ],
    'contribution_level_master' => [
        'name' => 'Contribution Level',
        'icon' => '⭐',
        'columns' => ['level_id', 'level_name'],
        'labels' => ['ID', 'Level Name'],
        'primary_key' => 'level_id',
        'editable' => ['level_name']
    ],
    'leave_type_master' => [
        'name' => 'Leave Type',
        'icon' => '🏖️',
        'columns' => ['leave_type_id', 'leave_type_name_th', 'leave_type_name_en', 'leave_type_name_my'],
        'labels' => ['ID', 'Thai', 'English', 'Myanmar'],
        'primary_key' => 'leave_type_id',
        'editable' => ['leave_type_name_th', 'leave_type_name_en', 'leave_type_name_my']
    ],
    'service_category_master' => [
        'name' => 'Service Category',
        'icon' => '🛎️',
        'columns' => ['category_id', 'category_name_th', 'category_name_en', 'category_name_my'],
        'labels' => ['ID', 'Thai', 'English', 'Myanmar'],
        'primary_key' => 'category_id',
        'editable' => ['category_name_th', 'category_name_en', 'category_name_my']
    ],
    'service_type_master' => [
        'name' => 'Service Type',
        'icon' => '🏷️',
        'columns' => ['type_id', 'type_name_th', 'type_name_en', 'type_name_my'],
        'labels' => ['ID', 'Thai', 'English', 'Myanmar'],
        'primary_key' => 'type_id',
        'editable' => ['type_name_th', 'type_name_en', 'type_name_my']
    ],
    'doc_type_master' => [
        'name' => 'Document Type',
        'icon' => '📄',
        'columns' => ['doc_type_id', 'doc_type_name'],
        'labels' => ['ID', 'Document Type'],
        'primary_key' => 'doc_type_id',
        'editable' => ['doc_type_name']
    ]
];

// Validate current table
if (!isset($masterTables[$currentTable])) {
    $currentTable = 'prefix_master';
}

$tableConfig = $masterTables[$currentTable];

// Handle CRUD Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'add') {
            $columns = [];
            $values = [];
            $placeholders = [];
            
            foreach ($tableConfig['editable'] as $column) {
                $columns[] = $column;
                $values[] = $_POST[$column] ?? '';
                $placeholders[] = '?';
            }
            
            $sql = "INSERT INTO $currentTable (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $db->query($sql, $values);
            
            $message = 'Record added successfully!';
            
        } elseif ($action === 'edit') {
            $primaryKey = $tableConfig['primary_key'];
            $id = $_POST[$primaryKey] ?? '';
            
            $setParts = [];
            $values = [];
            
            foreach ($tableConfig['editable'] as $column) {
                $setParts[] = "$column = ?";
                $values[] = $_POST[$column] ?? '';
            }
            
            $values[] = $id;
            
            $sql = "UPDATE $currentTable SET " . implode(', ', $setParts) . " WHERE $primaryKey = ?";
            $db->query($sql, $values);
            
            $message = 'Record updated successfully!';
            
        } elseif ($action === 'delete') {
            $primaryKey = $tableConfig['primary_key'];
            $id = $_POST[$primaryKey] ?? '';
            
            $sql = "DELETE FROM $currentTable WHERE $primaryKey = ?";
            $db->query($sql, [$id]);
            
            $message = 'Record deleted successfully!';
        }
        
        $messageType = 'success';
        
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Fetch data from current table
$data = $db->fetchAll("SELECT * FROM $currentTable ORDER BY {$tableConfig['primary_key']}");
?>

<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Master Data Management</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Manage system master data and lookup tables</p>
    </div>
    
    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500' : 'bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500'; ?>">
            <p class="<?php echo $messageType === 'success' ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        </div>
    <?php endif; ?>
    
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Sidebar - Table List -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-4">
                <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4">Master Tables</h2>
                <nav class="space-y-1">
                    <?php foreach ($masterTables as $tableName => $config): ?>
                        <a href="?table=<?php echo $tableName; ?>" 
                           class="flex items-center space-x-3 px-3 py-2 rounded-lg transition <?php echo $currentTable === $tableName ? 'bg-blue-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'; ?>">
                            <span class="text-xl"><?php echo $config['icon']; ?></span>
                            <span class="text-sm font-medium"><?php echo $config['name']; ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>
        </div>
        
        <!-- Main Content - Data Table -->
        <div class="lg:col-span-3">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">
                        <?php echo $tableConfig['icon']; ?> <?php echo $tableConfig['name']; ?>
                    </h2>
                    <button onclick="openAddModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                        + Add New
                    </button>
                </div>
                
                <!-- Data Table -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                            <tr>
                                <?php foreach ($tableConfig['labels'] as $label): ?>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"><?php echo $label; ?></th>
                                <?php endforeach; ?>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                            <?php if (empty($data)): ?>
                                <tr>
                                    <td colspan="<?php echo count($tableConfig['columns']) + 1; ?>" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        No data available. Click "Add New" to create your first record.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($data as $row): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <?php foreach ($tableConfig['columns'] as $column): ?>
                                            <td class="px-4 py-3 text-sm text-gray-800 dark:text-gray-100">
                                                <?php echo htmlspecialchars($row[$column] ?? ''); ?>
                                            </td>
                                        <?php endforeach; ?>
                                        <td class="px-4 py-3 text-sm">
                                            <button onclick='openEditModal(<?php echo json_encode($row); ?>)' class="text-blue-600 hover:text-blue-800 dark:text-blue-400 mr-3">
                                                Edit
                                            </button>
                                            <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this record?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="<?php echo $tableConfig['primary_key']; ?>" value="<?php echo $row[$tableConfig['primary_key']]; ?>">
                                                <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400">
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

<!-- Add/Edit Modal -->
<div id="dataModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
        <h3 id="modalTitle" class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">Add New Record</h3>
        
        <form method="POST" id="dataForm">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="<?php echo $tableConfig['primary_key']; ?>" id="primaryKeyValue">
            
            <div class="space-y-4">
                <?php foreach ($tableConfig['editable'] as $column): ?>
                    <div>
                        <label for="field_<?php echo $column; ?>" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <?php echo ucwords(str_replace('_', ' ', $column)); ?>
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="field_<?php echo $column; ?>" 
                               name="<?php echo $column; ?>" 
                               required 
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                <?php endforeach; ?>
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
const editableFields = <?php echo json_encode($tableConfig['editable']); ?>;
const primaryKey = '<?php echo $tableConfig['primary_key']; ?>';

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add New Record';
    document.getElementById('formAction').value = 'add';
    document.getElementById('primaryKeyValue').value = '';
    
    // Clear all fields
    editableFields.forEach(field => {
        document.getElementById('field_' + field).value = '';
    });
    
    document.getElementById('dataModal').classList.remove('hidden');
}

function openEditModal(data) {
    document.getElementById('modalTitle').textContent = 'Edit Record';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('primaryKeyValue').value = data[primaryKey];
    
    // Fill fields with data
    editableFields.forEach(field => {
        const element = document.getElementById('field_' + field);
        if (element && data[field] !== undefined) {
            element.value = data[field];
        }
    });
    
    document.getElementById('dataModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('dataModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('dataModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>