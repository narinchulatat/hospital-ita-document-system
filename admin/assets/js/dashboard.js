// Dashboard JavaScript - TailwindCSS Compatible

$(document).ready(function() {
    initializeDashboard();
    initializeCharts();
    initializeRealTimeUpdates();
});

/**
 * Initialize dashboard features
 */
function initializeDashboard() {
    // Initialize statistics cards with animations
    animateCounters();
    
    // Initialize quick actions
    initializeQuickActions();
    
    // Initialize recent activities
    initializeRecentActivities();
    
    // Initialize dashboard widgets
    initializeDashboardWidgets();
}

/**
 * Animate counter numbers
 */
function animateCounters() {
    $('.stat-counter').each(function() {
        const $this = $(this);
        const countTo = $this.data('count');
        
        $({ countNum: 0 }).animate({
            countNum: countTo
        }, {
            duration: 2000,
            easing: 'linear',
            step: function() {
                $this.text(Math.floor(this.countNum).toLocaleString());
            },
            complete: function() {
                $this.text(countTo.toLocaleString());
            }
        });
    });
}

/**
 * Initialize quick actions
 */
function initializeQuickActions() {
    // Quick document upload
    $('#quickUploadBtn').on('click', function() {
        $('#quickUploadModal').removeClass('hidden');
    });
    
    // Quick user creation
    $('#quickUserBtn').on('click', function() {
        window.location.href = BASE_URL + '/admin/users/create.php';
    });
    
    // System backup
    $('#backupBtn').on('click', function() {
        Swal.fire({
            title: 'สำรองข้อมูล',
            text: 'คุณต้องการสำรองข้อมูลระบบหรือไม่?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'สำรองข้อมูล',
            cancelButtonText: 'ยกเลิก',
            customClass: {
                popup: 'font-sans'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                performSystemBackup();
            }
        });
    });
}

/**
 * Perform system backup
 */
function performSystemBackup() {
    showLoading('กำลังสำรองข้อมูล...');
    
    $.ajax({
        url: BASE_URL + '/admin/api/backup.php',
        method: 'POST',
        data: {
            action: 'create_backup',
            csrf_token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                Swal.fire({
                    title: 'สำรองข้อมูลสำเร็จ',
                    text: 'ข้อมูลได้รับการสำรองเรียบร้อยแล้ว',
                    icon: 'success',
                    customClass: {
                        popup: 'font-sans'
                    }
                });
                
                // Update backup statistics
                updateBackupStats();
            } else {
                throw new Error(response.message || 'เกิดข้อผิดพลาดในการสำรองข้อมูล');
            }
        },
        error: function(xhr, status, error) {
            hideLoading();
            Swal.fire({
                title: 'เกิดข้อผิดพลาด',
                text: 'ไม่สามารถสำรองข้อมูลได้ กรุณาลองใหม่อีกครั้ง',
                icon: 'error',
                customClass: {
                    popup: 'font-sans'
                }
            });
        }
    });
}

/**
 * Initialize charts
 */
function initializeCharts() {
    // Users by role chart
    initializeUsersByRoleChart();
    
    // Documents by category chart
    initializeDocumentsByCategoryChart();
    
    // Activity timeline chart
    initializeActivityTimelineChart();
    
    // Storage usage chart
    initializeStorageUsageChart();
}

/**
 * Initialize users by role chart
 */
function initializeUsersByRoleChart() {
    const ctx = document.getElementById('usersByRoleChart');
    if (!ctx) return;
    
    const chartData = window.usersByRoleData || [];
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: chartData.map(item => item.role_name),
            datasets: [{
                data: chartData.map(item => item.count),
                backgroundColor: [
                    '#3B82F6', // blue-500
                    '#10B981', // emerald-500
                    '#F59E0B', // amber-500
                    '#8B5CF6', // violet-500
                    '#EF4444', // red-500
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            family: 'Sarabun'
                        }
                    }
                }
            }
        }
    });
}

/**
 * Initialize documents by category chart
 */
function initializeDocumentsByCategoryChart() {
    const ctx = document.getElementById('documentsByCategoryChart');
    if (!ctx) return;
    
    const chartData = window.documentsByCategoryData || [];
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.map(item => item.category_name),
            datasets: [{
                label: 'จำนวนเอกสาร',
                data: chartData.map(item => item.count),
                backgroundColor: '#3B82F6',
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#F3F4F6'
                    },
                    ticks: {
                        font: {
                            family: 'Sarabun'
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            family: 'Sarabun'
                        }
                    }
                }
            }
        }
    });
}

/**
 * Initialize activity timeline chart
 */
