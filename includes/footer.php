    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:py-16 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center">
                        <i class="fas fa-hospital text-2xl text-blue-400 mr-3"></i>
                        <span class="text-xl font-bold"><?= htmlspecialchars($siteName) ?></span>
                    </div>
                    <p class="mt-2 text-gray-300">
                        <?= htmlspecialchars($siteDescription) ?>
                    </p>
                    <div class="mt-4">
                        <p class="text-sm text-gray-400">
                            เวอร์ชัน <?= SITE_VERSION ?> | 
                            พัฒนาโดยระบบ ITA โรงพยาบาล
                        </p>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">ลิงก์ด่วน</h3>
                    <ul class="mt-4 space-y-4">
                        <?php if (isLoggedIn()): ?>
                        <li>
                            <a href="<?= getRoleRedirectUrl(getCurrentUserRole()) ?>" class="text-base text-gray-300 hover:text-white">
                                <i class="fas fa-tachometer-alt mr-2"></i>แดชบอร์ด
                            </a>
                        </li>
                        <?php else: ?>
                        <li>
                            <a href="<?= BASE_URL ?>/public/" class="text-base text-gray-300 hover:text-white">
                                <i class="fas fa-home mr-2"></i>หน้าหลัก
                            </a>
                        </li>
                        <li>
                            <a href="<?= BASE_URL ?>/public/documents/" class="text-base text-gray-300 hover:text-white">
                                <i class="fas fa-file-alt mr-2"></i>เอกสาร
                            </a>
                        </li>
                        <li>
                            <a href="<?= BASE_URL ?>/login.php" class="text-base text-gray-300 hover:text-white">
                                <i class="fas fa-sign-in-alt mr-2"></i>เข้าสู่ระบบ
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">ติดต่อเรา</h3>
                    <ul class="mt-4 space-y-4">
                        <li class="text-base text-gray-300">
                            <i class="fas fa-envelope mr-2"></i>
                            admin@hospital.com
                        </li>
                        <li class="text-base text-gray-300">
                            <i class="fas fa-phone mr-2"></i>
                            02-XXX-XXXX
                        </li>
                        <li class="text-base text-gray-300">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            โรงพยาบาล XXX
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="mt-8 border-t border-gray-700 pt-8">
                <p class="text-base text-gray-400 text-center">
                    &copy; <?= date('Y') ?> <?= htmlspecialchars($siteName) ?>. สงวนลิขสิทธิ์.
                </p>
            </div>
        </div>
    </footer>

    <!-- Notification modal -->
    <div id="notificationModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">การแจ้งเตือน</h3>
                    <button id="closeNotificationModal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="notificationContent" class="max-h-96 overflow-y-auto">
                    <!-- Notifications will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="<?= ASSETS_URL ?>js/app.js"></script>
    
    <script>
        // Global configuration
        window.APP_CONFIG = {
            BASE_URL: '<?= BASE_URL ?>',
            CSRF_TOKEN: '<?= generateCSRFToken() ?>',
            IS_LOGGED_IN: <?= isLoggedIn() ? 'true' : 'false' ?>,
            USER_ROLE: <?= getCurrentUserRole() ? getCurrentUserRole() : 'null' ?>
        };

        // Initialize application
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize dropdowns
            initializeDropdowns();
            
            // Initialize notifications
            if (window.APP_CONFIG.IS_LOGGED_IN) {
                loadNotifications();
                setInterval(loadNotifications, 60000); // Check every minute
            }
            
            // Initialize DataTables with Thai language
            if (typeof $.fn.DataTable !== 'undefined') {
                $.extend(true, $.fn.dataTable.defaults, {
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/th.json'
                    },
                    responsive: true,
                    pageLength: <?= getSetting('items_per_page', ITEMS_PER_PAGE) ?>,
                    dom: '<"flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4"<"flex items-center space-x-2"l><"flex items-center space-x-2"f>>rtip',
                    lengthMenu: [[10, 20, 50, 100, -1], [10, 20, 50, 100, "ทั้งหมด"]]
                });
            }
        });

        // Dropdown functionality
        function initializeDropdowns() {
            // User menu dropdown
            const userMenuBtn = document.getElementById('userMenuBtn');
            const userMenu = document.getElementById('userMenu');
            
            if (userMenuBtn && userMenu) {
                userMenuBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userMenu.classList.toggle('hidden');
                });
                
                document.addEventListener('click', function() {
                    userMenu.classList.add('hidden');
                });
            }
            
            // Mobile menu toggle
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const mobileMenu = document.getElementById('mobileMenu');
            
            if (mobileMenuBtn && mobileMenu) {
                mobileMenuBtn.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }
        }

        // Notification functionality
        function loadNotifications() {
            if (!window.APP_CONFIG.IS_LOGGED_IN) return;
            
            fetch(window.APP_CONFIG.BASE_URL + '/api/notifications.php?action=unread_count')
                .then(response => response.json())
                .then(data => {
                    const countBadge = document.getElementById('notificationCount');
                    if (data.count > 0) {
                        countBadge.textContent = data.count;
                        countBadge.classList.remove('hidden');
                    } else {
                        countBadge.classList.add('hidden');
                    }
                })
                .catch(console.error);
        }

        // Notification modal
        const notificationBtn = document.getElementById('notificationBtn');
        const notificationModal = document.getElementById('notificationModal');
        const closeNotificationModal = document.getElementById('closeNotificationModal');

        if (notificationBtn) {
            notificationBtn.addEventListener('click', function() {
                loadNotificationList();
                notificationModal.classList.remove('hidden');
            });
        }

        if (closeNotificationModal) {
            closeNotificationModal.addEventListener('click', function() {
                notificationModal.classList.add('hidden');
            });
        }

        function loadNotificationList() {
            const content = document.getElementById('notificationContent');
            content.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> กำลังโหลด...</div>';
            
            fetch(window.APP_CONFIG.BASE_URL + '/api/notifications.php?action=list')
                .then(response => response.json())
                .then(data => {
                    if (data.notifications && data.notifications.length > 0) {
                        content.innerHTML = data.notifications.map(notification => `
                            <div class="border-b border-gray-200 py-3 ${notification.is_read ? 'opacity-60' : ''}">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-${getNotificationIcon(notification.type)} text-${getNotificationColor(notification.type)}-500"></i>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <p class="text-sm font-medium text-gray-900">${notification.title}</p>
                                        <p class="text-sm text-gray-500">${notification.message}</p>
                                        <p class="text-xs text-gray-400 mt-1">${formatDate(notification.created_at)}</p>
                                    </div>
                                    ${!notification.is_read ? `
                                        <button onclick="markAsRead(${notification.id})" class="ml-2 text-xs text-blue-600 hover:text-blue-800">
                                            ทำเครื่องหมายว่าอ่านแล้ว
                                        </button>
                                    ` : ''}
                                </div>
                            </div>
                        `).join('');
                    } else {
                        content.innerHTML = '<div class="text-center py-4 text-gray-500">ไม่มีการแจ้งเตือน</div>';
                    }
                })
                .catch(console.error);
        }

        function markAsRead(notificationId) {
            fetch(window.APP_CONFIG.BASE_URL + '/api/notifications.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'mark_read',
                    notification_id: notificationId,
                    csrf_token: window.APP_CONFIG.CSRF_TOKEN
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadNotificationList();
                    loadNotifications();
                }
            })
            .catch(console.error);
        }

        function getNotificationIcon(type) {
            const icons = {
                'info': 'info-circle',
                'success': 'check-circle',
                'warning': 'exclamation-triangle',
                'error': 'times-circle'
            };
            return icons[type] || 'bell';
        }

        function getNotificationColor(type) {
            const colors = {
                'info': 'blue',
                'success': 'green',
                'warning': 'yellow',
                'error': 'red'
            };
            return colors[type] || 'gray';
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('th-TH', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Loading overlay functions
        function showLoading() {
            document.getElementById('loading').style.display = 'block';
        }

        function hideLoading() {
            document.getElementById('loading').style.display = 'none';
        }

        // Utility functions
        function showSuccess(message) {
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ!',
                text: message,
                confirmButtonColor: '#3b82f6'
            });
        }

        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด!',
                text: message,
                confirmButtonColor: '#3b82f6'
            });
        }

        function showConfirm(message, callback) {
            Swal.fire({
                title: 'ยืนยันการดำเนินการ?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed && callback) {
                    callback();
                }
            });
        }
    </script>
    
    <?php if (isset($additionalScripts)): ?>
        <?= $additionalScripts ?>
    <?php endif; ?>
</body>
</html>