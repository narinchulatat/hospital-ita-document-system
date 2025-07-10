/**
 * Dashboard JavaScript Functions
 * สำหรับจัดการฟังก์ชันการทำงานของแดชบอร์ด
 */

// ตัวแปรสำหรับเก็บ Chart instances
let chartsInstances = {};

$(document).ready(function() {
    // เริ่มต้นฟังก์ชันแดชบอร์ด
    initializeDashboard();
    
    // โหลดข้อมูลสถิติ
    loadDashboardStats();
    
    // สร้างกราฟต่างๆ
    initializeCharts();
    
    // เริ่มต้น Real-time updates
    initializeRealTimeUpdates();
});

/**
 * เริ่มต้นฟังก์ชันแดชบอร์ด
 */
function initializeDashboard() {
    // เพิ่มเอฟเฟคให้กับ stat cards
    $('.stats-card').hover(
        function() {
            $(this).addClass('transform-hover');
        },
        function() {
            $(this).removeClass('transform-hover');
        }
    );
    
    // เริ่มต้น tooltips สำหรับแดshboard
    initializeDashboardTooltips();
    
    // เริ่มต้น counters animation
    initializeCounters();
}

/**
 * โหลดข้อมูลสถิติแดชบอร์ด
 */
function loadDashboardStats() {
    $.ajax({
        url: BASE_URL + '/admin/api/dashboard-stats.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                updateStatsCards(response.data);
                updateRecentActivities(response.activities);
                updatePendingApprovals(response.pending);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading dashboard stats:', error);
        }
    });
}

/**
 * อัปเดตค่าใน stat cards
 */
function updateStatsCards(stats) {
    // อัปเดตจำนวนผู้ใช้
    animateCounter('#total-users', stats.total_users);
    
    // อัปเดตจำนวนเอกสาร
    animateCounter('#total-documents', stats.total_documents);
    
    // อัปเดตเอกสารรออนุมัติ
    animateCounter('#pending-documents', stats.pending_documents);
    
    // อัปเดตขนาดไฟล์ที่ใช้
    $('#storage-used').text(formatFileSize(stats.storage_used));
    
    // อัปเดตจำนวนดาวน์โหลด
    animateCounter('#total-downloads', stats.total_downloads);
}

/**
 * สร้าง counter animation
 */
function animateCounter(selector, targetValue) {
    const element = $(selector);
    const currentValue = parseInt(element.text().replace(/,/g, '')) || 0;
    
    if (currentValue !== targetValue) {
        $({ countNum: currentValue }).animate({
            countNum: targetValue
        }, {
            duration: 1000,
            easing: 'linear',
            step: function() {
                element.text(formatNumber(Math.floor(this.countNum)));
            },
            complete: function() {
                element.text(formatNumber(targetValue));
            }
        });
    }
}

/**
 * เริ่มต้นกราฟต่างๆ
 */
function initializeCharts() {
    // กราฟผู้ใช้ตามบทบาท
    createUsersByRoleChart();
    
    // กราฟเอกสารตามสถานะ
    createDocumentsByStatusChart();
    
    // กราฟกิจกรรมรายวัน
    createDailyActivityChart();
    
    // กราฟการดาวน์โหลดรายเดือน
    createMonthlyDownloadsChart();
}

/**
 * สร้างกราฟผู้ใช้ตามบทบาท
 */
function createUsersByRoleChart() {
    const ctx = document.getElementById('usersByRoleChart');
    if (!ctx) return;
    
    // โหลดข้อมูลจาก API
    $.ajax({
        url: BASE_URL + '/admin/api/users-by-role.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const chart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: response.data.labels,
                        datasets: [{
                            data: response.data.values,
                            backgroundColor: [
                                '#3b82f6', // blue
                                '#10b981', // green
                                '#f59e0b', // yellow
                                '#ef4444', // red
                                '#8b5cf6', // purple
                                '#06b6d4'  // cyan
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
                                    usePointStyle: true,
                                    font: {
                                        family: 'Sarabun',
                                        size: 14
                                    }
                                }
                            },
                            tooltip: {
                                titleFont: {
                                    family: 'Sarabun'
                                },
                                bodyFont: {
                                    family: 'Sarabun'
                                }
                            }
                        }
                    }
                });
                
                chartsInstances.usersByRole = chart;
            }
        }
    });
}

/**
 * สร้างกราฟเอกสารตามสถานะ
 */
function createDocumentsByStatusChart() {
    const ctx = document.getElementById('documentsByStatusChart');
    if (!ctx) return;
    
    $.ajax({
        url: BASE_URL + '/admin/api/documents-by-status.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: response.data.labels,
                        datasets: [{
                            label: 'จำนวนเอกสาร',
                            data: response.data.values,
                            backgroundColor: [
                                '#10b981', // approved - green
                                '#f59e0b', // pending - yellow
                                '#ef4444', // rejected - red
                                '#6b7280'  // draft - gray
                            ],
                            borderWidth: 1,
                            borderRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    font: {
                                        family: 'Sarabun'
                                    }
                                }
                            },
                            x: {
                                ticks: {
                                    font: {
                                        family: 'Sarabun'
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                titleFont: {
                                    family: 'Sarabun'
                                },
                                bodyFont: {
                                    family: 'Sarabun'
                                }
                            }
                        }
                    }
                });
                
                chartsInstances.documentsByStatus = chart;
            }
        }
    });
}

/**
 * สร้างกราฟกิจกรรมรายวัน
 */