function initializeActivityTimelineChart() {
    const ctx = document.getElementById('activityTimelineChart');
    if (!ctx) return;
    
    const chartData = window.activityTimelineData || [];
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(item => item.date),
            datasets: [{
                label: 'กิจกรรม',
                data: chartData.map(item => item.count),
                borderColor: '#10B981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#F3F4F6'
                    },
                    ticks: {
                        font: {
                            family: 'Sarabun'
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            family: 'Sarabun'
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
}

/**
 * Initialize storage usage chart
 */
function initializeStorageUsageChart() {
    const ctx = document.getElementById('storageUsageChart');
    if (!ctx) return;
    
    const usedStorage = window.storageData?.used || 0;
    const totalStorage = window.storageData?.total || 100;
    const freeStorage = totalStorage - usedStorage;
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['ใช้งานแล้ว', 'ว่าง'],
            datasets: [{
                data: [usedStorage, freeStorage],
                backgroundColor: ['#EF4444', '#E5E7EB'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            family: 'Sarabun'
                        }
                    }
                }
            }
        }
    });
}

/**
 * Initialize real-time updates
 */
function initializeRealTimeUpdates() {
    // Update statistics every 30 seconds
    setInterval(updateDashboardStats, 30000);
    
    // Update notifications every 15 seconds
    setInterval(updateNotifications, 15000);
    
    // Update recent activities every 60 seconds
    setInterval(updateRecentActivities, 60000);
}

/**
 * Update dashboard statistics
 */
function updateDashboardStats() {
    $.ajax({
        url: BASE_URL + '/admin/api/dashboard-stats.php',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                updateStatCards(response.data);
            }
        },
        error: function() {
            console.warn('Failed to update dashboard statistics');
        }
    });
}

/**
 * Update stat cards
 */
function updateStatCards(stats) {
    Object.keys(stats).forEach(key => {
        const $card = $(`[data-stat="${key}"]`);
        if ($card.length) {
            const currentValue = parseInt($card.text().replace(/,/g, ''));
            const newValue = stats[key];
            
            if (currentValue !== newValue) {
                // Animate the change
                $({ countNum: currentValue }).animate({
                    countNum: newValue
                }, {
                    duration: 1000,
                    step: function() {
                        $card.text(Math.floor(this.countNum).toLocaleString());
                    },
                    complete: function() {
                        $card.text(newValue.toLocaleString());
                    }
                });
            }
        }
    });
}

/**
 * Update notifications
 */
function updateNotifications() {
    $.ajax({
        url: BASE_URL + '/admin/api/notifications.php',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                updateNotificationBadge(response.data.unread_count);
                updateNotificationDropdown(response.data.notifications);
            }
        },
        error: function() {
            console.warn('Failed to update notifications');
        }
    });
}

/**
 * Update notification badge
 */
function updateNotificationBadge(count) {
    const $badge = $('#notificationBadge');
    if (count > 0) {
        $badge.text(count).removeClass('hidden');
    } else {
        $badge.addClass('hidden');
    }
}

/**
 * Update notification dropdown
 */
function updateNotificationDropdown(notifications) {
    const $dropdown = $('#notificationDropdown');
    const $container = $dropdown.find('.max-h-96');
    
    if (notifications.length === 0) {
        $container.html('<div class="p-4 text-center text-gray-500">ไม่มีการแจ้งเตือนใหม่</div>');
        return;
    }
    
    let html = '';
    notifications.forEach(notification => {
        html += `
            <div class="p-4 border-b border-gray-200 hover:bg-gray-50">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas ${getNotificationIcon(notification.type)} text-blue-500"></i>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-gray-900">${notification.title}</p>
                        <p class="text-sm text-gray-500">${notification.message}</p>
                        <p class="text-xs text-gray-400 mt-1">${formatThaiDate(notification.created_at, true)}</p>
                    </div>
                </div>
            </div>
        `;
    });
    
    $container.html(html);
}

/**
 * Get notification icon
 */
function getNotificationIcon(type) {
    const icons = {
        'info': 'fa-info-circle',
        'warning': 'fa-exclamation-triangle',
        'success': 'fa-check-circle',
        'error': 'fa-times-circle',
        'document': 'fa-file-alt',
        'user': 'fa-user',
        'system': 'fa-cog'
    };
    
    return icons[type] || 'fa-bell';
}

/**
 * Update recent activities
 */
function updateRecentActivities() {
    $.ajax({
        url: BASE_URL + '/admin/api/recent-activities.php',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                updateActivitiesList(response.data);
            }
        },
        error: function() {
            console.warn('Failed to update recent activities');
        }
    });
}

/**
 * Update activities list
 */
