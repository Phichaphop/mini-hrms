<?php
// /views/layout/footer.php
// Footer with JavaScript functions for theme and language switching
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

        // Change language
        function changeLanguage(language) {
            fetch('<?php echo BASE_URL; ?>/controllers/PreferencesHandler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=update_language&language=' + language
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload page to apply new language
                    location.reload();
                } else {
                    alert('Failed to update language: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating language');
            });
        }

        // Change theme color
        function changeThemeColor(color) {
            fetch('<?php echo BASE_URL; ?>/controllers/PreferencesHandler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=update_theme&color=' + encodeURIComponent(color)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update CSS variable immediately
                    document.documentElement.style.setProperty('--theme-color', color);
                    
                    // Update all theme-related elements
                    const themeElements = document.querySelectorAll('.theme-bg');
                    themeElements.forEach(el => {
                        el.style.backgroundColor = color;
                    });
                    
                    // Show success message (optional)
                    console.log('Theme color updated successfully');
                } else {
                    alert('Failed to update theme color: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating theme color');
            });
        }

        // Close mobile menu when clicking outside
        document.addEventListener('DOMContentLoaded', function() {
            const overlay = document.getElementById('mobileMenuOverlay');
            if (overlay) {
                overlay.addEventListener('click', toggleMobileMenu);
            }
        });
    </script>
</body>
</html>