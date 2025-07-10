// Dashboard JavaScript functionality

$(document).ready(function() {
    initializeDashboard();
    initializeCharts();
    initializeStats();
    loadRecentActivities();
    initializeNotifications();
});

/**
 * Initialize dashboard components
 */
function initializeDashboard() {
    // Auto-refresh dashboard data every 5 minutes
    setInterval(refreshDashboardData, 300000);
    
    // Initialize dashboard widgets
    initializeWidgets();
    
    // Initialize quick actions
    initializeQuickActions();
    
    // Initialize real-time features
    initializeRealTime();
}

/**
 * Initialize dashboard widgets
 */
function initializeWidgets() {
    // Weather widget
    if ($('#weatherWidget').length) {
        loadWeatherData();
    }
    
    // Clock widget
    if ($('#clockWidget').length) {
        updateClock();
        setInterval(updateClock, 1000);
    }
    
    // System status widget
    if ($('#systemStatusWidget').length) {
        loadSystemStatus();
        setInterval(loadSystemStatus, 60000);
    }
}

/**
 * Initialize charts
 */
function initializeCharts() {
    // Users chart
    if ($('#usersChart').length) {
        createUsersChart();
    }
    
    // Documents chart
    if ($('#documentsChart').length) {
        createDocumentsChart();
    }
    
    // Activities chart
    if ($('#activitiesChart').length) {
        createActivitiesChart();
    }
    
    // Monthly stats chart
    if ($('#monthlyStatsChart').length) {
        createMonthlyStatsChart();
    }
}

/**
 * Create users chart
 */
function createUsersChart() {
    const ctx = document.getElementById('usersChart').getContext('2d');
    
    fetch(`${BASE_URL}/admin/api/dashboard/users-chart.php`)
        .then(response => response.json())
        .then(data => {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.labels,
                    datasets: [{
                        data: data.values,
                        backgroundColor: [
                            '#3B82F6', // blue-500
                            '#10B981', // emerald-500
                            '#F59E0B', // amber-500
                            '#EF4444', // red-500
                            '#8B5CF6'  // violet-500
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                font: {
                                    family: 'Sarabun',
                                    size: 12
                                }
                            }
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error loading users chart:', error);
            $('#usersChart').closest('.chart-container').html('<p class="text-gray-500 text-center">ไม่สามารถโหลดข้อมูลได้</p>');
        });
}

/**
 * Create documents chart
 */
function createDocumentsChart() {
    const ctx = document.getElementById('documentsChart').getContext('2d');
    
    fetch(`${BASE_URL}/admin/api/dashboard/documents-chart.php`)
        .then(response => response.json())
        .then(data => {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'เอกสาร',
                        data: data.values,
                        backgroundColor: '#3B82F6',
                        borderColor: '#2563EB',
                        borderWidth: 1,
                        borderRadius: 4
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
        })
        .catch(error => {
            console.error('Error loading documents chart:', error);
            $('#documentsChart').closest('.chart-container').html('<p class="text-gray-500 text-center">ไม่สามารถโหลดข้อมูลได้</p>');
        });
}

/**
 * Create activities chart
 */
function createActivitiesChart() {
    const ctx = document.getElementById('activitiesChart').getContext('2d');
    
    fetch(`${BASE_URL}/admin/api/dashboard/activities-chart.php`)
        .then(response => response.json())
        .then(data => {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'กิจกรรม',
                        data: data.values,
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 2,
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
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error loading activities chart:', error);
            $('#activitiesChart').closest('.chart-container').html('<p class="text-gray-500 text-center">ไม่สามารถโหลดข้อมูลได้</p>');
        });
}

/**
 * Create monthly stats chart
 */
function createMonthlyStatsChart() {
    const ctx = document.getElementById('monthlyStatsChart').getContext('2d');
    
    fetch(`${BASE_URL}/admin/api/dashboard/monthly-stats.php`)
        .then(response => response.json())
        .then(data => {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: 'ผู้ใช้ใหม่',
                            data: data.users,
                            borderColor: '#3B82F6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 2,
                            fill: false
                        },
                        {
                            label: 'เอกสารใหม่',
                            data: data.documents,
                            borderColor: '#10B981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 2,
                            fill: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: {
                                    family: 'Sarabun'
                                }
                            }
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
        })
        .catch(error => {
            console.error('Error loading monthly stats chart:', error);
            $('#monthlyStatsChart').closest('.chart-container').html('<p class="text-gray-500 text-center">ไม่สามารถโหลดข้อมูลได้</p>');
        });
}

/**
 * Initialize stats counters
 */
function initializeStats() {
    $('.counter').each(function() {
        const $counter = $(this);
        const target = parseInt($counter.data('target'));
        const duration = $counter.data('duration') || 2000;
        
        animateCounter($counter, target, duration);
    });
}

/**
 * Animate counter
 */
function animateCounter($element, target, duration) {
    let current = 0;
    const increment = target / (duration / 16);
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            current = target;
            clearInterval(timer);
        }
        $element.text(Math.floor(current).toLocaleString());
    }, 16);
}

