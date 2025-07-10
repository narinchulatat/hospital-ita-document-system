// Reports JavaScript

// Global variables
let currentCharts = {};
let refreshInterval;

// Initialize reports
document.addEventListener('DOMContentLoaded', function() {
    initializeReports();
    setupEventListeners();
    
    // Auto-refresh every 5 minutes
    refreshInterval = setInterval(refreshData, 300000);
});

// Initialize reports
function initializeReports() {
    // Initialize DataTables
    initializeDataTables();
    
    // Initialize charts
    initializeCharts();
    
    // Initialize filters
    initializeFilters();
    
    // Initialize export buttons
    initializeExportButtons();
    
    // Initialize tooltips
    initializeTooltips();
}

// Initialize DataTables
function initializeDataTables() {
    $('.data-table').each(function() {
        if (!$.fn.DataTable.isDataTable(this)) {
            $(this).DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/th.json"
                },
                "responsive": true,
                "pageLength": 25,
                "order": [[ 0, "desc" ]],
                "columnDefs": [
                    { "orderable": false, "targets": -1 } // Disable sorting for last column (usually actions)
                ]
            });
        }
    });
}

// Initialize charts
function initializeCharts() {
    // Initialize all chart containers
    $('.chart-container').each(function() {
        const chartId = $(this).attr('id');
        const chartType = $(this).data('chart-type');
        const chartData = $(this).data('chart-data');
        
        if (chartId && chartType && chartData) {
            createChart(chartId, chartType, chartData);
        }
    });
}

// Create chart
function createChart(containerId, type, data) {
    const canvas = document.getElementById(containerId + '-canvas');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    // Destroy existing chart if it exists
    if (currentCharts[containerId]) {
        currentCharts[containerId].destroy();
    }
    
    // Default options
    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    padding: 20,
                    font: {
                        family: 'Sarabun',
                        size: 12
                    }
                }
            },
            tooltip: {
                titleFont: {
                    family: 'Sarabun',
                    size: 14
                },
                bodyFont: {
                    family: 'Sarabun',
                    size: 12
                },
                callbacks: {
                    label: function(context) {
                        const label = context.dataset.label || '';
                        const value = context.parsed.y || context.parsed;
                        return label + ': ' + formatNumber(value);
                    }
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    font: {
                        family: 'Sarabun',
                        size: 11
                    }
                },
                grid: {
                    display: false
                }
            },
            y: {
                ticks: {
                    font: {
                        family: 'Sarabun',
                        size: 11
                    },
                    callback: function(value) {
                        return formatNumber(value);
                    }
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                }
            }
        }
    };
    
    // Type-specific options
    let options = { ...defaultOptions };
    
    if (type === 'pie' || type === 'doughnut') {
        options.scales = {}; // Remove scales for pie/doughnut charts
    }
    
    if (type === 'line') {
        options.elements = {
            line: {
                tension: 0.4
            },
            point: {
                radius: 4,
                hoverRadius: 8
            }
        };
    }
    
    // Create chart
    currentCharts[containerId] = new Chart(ctx, {
        type: type,
        data: data,
        options: options
    });
}

// Initialize filters
function initializeFilters() {
    // Date range filter
    $('#dateRange').on('change', function() {
        const range = $(this).val();
        if (range === 'custom') {
            $('.custom-date-range').show();
        } else {
            $('.custom-date-range').hide();
            applyFilters();
        }
    });
    
    // Custom date range
    $('#startDate, #endDate').on('change', function() {
        if ($('#startDate').val() && $('#endDate').val()) {
            applyFilters();
        }
    });
    
    // Other filters
    $('.filter-select').on('change', function() {
        applyFilters();
    });
}

// Initialize export buttons
function initializeExportButtons() {
    $('.export-btn').on('click', function(e) {
        e.preventDefault();
        const format = $(this).data('format');
        exportReport(format);
    });
}

// Initialize tooltips
function initializeTooltips() {
    // Add tooltips to elements with data-tooltip attribute
    $('[data-tooltip]').each(function() {
        const tooltip = $(this).data('tooltip');
        $(this).attr('title', tooltip);
    });
}

// Setup event listeners
function setupEventListeners() {
    // Print button
    $('.print-btn').on('click', function() {
        window.print();
    });
    
    // Refresh button
    $('.refresh-btn').on('click', function() {
        refreshData();
    });
    
    // Full screen toggle
    $('.fullscreen-btn').on('click', function() {
        toggleFullScreen();
    });
    
    // Chart type toggle
    $('.chart-type-btn').on('click', function() {
        const chartId = $(this).data('chart-id');
        const chartType = $(this).data('chart-type');
        toggleChartType(chartId, chartType);
    });
    
    // Show/hide sections
    $('.toggle-section').on('click', function() {
        const target = $(this).data('target');
        $(target).toggle();
        $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
    });
}

