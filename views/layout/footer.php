<?php
// /views/layout/footer.php
// Footer with JavaScript functions for Dark/Light theme and language switching - FIXED
?>
        </main>
    </div>

    <script>
        // Mobile menu toggle
        function toggleMobileMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileMenuOverlay');
            
            sidebar.classList.toggle('active');
            overlay.classList.toggle('hidden');
        }

        // Toggle Dark/Light Mode - FIXED VERSION
        function toggleThemeMode() {
            const html = document.documentElement;
            const isDark = html.classList.contains('dark');
            const newMode = isDark ? 'light' : 'dark';
            
            console.log('Toggling theme from', isDark ? 'dark' : 'light', 'to', newMode);
            
            fetch('<?php echo BASE_URL; ?>/controllers/PreferencesHandler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=update_theme&mode=' + encodeURIComponent(newMode)
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                
                if (data.success) {
                    // Toggle dark class
                    html.classList.toggle('dark');
                    
                    // Update toggle button icon
                    const slider = document.querySelector('.theme-toggle-slider');
                    if (slider) {
                        slider.textContent = newMode === 'dark' ? '🌙' : '☀️';
                    }
                    
                    console.log('✅ Theme mode updated successfully to:', newMode);
                    
                    // Optional: Show success message
                    showNotification('Theme updated to ' + newMode + ' mode', 'success');
                } else {
                    console.error('❌ Failed to update theme mode:', data.message);
                    alert('Failed to update theme: ' + data.message);
                }
            })
            .catch(error => {
                console.error('❌ Error:', error);
                alert('Network error occurred. Please check console for details.');
            });
        }

        // Change language - FIXED VERSION
        function changeLanguage(language) {
            console.log('Changing language to:', language);
            
            fetch('<?php echo BASE_URL; ?>/controllers/PreferencesHandler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=update_language&language=' + encodeURIComponent(language)
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                
                if (data.success) {
                    console.log('✅ Language updated successfully to:', language);
                    
                    // Show loading message
                    showNotification('Language changed. Reloading...', 'success');
                    
                    // Reload page after short delay
                    setTimeout(() => {
                        location.reload();
                    }, 500);
                } else {
                    console.error('❌ Failed to update language:', data.message);
                    alert('Failed to update language: ' + data.message);
                }
            })
            .catch(error => {
                console.error('❌ Error:', error);
                alert('Network error occurred. Please check console for details.');
            });
        }

        // Show notification (optional helper)
        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 'bg-blue-500'
            } text-white`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Close mobile menu when clicking outside
        document.addEventListener('DOMContentLoaded', function() {
            const overlay = document.getElementById('mobileMenuOverlay');
            if (overlay) {
                overlay.addEventListener('click', toggleMobileMenu);
            }
            
            console.log('✅ Page loaded. Theme mode:', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
            console.log('✅ Current language:', '<?php echo $_SESSION['user_language'] ?? 'en'; ?>');
        });
    </script>
</body>
</html>