/**
 * Load recent activities
 */
function loadRecentActivities() {
    if ($('#recentActivities').length === 0) return;
    
    $.ajax({
        url: `${BASE_URL}/admin/api/dashboard/recent-activities.php`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                renderRecentActivities(response.data);
            } else {
                $('#recentActivities').html('<p class="text-gray-500 text-center">ไม่สามารถโหลดข้อมูลได้</p>');
            }
        },
        error: function() {
            $('#recentActivities').html('<p class="text-gray-500 text-center">เกิดข้อผิดพลาดในการโหลดข้อมูล</p>');
        }
    });
}

/**
 * Render recent activities
 */
function renderRecentActivities(activities) {
    const container = $('#recentActivities');
    container.empty();
    
    if (activities.length === 0) {
        container.html('<p class="text-gray-500 text-center">ไม่มีกิจกรรมล่าสุด</p>');
        return;
    }
    
    activities.forEach(activity => {
        const activityHtml = `
            <div class="flex items-start space-x-3 p-3 hover:bg-gray-50 rounded-lg transition-colors">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-${getActivityColor(activity.action)}-100 text-${getActivityColor(activity.action)}-600 rounded-full flex items-center justify-center">
                        <i class="fas ${getActivityIcon(activity.action)} text-xs"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-900">${activity.description}</p>
                    <p class="text-xs text-gray-500">${formatThaiDate(activity.created_at, true)}</p>
                </div>
            </div>
        `;
        container.append(activityHtml);
    });
}

/**
 * Get activity color based on action
 */
function getActivityColor(action) {
    const colors = {
        'login': 'green',
        'logout': 'gray',
        'create': 'blue',
        'update': 'yellow',
        'delete': 'red',
        'upload': 'purple',
        'download': 'indigo'
    };
    return colors[action] || 'gray';
}

/**
 * Get activity icon based on action
 */
function getActivityIcon(action) {
    const icons = {
        'login': 'fa-sign-in-alt',
        'logout': 'fa-sign-out-alt',
        'create': 'fa-plus',
        'update': 'fa-edit',
        'delete': 'fa-trash',
        'upload': 'fa-upload',
        'download': 'fa-download'
    };
    return icons[action] || 'fa-info';
}

/**
 * Initialize notifications
 */
function initializeNotifications() {
    loadNotifications();
    
    // Poll for new notifications every 30 seconds
    setInterval(loadNotifications, 30000);
}

/**
 * Load notifications
 */
function loadNotifications() {
    $.ajax({
        url: `${BASE_URL}/admin/api/notifications/unread.php`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                updateNotificationCount(response.count);
                updateNotificationDropdown(response.notifications);
            }
        },
        error: function() {
            console.error('Failed to load notifications');
        }
    });
}

/**
 * Update notification count
 */
function updateNotificationCount(count) {
    const badge = $('.notification-badge');
    if (count > 0) {
        badge.text(count).removeClass('hidden');
    } else {
        badge.addClass('hidden');
    }
}

/**
 * Update notification dropdown
 */
function updateNotificationDropdown(notifications) {
    const dropdown = $('#notificationDropdown');
    // Implementation would go here
}

/**
 * Initialize quick actions
 */
function initializeQuickActions() {
    // Quick add user
    $('#quickAddUser').on('click', function(e) {
        e.preventDefault();
        showQuickAddUserModal();
    });
    
    // Quick backup
    $('#quickBackup').on('click', function(e) {
        e.preventDefault();
        performQuickBackup();
    });
    
    // System check
    $('#systemCheck').on('click', function(e) {
        e.preventDefault();
        performSystemCheck();
    });
}

/**
 * Show quick add user modal
 */
function showQuickAddUserModal() {
    // Implementation would show a modal for quick user creation
    console.log('Quick add user modal');
}

/**
 * Perform quick backup
 */
