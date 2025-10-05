<?php
// /views/employee/settings.php
require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

$auth = new AuthController();
$auth->requireLogin();

$pageTitle = 'Settings';
require_once __DIR__ . '/../layout/header.php';

$currentUser = $auth->getCurrentUser();
$message = null;
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'change_password') {
            $oldPassword = $_POST['old_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if ($newPassword !== $confirmPassword) {
                $message = 'New passwords do not match!';
                $messageType = 'error';
            } elseif (strlen($newPassword) < 6) {
                $message = 'Password must be at least 6 characters!';
                $messageType = 'error';
            } elseif (!password_verify($oldPassword, $currentUser['password'])) {
                $message = 'Current password is incorrect!';
                $messageType = 'error';
            } else {
                require_once __DIR__ . '/../../models/Employee.php';
                $employeeModel = new Employee();
                $result = $employeeModel->update($_SESSION['user_id'], ['password' => $newPassword]);
                
                if ($result['success']) {
                    $message = 'Password changed successfully!';
                    $messageType = 'success';
                } else {
                    $message = $result['message'];
                    $messageType = 'error';
                }
            }
        }
    }
}
?>

<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Settings</h1>
        <p class="text-gray-600 mt-1">Manage your account settings</p>
    </div>
    
    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500'; ?>">
            <p class="<?php echo $messageType === 'success' ? 'text-green-700' : 'text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        </div>
    <?php endif; ?>
    
    <div class="space-y-6">
        <!-- Theme Settings -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Appearance</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Theme Color</label>
                    <div class="flex items-center space-x-4">
                        <input type="color" 
                               id="themeColorSetting" 
                               value="<?php echo $currentUser['theme_color_preference']; ?>"
                               onchange="changeThemeColor(this.value)"
                               class="w-20 h-20 border border-gray-300 rounded-lg cursor-pointer">
                        <div>
                            <p class="text-sm text-gray-600">Choose your preferred theme color</p>
                            <p class="text-xs text-gray-500 mt-1">Current: <?php echo $currentUser['theme_color_preference']; ?></p>
                        </div>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Language</label>
                    <select id="languageSetting" 
                            onchange="changeLanguage(this.value)"
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="en" <?php echo $currentUser['language_preference'] === 'en' ? 'selected' : ''; ?>>English</option>
                        <option value="th" <?php echo $currentUser['language_preference'] === 'th' ? 'selected' : ''; ?>>ไทย (Thai)</option>
                        <option value="my" <?php echo $currentUser['language_preference'] === 'my' ? 'selected' : ''; ?>>မြန်မာ (Burmese)</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Password Change -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Change Password</h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="change_password">
                
                <div>
                    <label for="old_password" class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                    <input type="password" id="old_password" name="old_password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                    <input type="password" id="new_password" name="new_password" required minlength="6" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                    Change Password
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>