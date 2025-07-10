            </div>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="<?php echo REPORTS_ASSETS_URL; ?>/js/reports.js"></script>
    <script src="<?php echo REPORTS_ASSETS_URL; ?>/js/charts.js"></script>
    
    <script>
        // Toggle user menu
        function toggleUserMenu() {
            const menu = document.getElementById('userMenu');
            menu.classList.toggle('hidden');
        }
        
        // Close user menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('userMenu');
            const button = event.target.closest('button');
            
            if (!button || !button.onclick) {
                menu.classList.add('hidden');
            }
        });
        
        // Initialize DataTables
        $(document).ready(function() {
            $('.data-table').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/th.json"
                },
                "responsive": true,
                "pageLength": 25,
                "order": [[ 0, "desc" ]]
            });
        });
        
        // Export functions
        function exportReport(format) {
            const currentUrl = window.location.href;
            const url = new URL(currentUrl);
            url.searchParams.set('export', format);
            window.location.href = url.toString();
        }
        
        // Show loading
        function showLoading() {
            Swal.fire({
                title: 'กำลังประมวลผล...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
        
        // Hide loading
        function hideLoading() {
            Swal.close();
        }
        
        // Show success message
        function showSuccess(message) {
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ',
                text: message,
                timer: 3000,
                showConfirmButton: false
            });
        }
        
        // Show error message
        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: message
            });
        }
        
        // Format number with commas
        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }
        
        // Format percentage
        function formatPercentage(value, total) {
            if (total === 0) return '0%';
            return ((value / total) * 100).toFixed(1) + '%';
        }
        
        // Format file size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        // Update URL with parameters
        function updateUrlParams(params) {
            const url = new URL(window.location.href);
            Object.keys(params).forEach(key => {
                if (params[key]) {
                    url.searchParams.set(key, params[key]);
                } else {
                    url.searchParams.delete(key);
                }
            });
            window.history.pushState({}, '', url.toString());
        }
        
        // Apply filters
        function applyFilters() {
            const form = document.getElementById('filterForm');
            if (form) {
                const formData = new FormData(form);
                const params = {};
                
                for (let [key, value] of formData.entries()) {
                    params[key] = value;
                }
                
                updateUrlParams(params);
                location.reload();
            }
        }
        
        // Reset filters
        function resetFilters() {
            const form = document.getElementById('filterForm');
            if (form) {
                form.reset();
                updateUrlParams({});
                location.reload();
            }
        }
        
        // Initialize charts
        function initializeCharts() {
            // This will be implemented in charts.js
            if (typeof window.initCharts === 'function') {
                window.initCharts();
            }
        }
        
        // Initialize on page load
        $(document).ready(function() {
            initializeCharts();
            
            // Auto-refresh data every 5 minutes
            setInterval(function() {
                if (typeof window.refreshData === 'function') {
                    window.refreshData();
                }
            }, 300000);
        });
    </script>
    
    <?php if (isset($customJS)): ?>
        <?php echo $customJS; ?>
    <?php endif; ?>
</body>
</html>