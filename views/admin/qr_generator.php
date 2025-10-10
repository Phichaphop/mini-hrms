<?php
// /views/admin/qr_generator.php
// Generate QR Code for Document Submission Form

require_once __DIR__ . '/../../config/db_config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

$auth = new AuthController();
$auth->requireRole(['Admin', 'Officer']);

$pageTitle = 'QR Code Generator';
require_once __DIR__ . '/../layout/header.php';

// Form URL
$formUrl = 'http://' . $_SERVER['HTTP_HOST'] . BASE_URL . '/views/qr_form/document_submit.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">QR Code Generator</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Generate QR codes for HR Service Form</p>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- QR Code Display -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">Default Form (Thai)</h2>
            <div class="bg-gray-50 dark:bg-gray-700 p-8 rounded-lg text-center">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=<?php echo urlencode($formUrl . '?lang=th'); ?>" 
                     alt="QR Code Thai" 
                     class="mx-auto mb-4 border-4 border-white dark:border-gray-600 shadow-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Thai Language Form</p>
                <a href="<?php echo $formUrl; ?>?lang=th" 
                   target="_blank" 
                   class="text-blue-600 dark:text-blue-400 hover:underline text-sm">
                    <?php echo $formUrl; ?>?lang=th
                </a>
            </div>
            <div class="mt-4 flex gap-2">
                <a href="https://api.qrserver.com/v1/create-qr-code/?size=1000x1000&data=<?php echo urlencode($formUrl . '?lang=th'); ?>" 
                   download="qr-code-thai.png"
                   class="flex-1 bg-blue-600 text-white text-center py-2 rounded-lg hover:bg-blue-700 transition">
                    📥 Download QR (Thai)
                </a>
                <button onclick="printQR('th')" 
                        class="flex-1 bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition">
                    🖨️ Print
                </button>
            </div>
        </div>
        
        <!-- English Version -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">English Form</h2>
            <div class="bg-gray-50 dark:bg-gray-700 p-8 rounded-lg text-center">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=<?php echo urlencode($formUrl . '?lang=en'); ?>" 
                     alt="QR Code English" 
                     class="mx-auto mb-4 border-4 border-white dark:border-gray-600 shadow-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">English Language Form</p>
                <a href="<?php echo $formUrl; ?>?lang=en" 
                   target="_blank" 
                   class="text-blue-600 dark:text-blue-400 hover:underline text-sm">
                    <?php echo $formUrl; ?>?lang=en
                </a>
            </div>
            <div class="mt-4 flex gap-2">
                <a href="https://api.qrserver.com/v1/create-qr-code/?size=1000x1000&data=<?php echo urlencode($formUrl . '?lang=en'); ?>" 
                   download="qr-code-english.png"
                   class="flex-1 bg-blue-600 text-white text-center py-2 rounded-lg hover:bg-blue-700 transition">
                    📥 Download QR (EN)
                </a>
                <button onclick="printQR('en')" 
                        class="flex-1 bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition">
                    🖨️ Print
                </button>
            </div>
        </div>
        
        <!-- Myanmar Version -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">Myanmar Form</h2>
            <div class="bg-gray-50 dark:bg-gray-700 p-8 rounded-lg text-center">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=<?php echo urlencode($formUrl . '?lang=my'); ?>" 
                     alt="QR Code Myanmar" 
                     class="mx-auto mb-4 border-4 border-white dark:border-gray-600 shadow-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Myanmar Language Form</p>
                <a href="<?php echo $formUrl; ?>?lang=my" 
                   target="_blank" 
                   class="text-blue-600 dark:text-blue-400 hover:underline text-sm">
                    <?php echo $formUrl; ?>?lang=my
                </a>
            </div>
            <div class="mt-4 flex gap-2">
                <a href="https://api.qrserver.com/v1/create-qr-code/?size=1000x1000&data=<?php echo urlencode($formUrl . '?lang=my'); ?>" 
                   download="qr-code-myanmar.png"
                   class="flex-1 bg-blue-600 text-white text-center py-2 rounded-lg hover:bg-blue-700 transition">
                    📥 Download QR (MY)
                </a>
                <button onclick="printQR('my')" 
                        class="flex-1 bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition">
                    🖨️ Print
                </button>
            </div>
        </div>
        
        <!-- All Languages -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">Multi-Language Poster</h2>
            <div class="bg-gradient-to-br from-blue-50 to-white dark:from-gray-700 dark:to-gray-600 p-8 rounded-lg">
                <div class="text-center mb-4">
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-100">🏢 HR Service</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">Scan QR Code to Submit Documents</p>
                </div>
                
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode($formUrl . '?lang=th'); ?>" 
                             alt="Thai" 
                             class="mx-auto mb-2 border-2 border-blue-200 dark:border-gray-500 rounded">
                        <p class="text-xs font-semibold dark:text-gray-100">🇹🇭 ไทย</p>
                    </div>
                    <div class="text-center">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode($formUrl . '?lang=en'); ?>" 
                             alt="English" 
                             class="mx-auto mb-2 border-2 border-blue-200 dark:border-gray-500 rounded">
                        <p class="text-xs font-semibold dark:text-gray-100">🇬🇧 English</p>
                    </div>
                    <div class="text-center">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode($formUrl . '?lang=my'); ?>" 
                             alt="Myanmar" 
                             class="mx-auto mb-2 border-2 border-blue-200 dark:border-gray-500 rounded">
                        <p class="text-xs font-semibold dark:text-gray-100">🇲🇲 မြန်မာ</p>
                    </div>
                </div>
            </div>
            <button onclick="printPoster()" 
                    class="w-full mt-4 bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700 transition">
                🖨️ Print Poster (All Languages)
            </button>
        </div>
    </div>
    
    <!-- Instructions -->
    <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-6 rounded-lg">
        <h3 class="font-bold text-blue-800 dark:text-blue-300 mb-2">📋 Instructions</h3>
        <ul class="list-disc list-inside text-sm text-blue-700 dark:text-blue-400 space-y-1">
            <li>Download QR codes as PNG images (1000x1000px)</li>
            <li>Print and display in common areas (break room, entrance, notice board)</li>
            <li>Employees can scan with their phones to access the form</li>
            <li>Forms support Thai, English, and Myanmar languages</li>
            <li>All submissions will appear in Document Submissions page</li>
        </ul>
    </div>
