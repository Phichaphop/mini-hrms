<?php
// /views/layout/header.php
// Main Layout Header with Navigation

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../db/Database.php';
require_once __DIR__ . '/../../db/Localization.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

$auth = new AuthController();
$auth->requireLogin();

// Get current user info
$currentUser = $auth->getCurrentUser();
$themeColor = $_SESSION['theme_color'] ?? DEFAULT_THEME_COLOR;
$userLang = $_SESSION['user_language'] ?? DEFAULT_LANGUAGE;
?>
<!DOCTYPE html>
<html lang="<?php echo $userLang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Mini HRMS'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --theme-color: <?php echo $themeColor; ?>;
        }
        .theme-bg { background-color: var(--theme-color); }
        .theme-text { color: var(--theme-color); }
        .theme-border { border-color: var(--theme-color); }
        .sidebar-active { background-color: var(--theme-color); color: white; }
        
        .mobile-menu {
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
        }
        .mobile-menu.active {
            transform: translateX(0);
        }
        
        * {
            transition: background-color 0.2s ease;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div id="mobileMenuOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden" onclick="toggleMobileMenu()"></div>
    
    <aside id="sidebar" class="mobile-menu fixed top-0 left-0 h-full w-64 bg-white shadow-lg z-50 lg:translate-x-0">
        <div class="h-full flex flex-col">
            <div class="theme-bg p-4 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-xl font-bold">Mini HRMS</h1>
                        <p class="text-xs opacity-90">Welcome</p>
                    </div>
                    <button onclick="toggleMobileMenu()" class="lg:hidden text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="p-4 border-b">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 rounded-full theme-bg flex items-center justify-center text-white font-bold text-lg">
                        <?php echo strtoupper(substr($currentUser['full_name_en'], 0, 1)); ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-800 truncate"><?php echo htmlspecialchars($currentUser['full_name_en']); ?></p>
                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($currentUser['role_name']); ?></p>
                    </div>
                </div>
            </div>
            
            <nav class="flex-1 overflow-y-auto p-4">
                <ul class="space-y-1">
                    <li>
                        <a href="<?php echo BASE_URL; ?>/views/dashboard.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'sidebar-active' : ''; ?>">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="<?php echo BASE_URL; ?>/views/employee/profile.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span>Profile</span>
                        </a>
                    </li>
                    
                    <?php if ($auth->hasRole(['Admin', 'Officer'])): ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>/views/admin/employees.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <span>Employees</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <li class="pt-4">
                        <p class="text-xs font-semibold text-gray-400 uppercase mb-2">Requests</p>
                    </li>
                    
                    <li>
                        <a href="<?php echo BASE_URL; ?>/views/employee/my_requests.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span>My Requests</span>
                        </a>
                    </li>
                    
                    <?php if ($auth->hasRole(['Admin', 'Officer'])): ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>/views/admin/all_requests.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <span>All Requests</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <li class="pt-4">
                        <p class="text-xs font-semibold text-gray-400 uppercase mb-2">Documents</p>
                    </li>
                    
                    <li>
                        <a href="<?php echo BASE_URL; ?>/views/employee/documents.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <span>Documents</span>
                        </a>
                    </li>
                    
                    <?php if ($auth->hasRole('Admin')): ?>
                    <li class="pt-4">
                        <p class="text-xs font-semibold text-gray-400 uppercase mb-2">Master Data</p>
                    </li>
                    
                    <li>
                        <a href="<?php echo BASE_URL; ?>/views/admin/master_data.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                            </svg>
                            <span>Manage Master Data</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="<?php echo BASE_URL; ?>/views/admin/localization.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10"/>
                            </svg>
                            <span>Localization</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <div class="p-4 border-t space-y-2">
                <a href="<?php echo BASE_URL; ?>/views/employee/settings.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Settings</span>
                </a>
                
                <a href="<?php echo BASE_URL; ?>/index.php?action=logout" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-red-50 text-red-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </aside>
    
    <div class="lg:ml-64 min-h-screen">
        <header class="bg-white shadow-sm sticky top-0 z-30">
            <div class="flex items-center justify-between p-4">
                <button onclick="toggleMobileMenu()" class="lg:hidden theme-text">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                
                <h2 class="text-xl font-semibold text-gray-800 hidden lg:block">
                    <?php echo $pageTitle ?? 'Dashboard'; ?>
                </h2>
                
                <div class="flex items-center space-x-4">
                    <select id="languageSelector" 
                            onchange="changeLanguage(this.value)"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="en" <?php echo $userLang === 'en' ? 'selected' : ''; ?>>English</option>
                        <option value="th" <?php echo $userLang === 'th' ? 'selected' : ''; ?>>ไทย</option>
                        <option value="my" <?php echo $userLang === 'my' ? 'selected' : ''; ?>>မြန်မာ</option>
                    </select>
                    
                    <input type="color" 
                           id="themeColorPicker" 
                           value="<?php echo $themeColor; ?>"
                           onchange="changeThemeColor(this.value)"
                           class="w-10 h-10 border border-gray-300 rounded-lg cursor-pointer"
                           title="Theme Color">
                </div>
            </div>
        </header>
        
        <main class="p-4 md:p-6 lg:p-8">