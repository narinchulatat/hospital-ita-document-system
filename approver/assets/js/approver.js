/**
 * Approver Panel JavaScript
 * Common functionality for the approver interface
 */

class ApproverPanel {
    constructor() {
        this.baseUrl = document.querySelector('meta[name="base-url"]')?.content || '';
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        this.init();
    }
    
    init() {
        this.initEventListeners();
        this.initDataTables();
        this.initTooltips();
        this.loadStatistics();
    }
    
    initEventListeners() {
        // Bulk selection checkboxes
        $(document).on('change', '.select-all', this.handleSelectAll.bind(this));
        $(document).on('change', '.select-item', this.handleSelectItem.bind(this));
        
        // Quick approval buttons
        $(document).on('click', '.quick-approve', this.handleQuickApprove.bind(this));
        $(document).on('click', '.quick-reject', this.handleQuickReject.bind(this));
        
        // Bulk actions
        $(document).on('click', '.bulk-approve', this.handleBulkApprove.bind(this));
        $(document).on('click', '.bulk-reject', this.handleBulkReject.bind(this));
        
        // Document preview
        $(document).on('click', '.preview-document', this.handleDocumentPreview.bind(this));
        
        // Filter changes
        $(document).on('change', '#statusFilter, #categoryFilter, #dateFilter', this.handleFilterChange.bind(this));
        
        // Search
        $(document).on('input', '#searchInput', this.debounce(this.handleSearch.bind(this), 300));
        
        // Export buttons
        $(document).on('click', '.export-pdf', this.handleExportPDF.bind(this));
        $(document).on('click', '.export-excel', this.handleExportExcel.bind(this));
        
        // Print
        $(document).on('click', '.print-page', this.handlePrint.bind(this));
    }
    