</div>

<script>
function printQR(lang) {
    const url = '<?php echo $formUrl; ?>?lang=' + lang;
    const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=1000x1000&data=' + encodeURIComponent(url);
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>QR Code - ${lang.toUpperCase()}</title>
            <style>
                body {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    min-height: 100vh;
                    margin: 0;
                    font-family: Arial, sans-serif;
                }
                .container {
                    text-align: center;
                    padding: 40px;
                }
                h1 {
                    font-size: 48px;
                    margin-bottom: 20px;
                    color: #1e40af;
                }
                img {
                    max-width: 500px;
                    border: 10px solid #3b82f6;
                    border-radius: 20px;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                }
                p {
                    font-size: 24px;
                    margin-top: 20px;
                    color: #374151;
                }
                @media print {
                    body {
                        padding: 0;
                    }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>🏢 HR Service Form</h1>
                <img src="${qrUrl}" alt="QR Code">
                <p>Scan to submit documents</p>
                <p style="font-size: 18px; color: #6b7280;">${url}</p>
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();
    setTimeout(() => {
        printWindow.print();
    }, 500);
}

function printPoster() {
    const baseUrl = '<?php echo $formUrl; ?>';
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>HR Service - Multi-Language Poster</title>
            <style>
                body {
                    margin: 0;
                    padding: 40px;
                    font-family: Arial, sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                }
                .poster {
                    background: white;
                    max-width: 800px;
                    margin: 0 auto;
                    padding: 60px;
                    border-radius: 20px;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                }
                h1 {
                    text-align: center;
                    font-size: 64px;
                    color: #1e40af;
                    margin-bottom: 20px;
                }
                .subtitle {
                    text-align: center;
                    font-size: 28px;
                    color: #374151;
                    margin-bottom: 40px;
                }
                .qr-grid {
                    display: grid;
                    grid-template-columns: repeat(3, 1fr);
                    gap: 40px;
                    margin: 40px 0;
                }
                .qr-item {
                    text-align: center;
                }
                .qr-item img {
                    width: 200px;
                    height: 200px;
                    border: 5px solid #3b82f6;
                    border-radius: 15px;
                    margin-bottom: 15px;
                }
                .qr-item p {
                    font-size: 24px;
                    font-weight: bold;
                    color: #1f2937;
                }
                .footer {
                    text-align: center;
                    margin-top: 40px;
                    padding-top: 30px;
                    border-top: 3px solid #e5e7eb;
                    color: #6b7280;
                    font-size: 18px;
                }
                @media print {
                    body {
                        background: white;
                        padding: 0;
                    }
                    .poster {
                        box-shadow: none;
                    }
                }
            </style>
        </head>
        <body>
            <div class="poster">
                <h1>🏢 HR Service</h1>
                <p class="subtitle">Scan QR Code to Submit Documents<br>สแกน QR เพื่อส่งเอกสาร</p>
                
                <div class="qr-grid">
                    <div class="qr-item">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(baseUrl + '?lang=th')}" alt="Thai">
                        <p>🇹🇭 ไทย</p>
                    </div>
                    <div class="qr-item">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(baseUrl + '?lang=en')}" alt="English">
                        <p>🇬🇧 English</p>
                    </div>
                    <div class="qr-item">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(baseUrl + '?lang=my')}" alt="Myanmar">
                        <p>🇲🇲 မြန်မာ</p>
                    </div>
                </div>
                
                <div class="footer">
                    <p><strong>Trax Inter Trade Co., Ltd.</strong></p>
                    <p>Human Resource Management System</p>
                </div>
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();
    setTimeout(() => {
        printWindow.print();
    }, 500);
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>