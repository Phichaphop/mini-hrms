<?php
// Debug Session
if (isset($_GET['debug_session'])) {
    echo "<pre>";
    echo "Session ID: " . session_id() . "\n";
    echo "Session Name: " . session_name() . "\n";
    echo "Session Data:\n";
    print_r($_SESSION);
    echo "</pre>";
    exit;
}

// /views/layout/header.php
// Main Layout Header with Navigation - UPDATED with Dark/Light Mode + Flag Icons

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../db/Database.php';
require_once __DIR__ . '/../../db/Localization.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

$auth = new AuthController();
$auth->requireLogin();

// Get current user info
$currentUser = $auth->getCurrentUser();
$themeMode = $_SESSION['theme_mode'] ?? 'light'; // light or dark
$userLang = $_SESSION['user_language'] ?? DEFAULT_LANGUAGE;

// Language flags mapping
$langFlags = [
    'th' => '🇹🇭',
    'en' => '🇬🇧',
    'my' => '🇲🇲'
];

$langNames = [
    'th' => 'ไทย',
    'en' => 'English',
    'my' => 'မြန်မာ'
];
?>
<!DOCTYPE html>
<html lang="<?php echo $userLang; ?>" class="<?php echo $themeMode === 'dark' ? 'dark' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Mini HRMS'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Tailwind dark mode configuration
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .sidebar-active { 
            background-color: rgb(59, 130, 246);
            color: white; 
        }
        
        .dark .sidebar-active {
            background-color: rgb(29, 78, 216);
        }
        
        .mobile-menu {
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
        }
        .mobile-menu.active {
            transform: translateX(0);
        }
        
        * {
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        /* Dark mode specific styles */
        .dark {
            color-scheme: dark;
        }
        
        .dark body {
            background-color: #111827;
            color: #f3f4f6;
        }
        
        .dark .bg-white {
            background-color: #1f2937;
        }
        
        .dark .text-gray-800 {
            color: #f3f4f6;
        }
        
        .dark .text-gray-600 {
            color: #d1d5db;
        }
        
        .dark .text-gray-500 {
            color: #9ca3af;
        }
        
        .dark .border-gray-200 {
            border-color: #374151;
        }
        
        .dark .border-gray-300 {
            border-color: #4b5563;
        }
        
        .dark .hover\:bg-gray-100:hover {
            background-color: #374151;
        }
        
        .dark .bg-gray-50 {
            background-color: #1f2937;
        }
        
        .dark .bg-gray-100 {
            background-color: #374151;
        }

        /* Theme toggle button styles */
        .theme-toggle {
            position: relative;
            width: 60px;
            height: 30px;
            background: #d1d5db;
            border-radius: 15px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .dark .theme-toggle {
            background: #4b5563;
        }
        
        .theme-toggle-slider {
            position: absolute;
            top: 3px;
            left: 3px;
            width: 24px;
            height: 24px;
            background: white;
            border-radius: 50%;
            transition: transform 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }
        
        .dark .theme-toggle-slider {
            transform: translateX(30px);
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <div id="mobileMenuOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden" onclick="toggleMobileMenu()"></div>
    
    <aside id="sidebar" class="mobile-menu fixed top-0 left-0 h-full w-64 bg-white dark:bg-gray-800 shadow-lg z-50 lg:translate-x-0">
        <div class="h-full flex flex-col">
            <div class="bg-primary-600 dark:bg-primary-800 p-4 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-xl font-bold">Mini HRMS</h1>
                        <p class="text-xs opacity-90"><?php echo get_text('welcome'); ?></p>
                    </div>
                    <button onclick="toggleMobileMenu()" class="lg:hidden text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 rounded-full bg-primary-600 dark:bg-primary-700 flex items-center justify-center text-white font-bold text-lg">
                        <?php echo strtoupper(substr($currentUser['full_name_en'], 0, 1)); ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-800 dark:text-gray-100 truncate"><?php echo htmlspecialchars($currentUser['full_name_en']); ?></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($currentUser['role_name']); ?></p>
                    </div>
                </div>
            </div>
            
            <nav class="flex-1 overflow-y-auto p-4">
                <ul class="space-y-1">
                    <li>
                        <a href="<?php echo BASE_URL; ?>/views/dashboard.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'sidebar-active' : 'text-gray-700 dark:text-gray-300'; ?>">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            <span><?php echo get_text('dashboard'); ?></span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="<?php echo BASE_URL; ?>/views/employee/profile.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span><?php echo get_text('profile'); ?></span>
                        </a>
                    </li>
                    
                    <?php if ($auth->hasRole(['Admin', 'Officer'])): ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>/views/admin/employees.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <span><?php echo get_text('employees'); ?></span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <li class="pt-4">
                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase mb-2"><?php echo get_text('requests'); ?></p>
                    </li>
                    
                    <li>
                        <a href="<?php echo BASE_URL; ?>/views/employee/my_requests.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span><?php echo get_text('my_requests'); ?></span>
                        </a>
                    </li>
                    
                    <?php if ($auth->hasRole(['Admin', 'Officer'])): ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>/views/admin/all_requests.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <span><?php echo get_text('all_requests'); ?></span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <li class="pt-4">
                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase mb-2"><?php echo get_text('documents'); ?></p>
                    </li>
                    
                    <li>
                        <a href="<?php echo BASE_URL; ?>/views/qr_form/document_submit.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span><?php echo get_text('document_submit'); ?></span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="<?php echo BASE_URL; ?>/views/admin/document_management.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <span><?php echo get_text('document_management'); ?></span>
                        </a>
                    </li>
                    
                    <?php if ($auth->hasRole('Admin')): ?>
                    <li class="pt-4">
                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase mb-2"><?php echo get_text('master_data'); ?></p>
                    </li>
                    
                    <li>
                        <a href="<?php echo BASE_URL; ?>/views/admin/master_data.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                            </svg>
                            <span><?php echo get_text('manage_master_data'); ?></span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="<?php echo BASE_URL; ?>/views/admin/localization.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10"/>
                            </svg>
                            <span><?php echo get_text('localization'); ?></span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <div class="p-4 border-t border-gray-200 dark:border-gray-700 space-y-2">
                <a href="<?php echo BASE_URL; ?>/views/employee/settings.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span><?php echo get_text('settings'); ?></span>
                </a>
                
                <a href="<?php echo BASE_URL; ?>/index.php?action=logout" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600 dark:text-red-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span><?php echo get_text('logout'); ?></span>
                </a>
            </div>
        </div>
    </aside>
    
    <div class="lg:ml-64 min-h-screen">
        <header class="bg-white dark:bg-gray-800 shadow-sm sticky top-0 z-30">
            <div class="flex items-center justify-between p-4">
                <button onclick="toggleMobileMenu()" class="lg:hidden text-primary-600 dark:text-primary-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100 hidden lg:block">
                    <?php echo $pageTitle ?? get_text('dashboard'); ?>
                </h2>
                
                <div class="flex items-center space-x-4">
                    <!-- Dark/Light Mode Toggle -->
                    <div class="theme-toggle" onclick="toggleThemeMode()" title="Toggle Dark/Light Mode">
                        <div class="theme-toggle-slider">
                            <?php echo $themeMode === 'dark' ? '🌙' : '☀️'; ?>
                        </div>
                    </div>
                    
                    <!-- Language Selector with Flags -->
                    <div class="flex items-center space-x-2 bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                        <?php foreach ($langFlags as $code => $flag): ?>
                            <button onclick="changeLanguage('<?php echo $code; ?>')"
                                    class="px-3 py-1.5 rounded-md transition-colors <?php echo $userLang === $code ? 'bg-white dark:bg-gray-600 shadow' : 'hover:bg-gray-200 dark:hover:bg-gray-600'; ?>"
                                    title="<?php echo $langNames[$code]; ?>">
                                <span class="text-xl"><?php echo $flag; ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </header>
        
        <main class="p-4 md:p-6 lg:p-8">

<script>
    // Global CSRF Token
    const CSRF_TOKEN = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
    
    function toggleThemeMode() {
        const html = document.documentElement;
        const isDark = html.classList.contains('dark');
        const newMode = isDark ? 'light' : 'dark';
        
        fetch('<?php echo BASE_URL; ?>/controllers/PreferencesHandler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            credentials: 'same-origin', // สำคัญ! ส่ง cookies
            body: 'action=update_theme&mode=' + encodeURIComponent(newMode) + '&csrf_token=' + CSRF_TOKEN
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                html.classList.toggle('dark');
                const slider = document.querySelector('.theme-toggle-slider');
                if (slider) {
                    slider.textContent = newMode === 'dark' ? '🌙' : '☀️';
                }
            } else {
                console.error('Failed:', data.message);
                if (data.message.includes('authenticated')) {
                    alert('Session expired. Please login again.');
                    window.location.href = '<?php echo BASE_URL; ?>/index.php';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
    
    function changeLanguage(language) {
        fetch('<?php echo BASE_URL; ?>/controllers/PreferencesHandler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            credentials: 'same-origin', // สำคัญ! ส่ง cookies
            body: 'action=update_language&language=' + encodeURIComponent(language) + '&csrf_token=' + CSRF_TOKEN
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                console.error('Failed:', data.message);
                if (data.message.includes('authenticated')) {
                    alert('Session expired. Please login again.');
                    window.location.href = '<?php echo BASE_URL; ?>/index.php';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
</script>