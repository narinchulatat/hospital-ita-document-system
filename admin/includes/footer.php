        </div>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- DataTables JS for TailwindCSS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    
    <!-- Admin JS -->
    <script src="<?= BASE_URL ?>/admin/assets/js/admin.js"></script>
    
    <script>
        // Sidebar toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            
            if (sidebar.classList.contains('sidebar-collapsed')) {
                sidebar.classList.remove('sidebar-collapsed');
                mainContent.classList.remove('main-content-expanded');
                sidebar.classList.remove('w-18');
                sidebar.classList.add('w-64');
            } else {
                sidebar.classList.add('sidebar-collapsed');
                mainContent.classList.add('main-content-expanded');
                sidebar.classList.remove('w-64');
                sidebar.classList.add('w-18');
            }
            
            // Hide/show text elements
            const textElements = sidebar.querySelectorAll('.nav-text, .brand-text');
            textElements.forEach(el => {
                if (sidebar.classList.contains('sidebar-collapsed')) {
                    el.classList.add('hidden');
                } else {
                    el.classList.remove('hidden');
                }
            });
        });
        
        // Mobile sidebar toggle
        if (window.innerWidth <= 768) {
            document.getElementById('sidebarToggle').addEventListener('click', function() {
                const sidebar = document.getElementById('sidebar');
                sidebar.classList.toggle('show');
                
                // Create/remove overlay
                let overlay = document.querySelector('.sidebar-overlay');
                if (sidebar.classList.contains('show') && !overlay) {
                    overlay = document.createElement('div');
                    overlay.className = 'sidebar-overlay fixed inset-0 bg-black bg-opacity-50 z-40';
                    overlay.addEventListener('click', function() {
                        sidebar.classList.remove('show');
                        this.remove();
                    });
                    document.body.appendChild(overlay);
                } else if (overlay) {
                    overlay.remove();
                }
            });
        }
        
        // CSRF token for AJAX requests
        if (typeof $ !== 'undefined') {
            $.ajaxSetup({
                beforeSend: function(xhr, settings) {
                    if (!/^(GET|HEAD|OPTIONS|TRACE)$/i.test(settings.type) && !this.crossDomain) {
                        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                    }
                }
            });
        }
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-auto-dismiss');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
        
        // Confirm delete actions
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-delete') || e.target.closest('.btn-delete')) {
                e.preventDefault();
                const btn = e.target.closest('.btn-delete') || e.target;
                const url = btn.getAttribute('href') || btn.dataset.url;
                const title = btn.dataset.title || 'ยืนยันการลบ';
                const text = btn.dataset.text || 'คุณแน่ใจหรือไม่ที่จะลบรายการนี้?';
                
                Swal.fire({
                    title: title,
                    text: text,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'ใช่, ลบ!',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            }
        });
        
        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('.needs-validation');
            
            forms.forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                        
                        // Focus first invalid field
                        const firstInvalidField = form.querySelector(':invalid');
                        if (firstInvalidField) {
                            firstInvalidField.focus();
                        }
                    }
                    
                    form.classList.add('was-validated');
                }, false);
            });
        });
    </script>
    </script>
    
    <?php if (isset($customJS)): ?>
        <?= $customJS ?>
    <?php endif; ?>
</body>
</html>