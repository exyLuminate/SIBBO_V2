        </main>
        
        <footer>
            Sistem Informasi Belanja Berbasis Online (SIBBO) &copy; 2024
        </footer>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            if (sidebarToggle && sidebar && sidebarOverlay) {
                // Toggle sidebar visibility
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                    sidebarOverlay.classList.toggle('active');
                });

                // Close sidebar when clicking overlay
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                });
            }

            // Add dynamic ripple click effect to all interactive buttons
            const clickables = document.querySelectorAll('.btn, .btn-qty, .btn-qty-danger, .login-container button');
            clickables.forEach(btn => {
                btn.addEventListener('mousedown', function(e) {
                    const rect = this.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    
                    const ripple = document.createElement('span');
                    ripple.className = 'btn-ripple';
                    ripple.style.left = `${x}px`;
                    ripple.style.top = `${y}px`;
                    
                    // Cleanup old ripples just in case
                    const oldRipple = this.querySelector('.btn-ripple');
                    if (oldRipple) oldRipple.remove();
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
        });
    </script>
</body>
</html>