function performQuickBackup() {
    Swal.fire({
        title: 'สำรองข้อมูล',
        text: 'คุณต้องการสำรองข้อมูลหรือไม่?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'ใช่, สำรอง',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            showLoading('กำลังสำรองข้อมูล...');
            
            $.ajax({
                url: `${BASE_URL}/admin/api/backup/quick.php`,
                method: 'POST',
                success: function(response) {
                    hideLoading();
                    if (response.success) {
                        Swal.fire('สำเร็จ!', 'สำรองข้อมูลเรียบร้อยแล้ว', 'success');
                    } else {
                        Swal.fire('ผิดพลาด!', response.message, 'error');
                    }
                },
                error: function() {
                    hideLoading();
                    Swal.fire('ผิดพลาด!', 'เกิดข้อผิดพลาดในการสำรองข้อมูล', 'error');
                }
            });
        }
    });
}

/**
 * Perform system check
 */
function performSystemCheck() {
    showLoading('กำลังตรวจสอบระบบ...');
    
    $.ajax({
        url: `${BASE_URL}/admin/api/system/check.php`,
        method: 'GET',
        success: function(response) {
            hideLoading();
            if (response.success) {
                showSystemCheckResults(response.results);
            } else {
                Swal.fire('ผิดพลาด!', response.message, 'error');
            }
        },
        error: function() {
            hideLoading();
            Swal.fire('ผิดพลาด!', 'เกิดข้อผิดพลาดในการตรวจสอบระบบ', 'error');
        }
    });
}

/**
 * Show system check results
 */
function showSystemCheckResults(results) {
    let html = '<div class="space-y-2">';
    
    results.forEach(result => {
        const statusClass = result.status === 'OK' ? 'text-green-600' : 'text-red-600';
        const icon = result.status === 'OK' ? 'fa-check-circle' : 'fa-exclamation-triangle';
        
        html += `
            <div class="flex items-center justify-between">
                <span>${result.name}</span>
                <span class="${statusClass}">
                    <i class="fas ${icon} mr-1"></i>
                    ${result.status}
                </span>
            </div>
        `;
    });
    
    html += '</div>';
    
    Swal.fire({
        title: 'ผลการตรวจสอบระบบ',
        html: html,
        icon: 'info',
        width: '500px'
    });
}

/**
 * Initialize real-time features
 */
function initializeRealTime() {
    // WebSocket connection for real-time updates
    if (typeof WebSocket !== 'undefined') {
        connectWebSocket();
    }
}

/**
 * Connect WebSocket for real-time updates
 */
function connectWebSocket() {
    // Implementation would connect to WebSocket server
    console.log('WebSocket connection would be established here');
}

/**
 * Refresh dashboard data
 */
function refreshDashboardData() {
    // Reload stats
    loadStats();
    
    // Reload charts
    initializeCharts();
    
    // Reload recent activities
    loadRecentActivities();
    
    // Reload notifications
    loadNotifications();
}

/**
 * Load stats data
 */
function loadStats() {
    $.ajax({
        url: `${BASE_URL}/admin/api/dashboard/stats.php`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                updateStatsCards(response.data);
            }
        },
        error: function() {
            console.error('Failed to load stats');
        }
    });
}

/**
 * Update stats cards
 */
function updateStatsCards(stats) {
    Object.keys(stats).forEach(key => {
        const $element = $(`[data-stat="${key}"]`);
        if ($element.length) {
            animateCounter($element, stats[key], 1000);
        }
    });
}

/**
 * Update clock
 */
function updateClock() {
    const now = new Date();
    const options = {
        timeZone: 'Asia/Bangkok',
        hour12: false,
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    };
    
    const timeString = now.toLocaleTimeString('th-TH', options);
    $('#clockWidget .time').text(timeString);
    
    const dateOptions = {
        timeZone: 'Asia/Bangkok',
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    };
    
    const dateString = now.toLocaleDateString('th-TH', dateOptions);
    $('#clockWidget .date').text(dateString);
}

/**
 * Load weather data
 */
function loadWeatherData() {
    // Implementation would load weather data from API
    console.log('Weather data would be loaded here');
}

/**
 * Load system status
 */
function loadSystemStatus() {
    $.ajax({
        url: `${BASE_URL}/admin/api/system/status.php`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                updateSystemStatus(response.data);
            }
        },
        error: function() {
            console.error('Failed to load system status');
        }
    });
}

/**
 * Update system status
 */
function updateSystemStatus(status) {
    const $widget = $('#systemStatusWidget');
    
    // Update CPU usage
    $widget.find('.cpu-usage').text(`${status.cpu}%`);
    $widget.find('.cpu-bar').css('width', `${status.cpu}%`);
    
    // Update memory usage
    $widget.find('.memory-usage').text(`${status.memory}%`);
    $widget.find('.memory-bar').css('width', `${status.memory}%`);
    
    // Update disk usage
    $widget.find('.disk-usage').text(`${status.disk}%`);
    $widget.find('.disk-bar').css('width', `${status.disk}%`);
}