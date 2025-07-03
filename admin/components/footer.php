        </main>

        <!-- Footer -->
        <footer class="admin-footer">
            <div class="footer-content">
                <div class="footer-left">
                    <p>&copy; 2025 QueueingPro. All rights reserved.</p>
                </div>
                <div class="footer-right">
                    <span class="version">Version 1.0</span>
                    <a href="../QueDisplay.php" class="footer-link">
                        <i class="fas fa-tv"></i>
                        View Display
                    </a>
                    <a href="../controller/" class="footer-link">
                        <i class="fas fa-headset"></i>
                        Controller
                    </a>
                </div>
            </div>
        </footer>
    </div>

    <script>
        // Update current date and time
        function updateDateTime() {
            const now = new Date();
            const options = {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            };
            document.getElementById('currentDateTime').textContent = now.toLocaleDateString('en-US', options);
        }

        // Update time every second
        setInterval(updateDateTime, 1000);
        updateDateTime(); // Initial call

        // Tab navigation functionality
        document.addEventListener('DOMContentLoaded', function() {
            const navTabs = document.querySelectorAll('.nav-tab');
            
            navTabs.forEach(tab => {
                tab.addEventListener('click', function(e) {
                    // Remove active class from all tabs
                    navTabs.forEach(t => t.classList.remove('active'));
                    // Add active class to clicked tab
                    this.classList.add('active');
                });
            });
        });
    </script>
</body>
</html>
