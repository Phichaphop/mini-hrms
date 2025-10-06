<?php
// /views/admin/document_management.php
// Document Management (Officer can view/download, Admin has full access)

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../db/Database.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

$auth = new AuthController();
$auth->requireRole(['Admin', 'Officer']);

$pageTitle = 'Document Management';
require_once __DIR__ . '/../layout/header.php';

$db = Database::getInstance();
$message = null;
$messageType = 'success';

// Handle Upload (Admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
    if ($auth->hasRole('Admin')) {
        $fileName = $_POST['file_name'] ?? '';
        $docTypeId = $_POST['doc_type_id'] ?? '';
        
        if (empty($fileName) || empty($docTypeId) || !isset($_FILES['document'])) {
            $message = 'Please fill all required fields';
            $messageType = 'error';
        } else {
            try {
                if ($_FILES['document']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../../assets/uploads/documents/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    $fileExt = pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION);
                    $newFileName = uniqid() . '.' . $fileExt;
                    $targetPath = $uploadDir . $newFileName;
                    
                    if (move_uploaded_file($_FILES['document']['tmp_name'], $targetPath)) {
                        $filePath = '/assets/uploads/documents/' . $newFileName;
                        
                        $sql = "INSERT INTO document_management (file_name_custom, file_path, doc_type_id, uploaded_by) 
                                VALUES (?, ?, ?, ?)";
                        $db->query($sql, [$fileName, $filePath, $docTypeId, $_SESSION['user_id']]);
                        
                        $message = 'Document uploaded successfully!';
                        $messageType = 'success';
                    }
                }
            } catch (Exception $e) {
                $message = 'Upload failed: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

// Handle Delete (Admin only)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if ($auth->hasRole('Admin')) {
        try {
            $docId = $_GET['id'];
            
            // Get file path before deleting
            $doc = $db->fetchOne("SELECT file_path FROM document_management WHERE doc_id = ?", [$docId]);
            if ($doc) {
                // Delete file from server
                $filePath = __DIR__ . '/../../' . $doc['file_path'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                
                // Delete from database
                $db->query("DELETE FROM document_management WHERE doc_id = ?", [$docId]);
                $message = 'Document deleted successfully!';
                $messageType = 'success';
            }
        } catch (Exception $e) {
            $message = 'Delete failed: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Fetch all documents
$documents = $db->fetchAll("
    SELECT d.*, dt.doc_type_name, e.full_name_en as uploaded_by_name
    FROM document_management d
    LEFT JOIN doc_type_master dt ON d.doc_type_id = dt.doc_type_id
    LEFT JOIN employees e ON d.uploaded_by = e.employee_id
    ORDER BY d.upload_at DESC
");

// Get document types for upload form
$docTypes = $db->fetchAll("SELECT * FROM doc_type_master ORDER BY doc_type_name");
?>

<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Document Management</h1>
            <p class="text-gray-600 mt-1">View and manage company documents</p>
        </div>
        <?php if ($auth->hasRole('Admin')): ?>
        <button onclick="openUploadModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
            + Upload Document
        </button>
        <?php endif; ?>
    </div>
    
    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500'; ?>">
            <p class="<?php echo $messageType === 'success' ? 'text-green-700' : 'text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        </div>
    <?php endif; ?>
    
    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Total Documents</div>
            <div class="text-2xl font-bold text-gray-800"><?php echo count($documents); ?></div>
        </div>
        <?php foreach ($docTypes as $type): ?>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($type['doc_type_name']); ?></div>
            <div class="text-2xl font-bold text-blue-600">
                <?php echo count(array_filter($documents, fn($d) => $d['doc_type_id'] == $type['doc_type_id'])); ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Documents Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Document Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Uploaded By</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Upload Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($documents)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                No documents available
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($documents as $doc): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="font-medium text-gray-900"><?php echo htmlspecialchars($doc['file_name_custom']); ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">
                                        <?php echo htmlspecialchars($doc['doc_type_name']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?php echo htmlspecialchars($doc['uploaded_by_name']); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?php echo date('M d, Y H:i', strtotime($doc['upload_at'])); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex space-x-2">
                                        <a href="<?php echo BASE_URL . $doc['file_path']; ?>" 
                                           target="_blank" 
                                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            Download
                                        </a>
                                        <?php if ($auth->hasRole('Admin')): ?>
                                        <a href="?action=delete&id=<?php echo $doc['doc_id']; ?>" 
                                           onclick="return confirm('Are you sure you want to delete this document?')" 
                                           class="text-red-600 hover:text-red-800 text-sm font-medium">
                                            Delete
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($auth->hasRole('Admin')): ?>
<!-- Upload Modal -->
<div id="uploadModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-2xl w-full p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Upload Document</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="upload">
            
            <div class="space-y-4">
                <div>
                    <label for="file_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Document Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="file_name" 
                           name="file_name" 
                           required 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="e.g., Company Policy 2025">
                </div>
                
                <div>
                    <label for="doc_type_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Document Type <span class="text-red-500">*</span>
                    </label>
                    <select id="doc_type_id" 
                            name="doc_type_id" 
                            required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Type</option>
                        <?php foreach ($docTypes as $type): ?>
                            <option value="<?php echo $type['doc_type_id']; ?>">
                                <?php echo htmlspecialchars($type['doc_type_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="document" class="block text-sm font-medium text-gray-700 mb-2">
                        Upload File <span class="text-red-500">*</span>
                    </label>
                    <input type="file" 
                           id="document" 
                           name="document" 
                           required 
                           accept=".pdf,.doc,.docx,.xlsx,.xls,.ppt,.pptx"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Supported: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX (Max 10MB)</p>
                </div>
            </div>
            
            <div class="flex gap-3 mt-6">
                <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
                    Upload
                </button>
                <button type="button" onclick="closeUploadModal()" class="flex-1 bg-gray-500 text-white py-2 rounded-lg hover:bg-gray-600 transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openUploadModal() {
        document.getElementById('uploadModal').classList.remove('hidden');
    }
    
    function closeUploadModal() {
        document.getElementById('uploadModal').classList.add('hidden');
    }
    
    document.getElementById('uploadModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeUploadModal();
        }
    });
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>