function updateActivitiesList(activities) {
    const $container = $('#recentActivitiesList');
    if (!$container.length) return;
    
    if (activities.length === 0) {
        $container.html('<p class="text-gray-500 text-center py-4">ไม่มีกิจกรรม</p>');
        return;
    }
    
    let html = '<ul class="-mb-8">';
    activities.forEach((activity, index) => {
        html += `
            <li>
                <div class="relative pb-8">
                    ${index < activities.length - 1 ? '<span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>' : ''}
                    <div class="relative flex space-x-3">
                        <div>
                            <span class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center ring-8 ring-white">
                                <i class="fas ${getActivityIcon(activity.action)} text-blue-600 text-sm"></i>
                            </span>
                        </div>
                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                            <div>
                                <p class="text-sm text-gray-500">
                                    <span class="font-medium text-gray-900">
                                        ${activity.user_name || 'ระบบ'}
                                    </span>
                                    ${getActivityDescription(activity.action, activity.table_name)}
                                </p>
                            </div>
                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                ${formatThaiDate(activity.created_at, true)}
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        `;
    });
    html += '</ul>';
    
    $container.html(html);
}

/**
 * Get activity icon
 */
function getActivityIcon(action) {
    const icons = {
        'CREATE': 'fa-plus',
        'UPDATE': 'fa-edit',
        'DELETE': 'fa-trash',
        'LOGIN': 'fa-sign-in-alt',
        'LOGOUT': 'fa-sign-out-alt',
        'APPROVE': 'fa-check',
        'REJECT': 'fa-times',
        'DOWNLOAD': 'fa-download'
    };
    
    return icons[action] || 'fa-circle';
}

/**
 * Get activity description
 */
function getActivityDescription(action, table) {
    const descriptions = {
        'CREATE': 'สร้าง' + getTableName(table),
        'UPDATE': 'แก้ไข' + getTableName(table),
        'DELETE': 'ลบ' + getTableName(table),
        'LOGIN': 'เข้าสู่ระบบ',
        'LOGOUT': 'ออกจากระบบ',
        'APPROVE': 'อนุมัติเอกสาร',
        'REJECT': 'ไม่อนุมัติเอกสาร',
        'DOWNLOAD': 'ดาวน์โหลดเอกสาร'
    };
    
    return descriptions[action] || 'ดำเนินการ';
}

/**
 * Get table name in Thai
 */
function getTableName(table) {
    const names = {
        'users': 'ผู้ใช้',
        'documents': 'เอกสาร',
        'categories': 'หมวดหมู่',
        'settings': 'การตั้งค่า'
    };
    
    return names[table] || table;
}

/**
 * Initialize dashboard widgets
 */
function initializeDashboardWidgets() {
    // Collapsible widgets
    $('.widget-toggle').on('click', function() {
        const $widget = $(this).closest('.widget');
        const $content = $widget.find('.widget-content');
        const $icon = $(this).find('i');
        
        $content.slideToggle();
        $icon.toggleClass('fa-chevron-up fa-chevron-down');
        
        // Save widget state
        const widgetId = $widget.data('widget-id');
        const isCollapsed = $content.is(':hidden');
        localStorage.setItem(`widget-${widgetId}-collapsed`, isCollapsed);
    });
    
    // Restore widget states
    $('.widget').each(function() {
        const $widget = $(this);
        const widgetId = $widget.data('widget-id');
        const isCollapsed = localStorage.getItem(`widget-${widgetId}-collapsed`) === 'true';
        
        if (isCollapsed) {
            $widget.find('.widget-content').hide();
            $widget.find('.widget-toggle i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
        }
    });
}

/**
 * Update backup stats
 */
function updateBackupStats() {
    $.ajax({
        url: BASE_URL + '/admin/api/backup-stats.php',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const $lastBackup = $('#lastBackupDate');
                const $backupCount = $('#backupCount');
                
                if ($lastBackup.length && response.data.last_backup) {
                    $lastBackup.text(formatThaiDate(response.data.last_backup, true));
                }
                
                if ($backupCount.length && response.data.total_backups) {
                    $backupCount.text(response.data.total_backups);
                }
            }
        },
        error: function() {
            console.warn('Failed to update backup stats');
        }
    });
}

// Helper functions
function showLoading(message = 'กำลังโหลด...') {
    if (window.AdminJS && window.AdminJS.showLoading) {
        window.AdminJS.showLoading(message);
    }
}

function hideLoading() {
    if (window.AdminJS && window.AdminJS.hideLoading) {
        window.AdminJS.hideLoading();
    }
}

function formatThaiDate(dateString, showTime = false) {
    if (window.AdminJS && window.AdminJS.formatThaiDate) {
        return window.AdminJS.formatThaiDate(dateString, showTime);
    }
    return new Date(dateString).toLocaleDateString('th-TH');
}