    </main>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="hidden fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-4">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <div class="text-gray-900">กำลังโหลด...</div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-500">
                        © <?= date('Y') ?> <?= htmlspecialchars($siteName) ?> - ระบบจัดการเอกสารโรงพยาบาล
                    </div>
                </div>
                <div class="flex items-center space-x-4 text-sm text-gray-500">
                    <div>เวอร์ชัน 1.0</div>
                    <div>|</div>
                    <div>ผู้ใช้งาน: <?= htmlspecialchars($currentUser['username']) ?></div>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="<?= BASE_URL ?>/approver/assets/js/approver.js"></script>
    
    <script>
        // Initialize dropdown menus
        $(document).ready(function() {
            // User menu dropdown
            $('#userMenuBtn').click(function(e) {
                e.stopPropagation();
                $('#userDropdown').toggle();
                $('#notificationDropdown').hide();
            });
            
            // Notification dropdown
            $('#notificationBtn').click(function(e) {
                e.stopPropagation();
                $('#notificationDropdown').toggle();
                $('#userDropdown').hide();
                loadNotifications();
            });
            
            // Mobile menu
            $('#mobileMenuBtn').click(function() {
                $('#mobileMenu').toggle();
            });
            
            // Close dropdowns when clicking outside
            $(document).click(function() {
                $('#userDropdown').hide();
                $('#notificationDropdown').hide();
            });
            
            // Load notifications on page load
            loadNotifications();
            
            // Auto-refresh notifications every 30 seconds
            setInterval(loadNotifications, 30000);
        });
        
        // Notification functions
        function loadNotifications() {
            $.get('<?= BASE_URL ?>/approver/api/notifications.php', function(data) {
                if (data.success) {
                    updateNotificationBadge(data.unread_count);
                    updateNotificationList(data.notifications);
                }
            }).catch(function() {
                // Fail silently for notifications
            });
        }
        
        function updateNotificationBadge(count) {
            const badge = $('#notificationBadge');
            if (count > 0) {
                badge.text(count > 99 ? '99+' : count).removeClass('hidden');
            } else {
                badge.addClass('hidden');
            }
        }
        
        function updateNotificationList(notifications) {
            const list = $('#notificationList');
            
            if (notifications.length === 0) {
                list.html('<div class="px-4 py-3 text-sm text-gray-500 text-center">ไม่มีการแจ้งเตือน</div>');
                return;
            }
            
            let html = '';
            notifications.slice(0, 5).forEach(function(notif) {
                const timeAgo = formatTimeAgo(notif.created_at);
                const isUnread = notif.is_read === '0' ? 'bg-blue-50' : '';
                
                html += `
                    <div class="px-4 py-3 hover:bg-gray-50 ${isUnread}" onclick="markAsRead(${notif.id})">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <i class="fas fa-${getNotificationIcon(notif.type)} text-${getNotificationColor(notif.type)}-500"></i>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-gray-900">${notif.title}</p>
                                <p class="text-xs text-gray-500 mt-1">${notif.message}</p>
                                <p class="text-xs text-gray-400 mt-1">${timeAgo}</p>
                            </div>
                            ${notif.is_read === '0' ? '<div class="flex-shrink-0"><div class="w-2 h-2 bg-blue-500 rounded-full"></div></div>' : ''}
                        </div>
                    </div>
                `;
            });
            
            if (notifications.length > 5) {
                html += `
                    <div class="px-4 py-2 bg-gray-50 text-center">
                        <a href="<?= BASE_URL ?>/approver/profile/notifications.php" class="text-sm text-blue-600 hover:text-blue-500">
                            ดูทั้งหมด (${notifications.length})
                        </a>
                    </div>
                `;
            }
            
            list.html(html);
        }
        
        function markAsRead(notificationId) {
            $.post('<?= BASE_URL ?>/approver/api/notifications.php', {
                action: 'mark_read',
                id: notificationId,
                csrf_token: '<?= generateCSRFToken() ?>'
            }, function(data) {
                if (data.success) {
                    loadNotifications();
                }
            });
        }
        
        function getNotificationIcon(type) {
            const icons = {
                'approval_needed': 'hourglass-half',
                'document_approved': 'check-circle',
                'document_rejected': 'times-circle',
                'system': 'cog',
                'info': 'info-circle'
            };
            return icons[type] || 'bell';
        }
        
        function getNotificationColor(type) {
            const colors = {
                'approval_needed': 'yellow',
                'document_approved': 'green',
                'document_rejected': 'red',
                'system': 'blue',
                'info': 'blue'
            };
            return colors[type] || 'gray';
        }
        
        function formatTimeAgo(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = Math.floor((now - date) / 1000);
            
            if (diff < 60) return 'เมื่อสักครู่';
            if (diff < 3600) return Math.floor(diff / 60) + ' นาทีที่แล้ว';
            if (diff < 86400) return Math.floor(diff / 3600) + ' ชั่วโมงที่แล้ว';
            if (diff < 604800) return Math.floor(diff / 86400) + ' วันที่แล้ว';
            return date.toLocaleDateString('th-TH');
        }
        
        // Global utility functions
        function showLoading() {
            $('#loadingOverlay').removeClass('hidden');
        }
        
        function hideLoading() {
            $('#loadingOverlay').addClass('hidden');
        }
        
        function showSuccess(message) {
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ',
                text: message,
                timer: 3000,
                showConfirmButton: false
            });
        }
        
        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: message
            });
        }
        
        function showWarning(message) {
            Swal.fire({
                icon: 'warning',
                title: 'คำเตือน',
                text: message
            });
        }
        
        function showInfo(message) {
            Swal.fire({
                icon: 'info',
                title: 'ข้อมูล',
                text: message
            });
        }
        
        function confirmAction(message, callback) {
            Swal.fire({
                title: 'ยืนยันการดำเนินการ',
                text: message,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
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
</body>
</html>