function createDailyActivityChart() {
    const ctx = document.getElementById('dailyActivityChart');
    if (!ctx) return;
    
    $.ajax({
        url: BASE_URL + '/admin/api/daily-activity.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: response.data.labels,
                        datasets: [{
                            label: 'กิจกรรม',
                            data: response.data.values,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#3b82f6',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    font: {
                                        family: 'Sarabun'
                                    }
                                }
                            },
                            x: {
                                ticks: {
                                    font: {
                                        family: 'Sarabun'
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                titleFont: {
                                    family: 'Sarabun'
                                },
                                bodyFont: {
                                    family: 'Sarabun'
                                }
                            }
                        }
                    }
                });
                
                chartsInstances.dailyActivity = chart;
            }
        }
    });
}

/**
 * สร้างกราฟการดาวน์โหลดรายเดือน
 */
function createMonthlyDownloadsChart() {
    const ctx = document.getElementById('monthlyDownloadsChart');
    if (!ctx) return;
    
    $.ajax({
        url: BASE_URL + '/admin/api/monthly-downloads.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: response.data.labels,
                        datasets: [{
                            label: 'ดาวน์โหลด',
                            data: response.data.values,
                            backgroundColor: '#10b981',
                            borderWidth: 1,
                            borderRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    font: {
                                        family: 'Sarabun'
                                    }
                                }
                            },
                            x: {
                                ticks: {
                                    font: {
                                        family: 'Sarabun'
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                titleFont: {
                                    family: 'Sarabun'
                                },
                                bodyFont: {
                                    family: 'Sarabun'
                                }
                            }
                        }
                    }
                });
                
                chartsInstances.monthlyDownloads = chart;
            }
        }
    });
}

/**
 * เริ่มต้น Real-time updates
 */
function initializeRealTimeUpdates() {
    // อัปเดตข้อมูลทุก 30 วินาที
    setInterval(function() {
        loadDashboardStats();
    }, 30000);
    
    // อัปเดตการแจ้งเตือนทุก 10 วินาที
    setInterval(function() {
        updateNotifications();
    }, 10000);
}

/**
 * อัปเดตการแจ้งเตือน
 */
function updateNotifications() {
    $.ajax({
        url: BASE_URL + '/admin/api/notifications.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const badge = $('.notification-badge');
                const count = response.data.unread_count;
                
                if (count > 0) {
                    badge.text(count).removeClass('hidden');
                } else {
                    badge.addClass('hidden');
                }
            }
        }
    });
}

/**
 * อัปเดตกิจกรรมล่าสุด
 */
function updateRecentActivities(activities) {
    const container = $('#recent-activities');
    if (!container.length || !activities) return;
    
    let html = '';
    activities.forEach(function(activity) {
        html += `
            <div class="flex items-start space-x-3 p-3 hover:bg-gray-50 rounded-lg">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-${getActivityIcon(activity.action)} text-blue-600 text-sm"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-900">
                        <span class="font-medium">${activity.user_name || 'ระบบ'}</span>
                        ${getActivityDescription(activity.action)}
                    </p>
                    <p class="text-xs text-gray-500">${formatThaiDate(activity.created_at, true)}</p>
                </div>
            </div>
        `;
    });
    
    container.html(html);
}

/**
 * อัปเดตเอกสารรออนุมัติ
 */
function updatePendingApprovals(pending) {
    const container = $('#pending-approvals');
    if (!container.length || !pending) return;
    
    let html = '';
    pending.forEach(function(doc) {
        html += `
            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h4 class="text-sm font-medium text-gray-900">
                            <a href="${BASE_URL}/admin/documents/view.php?id=${doc.id}" 
                               class="hover:text-blue-600">
                                ${doc.title}
                            </a>
                        </h4>
                        <p class="text-xs text-gray-500 mt-1">
                            อัปโหลดโดย: ${doc.uploader_name}
                        </p>
                        <p class="text-xs text-gray-500">
                            ${formatThaiDate(doc.created_at)}
                        </p>
                    </div>
                    <div class="ml-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            รออนุมัติ
                        </span>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.html(html);
}

/**
 * เริ่มต้น tooltips สำหรับแดshboard
 */
function initializeDashboardTooltips() {
    // เพิ่ม tooltips ให้กับ stat cards
    $('.stats-card').each(function() {
        const card = $(this);
        const tooltip = card.find('[data-tooltip]');
        
        if (tooltip.length) {
            card.attr('title', tooltip.data('tooltip'));
        }
    });
}

/**
 * เริ่มต้น counters animation
 */
function initializeCounters() {
    // สร้าง intersection observer สำหรับ animate counters เมื่อเข้ามาในมุมมอง
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const counter = entry.target;
                    const target = parseInt(counter.dataset.target || counter.textContent.replace(/,/g, ''));
                    animateCounter(counter, target);
                    observer.unobserve(counter);
                }
            });
        });
        
        document.querySelectorAll('.counter').forEach(function(counter) {
            observer.observe(counter);
        });
    }
}

/**
 * รีเฟรชแดชบอร์ด
 */
function refreshDashboard() {
    // แสดง loading indicator
    showLoading('กำลังโหลดข้อมูล...');
    
    // โหลดข้อมูลใหม่
    loadDashboardStats();
    
    // อัปเดตกราฟ
    Object.keys(chartsInstances).forEach(function(key) {
        if (chartsInstances[key]) {
            chartsInstances[key].destroy();
        }
    });
    
    setTimeout(function() {
        initializeCharts();
        hideLoading();
    }, 1000);
}

/**
 * Export dashboard functions
 */
window.Dashboard = {
    refresh: refreshDashboard,
    loadStats: loadDashboardStats,
    updateStats: updateStatsCards,
    createCharts: initializeCharts
};