// Apply filters
function applyFilters() {
    showLoading();
    
    const filterData = {
        dateRange: $('#dateRange').val(),
        startDate: $('#startDate').val(),
        endDate: $('#endDate').val(),
        category: $('#categoryFilter').val(),
        status: $('#statusFilter').val(),
        user: $('#userFilter').val()
    };
    
    // Update URL parameters
    updateUrlParams(filterData);
    
    // Reload page with new parameters
    setTimeout(function() {
        location.reload();
    }, 500);
}

// Reset filters
function resetFilters() {
    $('#filterForm')[0].reset();
    $('.custom-date-range').hide();
    
    // Clear URL parameters
    const url = new URL(window.location.href);
    const params = ['dateRange', 'startDate', 'endDate', 'category', 'status', 'user'];
    params.forEach(param => url.searchParams.delete(param));
    
    window.history.pushState({}, '', url.toString());
    location.reload();
}

// Export report
function exportReport(format) {
    showLoading();
    
    const currentUrl = window.location.href;
    const url = new URL(currentUrl);
    url.searchParams.set('export', format);
    
    // Create hidden form to submit POST request
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url.toString();
    form.style.display = 'none';
    
    // Add CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = csrfToken.content;
        form.appendChild(csrfInput);
    }
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
    
    setTimeout(hideLoading, 2000);
}

// Refresh data
function refreshData() {
    showLoading();
    
    // Refresh charts
    refreshCharts();
    
    // Refresh data tables
    refreshDataTables();
    
    // Refresh statistics
    refreshStatistics();
    
    setTimeout(hideLoading, 1000);
}

// Refresh charts
function refreshCharts() {
    Object.keys(currentCharts).forEach(chartId => {
        const chart = currentCharts[chartId];
        if (chart && chart.data) {
            // Fetch new data via AJAX
            fetchChartData(chartId).then(newData => {
                chart.data = newData;
                chart.update();
            });
        }
    });
}

// Refresh data tables
function refreshDataTables() {
    $('.data-table').each(function() {
        if ($.fn.DataTable.isDataTable(this)) {
            $(this).DataTable().ajax.reload(null, false);
        }
    });
}

// Refresh statistics
function refreshStatistics() {
    $('.stat-card').each(function() {
        const statType = $(this).data('stat-type');
        if (statType) {
            fetchStatistic(statType).then(data => {
                $(this).find('.stat-number').text(formatNumber(data.value));
                $(this).find('.stat-change').text(data.change);
            });
        }
    });
}

// Fetch chart data
async function fetchChartData(chartId) {
    try {
        const response = await fetch(`${window.location.origin}/reports/api/charts.php?chart=${chartId}`);
        return await response.json();
    } catch (error) {
        console.error('Error fetching chart data:', error);
        return {};
    }
}

// Fetch statistic
async function fetchStatistic(statType) {
    try {
        const response = await fetch(`${window.location.origin}/reports/api/data.php?type=${statType}`);
        return await response.json();
    } catch (error) {
        console.error('Error fetching statistic:', error);
        return { value: 0, change: '0%' };
    }
}

// Toggle chart type
function toggleChartType(chartId, newType) {
    const chart = currentCharts[chartId];
    if (chart) {
        chart.config.type = newType;
        chart.update();
    }
}

// Toggle full screen
function toggleFullScreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        }
    }
}

// Update URL parameters
function updateUrlParams(params) {
    const url = new URL(window.location.href);
    
    Object.keys(params).forEach(key => {
        if (params[key] && params[key] !== '') {
            url.searchParams.set(key, params[key]);
        } else {
            url.searchParams.delete(key);
        }
    });
    
    window.history.pushState({}, '', url.toString());
}

// Format number with commas
function formatNumber(num) {
    if (num === null || num === undefined) return '0';
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
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

// Show loading
function showLoading() {
    Swal.fire({
        title: 'กำลังโหลดข้อมูล...',
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

// Show confirmation dialog
function showConfirmation(title, text, callback) {
    Swal.fire({
        title: title,
        text: text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3B82F6',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'ตกลง',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed && callback) {
            callback();
        }
    });
}

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
    
    // Destroy all charts
    Object.keys(currentCharts).forEach(chartId => {
        if (currentCharts[chartId]) {
            currentCharts[chartId].destroy();
        }
    });
});

// Make functions available globally
window.reportsJS = {
    formatNumber,
    formatPercentage,
    formatFileSize,
    showLoading,
    hideLoading,
    showSuccess,
    showError,
    showConfirmation,
    applyFilters,
    resetFilters,
    exportReport,
    refreshData,
    createChart,
    toggleChartType,
    toggleFullScreen
};