// Charts JavaScript for Reports

// Chart configuration
const chartConfig = {
    defaults: {
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
                }
            }
        }
    },
    
    colors: {
        primary: '#3B82F6',
        secondary: '#64748B',
        success: '#10B981',
        warning: '#F59E0B',
        danger: '#EF4444',
        info: '#06B6D4',
        light: '#F1F5F9',
        dark: '#1E293B'
    },
    
    gradients: {
        primary: ['#3B82F6', '#60A5FA'],
        success: ['#10B981', '#34D399'],
        warning: ['#F59E0B', '#FBBF24'],
        danger: ['#EF4444', '#F87171'],
        info: ['#06B6D4', '#22D3EE']
    }
};

// Chart types and configurations
const chartTypes = {
    line: {
        type: 'line',
        options: {
            ...chartConfig.defaults,
            elements: {
                line: {
                    tension: 0.4
                },
                point: {
                    radius: 4,
                    hoverRadius: 8
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
        }
    },
    
    bar: {
        type: 'bar',
        options: {
            ...chartConfig.defaults,
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
        }
    },
    
    pie: {
        type: 'pie',
        options: {
            ...chartConfig.defaults,
            plugins: {
                ...chartConfig.defaults.plugins,
                tooltip: {
                    ...chartConfig.defaults.plugins.tooltip,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${formatNumber(value)} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    },
    
    doughnut: {
        type: 'doughnut',
        options: {
            ...chartConfig.defaults,
            plugins: {
                ...chartConfig.defaults.plugins,
                tooltip: {
                    ...chartConfig.defaults.plugins.tooltip,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${formatNumber(value)} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    },
    
    area: {
        type: 'line',
        options: {
            ...chartConfig.defaults,
            elements: {
                line: {
                    tension: 0.4,
                    fill: true
                },
                point: {
                    radius: 4,
                    hoverRadius: 8
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
        }
    }
};

// Initialize all charts
function initCharts() {
    // Initialize charts from data attributes
    document.querySelectorAll('.chart-container').forEach(container => {
        const chartId = container.getAttribute('data-chart-id');
        const chartType = container.getAttribute('data-chart-type');
        const chartData = container.getAttribute('data-chart-data');
        
        if (chartId && chartType && chartData) {
            try {
                const data = JSON.parse(chartData);
                createChart(chartId, chartType, data);
            } catch (error) {
                console.error('Error parsing chart data:', error);
            }
        }
    });
}

// Create chart
function createChart(chartId, type, data) {
    const canvas = document.getElementById(chartId);
    if (!canvas) {
        console.error(`Canvas with ID ${chartId} not found`);
        return;
    }
    
    const ctx = canvas.getContext('2d');
    
    // Destroy existing chart if it exists
    if (window.charts && window.charts[chartId]) {
        window.charts[chartId].destroy();
    }
    
    // Prepare data with colors
    const processedData = processChartData(data, type);
    
    // Get chart configuration
    const config = chartTypes[type] || chartTypes.bar;
    
    // Create chart
    const chart = new Chart(ctx, {
        type: config.type,
        data: processedData,
        options: config.options
    });
    
    // Store chart instance
    if (!window.charts) {
        window.charts = {};
    }
    window.charts[chartId] = chart;
    
    return chart;
}

// Process chart data and add colors
function processChartData(data, type) {
    const processedData = { ...data };
    
    if (processedData.datasets) {
        processedData.datasets.forEach((dataset, index) => {
            if (type === 'pie' || type === 'doughnut') {
                // Multiple colors for pie/doughnut charts
                dataset.backgroundColor = generateColors(dataset.data.length);
                dataset.borderColor = generateColors(dataset.data.length, 1);
                dataset.borderWidth = 2;
            } else {
                // Single color for other chart types
                const colorKey = Object.keys(chartConfig.colors)[index % Object.keys(chartConfig.colors).length];
                const color = chartConfig.colors[colorKey];
                
                dataset.backgroundColor = type === 'area' ? color + '20' : color;
                dataset.borderColor = color;
                dataset.borderWidth = 2;
                
                if (type === 'area') {
                    dataset.fill = true;
                }
            }
        });
    }
    
    return processedData;
}

// Generate colors for charts
function generateColors(count, alpha = 0.8) {
    const colors = Object.values(chartConfig.colors);
    const result = [];
    
    for (let i = 0; i < count; i++) {
        const color = colors[i % colors.length];
        result.push(alpha < 1 ? color + Math.floor(alpha * 255).toString(16) : color);
    }
    
    return result;
}

// Update chart data
function updateChart(chartId, newData) {
    if (window.charts && window.charts[chartId]) {
        const chart = window.charts[chartId];
        chart.data = newData;
        chart.update();
    }
}

// Destroy chart
function destroyChart(chartId) {
    if (window.charts && window.charts[chartId]) {
        window.charts[chartId].destroy();
        delete window.charts[chartId];
    }
}

// Resize chart
function resizeChart(chartId) {
    if (window.charts && window.charts[chartId]) {
        window.charts[chartId].resize();
    }
}

// Export chart as image
function exportChart(chartId, format = 'png') {
    if (window.charts && window.charts[chartId]) {
        const chart = window.charts[chartId];
        const url = chart.toBase64Image();
        
        const link = document.createElement('a');
        link.download = `chart_${chartId}.${format}`;
        link.href = url;
        link.click();
    }
}

// Create dashboard overview chart
function createDashboardOverviewChart(data) {
    const canvas = document.getElementById('dashboard-overview-chart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'เอกสารใหม่',
                data: data.documents,
                borderColor: chartConfig.colors.primary,
                backgroundColor: chartConfig.colors.primary + '20',
                fill: true,
                tension: 0.4
            }, {
                label: 'การดาวน์โหลด',
                data: data.downloads,
                borderColor: chartConfig.colors.success,
                backgroundColor: chartConfig.colors.success + '20',
                fill: true,
                tension: 0.4
            }, {
                label: 'ผู้ใช้งาน',
                data: data.users,
                borderColor: chartConfig.colors.warning,
                backgroundColor: chartConfig.colors.warning + '20',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            ...chartConfig.defaults,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'วันที่'
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'จำนวน'
                    }
                }
            }
        }
    });
}

// Create category distribution chart
function createCategoryDistributionChart(data) {
    const canvas = document.getElementById('category-distribution-chart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.values,
                backgroundColor: generateColors(data.labels.length),
                borderColor: generateColors(data.labels.length, 1),
                borderWidth: 2
            }]
        },
        options: {
            ...chartConfig.defaults,
            plugins: {
                ...chartConfig.defaults.plugins,
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${formatNumber(value)} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

// Create user activity chart
function createUserActivityChart(data) {
    const canvas = document.getElementById('user-activity-chart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'กิจกรรมรายวัน',
                data: data.values,
                backgroundColor: chartConfig.colors.info,
                borderColor: chartConfig.colors.info,
                borderWidth: 1
            }]
        },
        options: {
            ...chartConfig.defaults,
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'วันที่'
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'จำนวนกิจกรรม'
                    }
                }
            }
        }
    });
}