    initDataTables() {
        if ($.fn.DataTable && $('.datatable').length) {
            $('.datatable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/th.json'
                },
                responsive: true,
                pageLength: 25,
                order: [[0, 'desc']],
                columnDefs: [
                    { orderable: false, targets: 'no-sort' }
                ]
            });
        }
    }
    
    initTooltips() {
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    }
    
    loadStatistics() {
        $.get(`${this.baseUrl}/approver/api/stats.php`, (data) => {
            if (data.success) {
                this.updateStatistics(data.stats);
            }
        }).catch(() => {
            console.log('Failed to load statistics');
        });
    }
    
    updateStatistics(stats) {
        Object.keys(stats).forEach(key => {
            const element = $(`[data-stat="${key}"]`);
            if (element.length) {
                this.animateNumber(element, parseInt(stats[key]) || 0);
            }
        });
    }
    
    animateNumber(element, target) {
        const current = parseInt(element.text()) || 0;
        const increment = Math.ceil((target - current) / 20);
        
        if (current < target) {
            element.text(current + increment);
            setTimeout(() => this.animateNumber(element, target), 50);
        } else {
            element.text(target.toLocaleString());
        }
    }
    
    handleSelectAll(e) {
        const isChecked = $(e.target).is(':checked');
        $('.select-item').prop('checked', isChecked);
        this.updateBulkActionButtons();
    }
    
    handleSelectItem(e) {
        const totalItems = $('.select-item').length;
        const checkedItems = $('.select-item:checked').length;
        
        $('.select-all').prop('checked', totalItems === checkedItems);
        this.updateBulkActionButtons();
    }
    
    updateBulkActionButtons() {
        const selectedCount = $('.select-item:checked').length;
        const bulkActions = $('.bulk-actions');
        
        if (selectedCount > 0) {
            bulkActions.removeClass('hidden').find('.selected-count').text(selectedCount);
        } else {
            bulkActions.addClass('hidden');
        }
    }
    
    handleQuickApprove(e) {
        e.preventDefault();
        const documentId = $(e.target).closest('[data-document-id]').data('document-id');
        this.showApprovalModal(documentId, 'approve');
    }
    
    handleQuickReject(e) {
        e.preventDefault();
        const documentId = $(e.target).closest('[data-document-id]').data('document-id');
        this.showApprovalModal(documentId, 'reject');
    }
    
    handleBulkApprove(e) {
        e.preventDefault();
        const documentIds = this.getSelectedDocuments();
        if (documentIds.length === 0) {
            this.showWarning('กรุณาเลือกเอกสารที่ต้องการอนุมัติ');
            return;
        }
        this.showBulkApprovalModal(documentIds, 'approve');
    }
    
    handleBulkReject(e) {
        e.preventDefault();
        const documentIds = this.getSelectedDocuments();
        if (documentIds.length === 0) {
            this.showWarning('กรุณาเลือกเอกสารที่ต้องการไม่อนุมัติ');
            return;
        }
        this.showBulkApprovalModal(documentIds, 'reject');
    }
    
    getSelectedDocuments() {
        return $('.select-item:checked').map(function() {
            return $(this).closest('[data-document-id]').data('document-id');
        }).get();
    }
    
    showApprovalModal(documentId, action) {
        const title = action === 'approve' ? 'อนุมัติเอกสาร' : 'ไม่อนุมัติเอกสาร';
        const isReject = action === 'reject';
        
        Swal.fire({
            title: title,
            html: `
                <div class="text-left">
                    <label for="approval-comment" class="block text-sm font-medium text-gray-700 mb-2">
                        ความเห็น ${isReject ? '<span class="text-red-500">*</span>' : '(ไม่บังคับ)'}
                    </label>
                    <textarea id="approval-comment" 
                              class="w-full p-3 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" 
                              rows="4" 
                              placeholder="${isReject ? 'กรุณาระบุเหตุผลที่ไม่อนุมัติ...' : 'ระบุความเห็นเพิ่มเติม (ไม่บังคับ)...'}"
                              ${isReject ? 'required' : ''}></textarea>
                </div>
            `,
            icon: action === 'approve' ? 'question' : 'warning',
            showCancelButton: true,
            confirmButtonText: action === 'approve' ? 'อนุมัติ' : 'ไม่อนุมัติ',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: action === 'approve' ? '#10b981' : '#ef4444',
            preConfirm: () => {
                const comment = document.getElementById('approval-comment').value.trim();
                if (isReject && !comment) {
                    Swal.showValidationMessage('กรุณาระบุเหตุผลที่ไม่อนุมัติ');
                    return false;
                }
                return comment;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                this.processApproval([documentId], action, result.value);
            }
        });
    }
    
    showBulkApprovalModal(documentIds, action) {
        const title = action === 'approve' ? `อนุมัติเอกสาร ${documentIds.length} รายการ` : `ไม่อนุมัติเอกสาร ${documentIds.length} รายการ`;
        const isReject = action === 'reject';
        
        Swal.fire({
            title: title,
            html: `
                <div class="text-left">
                    <div class="mb-4 p-3 bg-gray-50 rounded-md">
                        <p class="text-sm text-gray-600">เอกสารที่เลือก: ${documentIds.length} รายการ</p>
                    </div>
                    <label for="bulk-approval-comment" class="block text-sm font-medium text-gray-700 mb-2">
                        ความเห็น ${isReject ? '<span class="text-red-500">*</span>' : '(ไม่บังคับ)'}
                    </label>
                    <textarea id="bulk-approval-comment" 
                              class="w-full p-3 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" 
                              rows="4" 
                              placeholder="${isReject ? 'กรุณาระบุเหตุผลที่ไม่อนุมัติ...' : 'ระบุความเห็นเพิ่มเติม (ไม่บังคับ)...'}"
                              ${isReject ? 'required' : ''}></textarea>
                </div>
            `,
            icon: action === 'approve' ? 'question' : 'warning',
            showCancelButton: true,
            confirmButtonText: action === 'approve' ? 'อนุมัติทั้งหมด' : 'ไม่อนุมัติทั้งหมด',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: action === 'approve' ? '#10b981' : '#ef4444',
            preConfirm: () => {
                const comment = document.getElementById('bulk-approval-comment').value.trim();
                if (isReject && !comment) {
                    Swal.showValidationMessage('กรุณาระบุเหตุผลที่ไม่อนุมัติ');
                    return false;
                }
                return comment;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                this.processApproval(documentIds, action, result.value);
            }
        });
    }
    
    processApproval(documentIds, action, comment) {
        this.showLoading();
        
        $.post(`${this.baseUrl}/approver/api/approve.php`, {
            document_ids: documentIds,
            action: action,
            comment: comment,
            csrf_token: this.csrfToken
        })
        .done((data) => {
            this.hideLoading();
            if (data.success) {
                this.showSuccess(data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                this.showError(data.message || 'เกิดข้อผิดพลาด');
            }
        })
        .fail(() => {
            this.hideLoading();
            this.showError('เกิดข้อผิดพลาดในการเชื่อมต่อ');
        });
    }
    
    handleDocumentPreview(e) {
        e.preventDefault();
        const documentId = $(e.target).closest('[data-document-id]').data('document-id');
        window.open(`${this.baseUrl}/approver/documents/view.php?id=${documentId}`, '_blank');
    }
    
    handleFilterChange(e) {
        this.applyFilters();
    }
    
    handleSearch(e) {
        this.applyFilters();
    }
    
    applyFilters() {
        const filters = {
            status: $('#statusFilter').val(),
            category: $('#categoryFilter').val(),
            date: $('#dateFilter').val(),
            search: $('#searchInput').val()
        };
        
        // Update URL parameters
        const url = new URL(window.location);
        Object.keys(filters).forEach(key => {
            if (filters[key]) {
                url.searchParams.set(key, filters[key]);
            } else {
                url.searchParams.delete(key);
            }
        });
        
        window.location.href = url.toString();
    }
    
    handleExportPDF(e) {
        e.preventDefault();
        const currentUrl = new URL(window.location);
        currentUrl.searchParams.set('export', 'pdf');
        window.open(currentUrl.toString(), '_blank');
    }
    
    handleExportExcel(e) {
        e.preventDefault();
        const currentUrl = new URL(window.location);
        currentUrl.searchParams.set('export', 'excel');
        window.location.href = currentUrl.toString();
    }
    
    handlePrint(e) {
        e.preventDefault();
        window.print();
    }
    
    // Utility functions
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    showLoading() {
        if (typeof showLoading === 'function') {
            showLoading();
        } else {
            $('#loadingOverlay').removeClass('hidden');
        }
    }
    
    hideLoading() {
        if (typeof hideLoading === 'function') {
            hideLoading();
        } else {
            $('#loadingOverlay').addClass('hidden');
        }
    }
    
    showSuccess(message) {
        if (typeof showSuccess === 'function') {
            showSuccess(message);
        } else {
            Swal.fire({ icon: 'success', title: 'สำเร็จ', text: message, timer: 3000 });
        }
    }
    
    showError(message) {
        if (typeof showError === 'function') {
            showError(message);
        } else {
            Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: message });
        }
    }
    
    showWarning(message) {
        if (typeof showWarning === 'function') {
            showWarning(message);
        } else {
            Swal.fire({ icon: 'warning', title: 'คำเตือน', text: message });
        }
    }
    
    showInfo(message) {
        if (typeof showInfo === 'function') {
            showInfo(message);
        } else {
            Swal.fire({ icon: 'info', title: 'ข้อมูล', text: message });
        }
    }
}

// Chart utilities
class ChartManager {
    constructor() {
        this.charts = {};
    }
    
    createApprovalChart(canvasId, data) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;
        
        this.charts[canvasId] = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['อนุมัติแล้ว', 'รออนุมัติ', 'ไม่อนุมัติ'],
                datasets: [{
                    data: [data.approved, data.pending, data.rejected],
                    backgroundColor: [
                        '#10b981',
                        '#f59e0b',
                        '#ef4444'
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
                        position: 'bottom'
                    }
                }
            }
        });
    }
    
    createTrendChart(canvasId, data) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;
        
        this.charts[canvasId] = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'เอกสารที่อนุมัติ',
                    data: data.approved,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true
                }, {
                    label: 'เอกสารที่ไม่อนุมัติ',
                    data: data.rejected,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    destroyChart(canvasId) {
        if (this.charts[canvasId]) {
            this.charts[canvasId].destroy();
            delete this.charts[canvasId];
        }
    }
    
    destroyAllCharts() {
        Object.keys(this.charts).forEach(chartId => {
            this.destroyChart(chartId);
        });
    }
}

// Initialize when DOM is ready
$(document).ready(function() {
    window.approverPanel = new ApproverPanel();
    window.chartManager = new ChartManager();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { ApproverPanel, ChartManager };
}