// Create approval timeline chart
function createApprovalTimelineChart(data) {
    const canvas = document.getElementById('approval-timeline-chart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'อนุมัติ',
                data: data.approved,
                borderColor: chartConfig.colors.success,
                backgroundColor: chartConfig.colors.success + '20',
                fill: false,
                tension: 0.4
            }, {
                label: 'ปฏิเสธ',
                data: data.rejected,
                borderColor: chartConfig.colors.danger,
                backgroundColor: chartConfig.colors.danger + '20',
                fill: false,
                tension: 0.4
            }, {
                label: 'รออนุมัติ',
                data: data.pending,
                borderColor: chartConfig.colors.warning,
                backgroundColor: chartConfig.colors.warning + '20',
                fill: false,
                tension: 0.4
            }]
        },
        options: {
            ...chartConfig.defaults,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'วันที่'
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'จำนวนเอกสาร'
                    }
                }
            }
        }
    });
}

// Create storage usage chart
function createStorageUsageChart(data) {
    const canvas = document.getElementById('storage-usage-chart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.values,
                backgroundColor: generateColors(data.labels.length),
                borderColor: generateColors(data.labels.length, 1),
                borderWidth: 2
            }]
        },
        options: {
            ...chartConfig.defaults,
            plugins: {
                ...chartConfig.defaults.plugins,
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${formatFileSize(value)} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

// Utility function for formatting numbers
function formatNumber(num) {
    if (num === null || num === undefined) return '0';
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// Utility function for formatting file sizes
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Make functions available globally
window.chartsJS = {
    initCharts,
    createChart,
    updateChart,
    destroyChart,
    resizeChart,
    exportChart,
    createDashboardOverviewChart,
    createCategoryDistributionChart,
    createUserActivityChart,
    createApprovalTimelineChart,
    createStorageUsageChart
};

// Initialize charts when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initCharts();
});

// Handle window resize
window.addEventListener('resize', function() {
    if (window.charts) {
        Object.keys(window.charts).forEach(chartId => {
            resizeChart(chartId);
        });
    }
});