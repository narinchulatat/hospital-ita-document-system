// DataTables specific JavaScript

$(document).ready(function() {
    // Initialize DataTables with custom settings
    initializeDataTables();
    
    // Initialize table features
    initializeTableFeatures();
    
    // Initialize bulk selection
    initializeBulkSelection();
    
    // Initialize responsive tables
    initializeResponsiveTables();
});

/**
 * Initialize DataTables
 */
function initializeDataTables() {
    // Default DataTable configuration
    const defaultConfig = {
        responsive: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "ทั้งหมด"]],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/th.json',
            search: "ค้นหา:",
            lengthMenu: "แสดง _MENU_ รายการ",
            info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
            infoEmpty: "แสดง 0 ถึง 0 จาก 0 รายการ",
            infoFiltered: "(กรองจาก _MAX_ รายการทั้งหมด)",
            paginate: {
                first: "แรก",
                last: "สุดท้าย",
                next: "ถัดไป",
                previous: "ก่อนหน้า"
            },
            emptyTable: "ไม่มีข้อมูล",
            zeroRecords: "ไม่พบข้อมูลที่ค้นหา"
        },
        dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center"f>>' +
             '<"table-responsive"t>' +
             '<"d-flex justify-content-between align-items-center mt-3"<"text-muted"i><"d-flex"p>>',
        columnDefs: [
            { orderable: false, targets: 'no-sort' },
            { searchable: false, targets: 'no-search' }
        ]
    };
    
    // Initialize all DataTables
    $('.data-table').each(function() {
        const $table = $(this);
        const customConfig = $table.data('config') || {};
        const config = $.extend(true, {}, defaultConfig, customConfig);
        
        const dataTable = $table.DataTable(config);
        
        // Store reference for later use
        $table.data('dataTable', dataTable);
        
        // Custom search
        if ($table.siblings('.table-search').length) {
            const $searchInput = $table.siblings('.table-search').find('input');
            $searchInput.on('keyup', function() {
                dataTable.search(this.value).draw();
            });
        }
    });
}

/**
 * Initialize table features
 */
function initializeTableFeatures() {
    // Row selection
    initializeRowSelection();
    
    // Column visibility toggle
    initializeColumnToggle();
    
    // Table density controls
    initializeTableDensity();
    
    // Export functions
    initializeTableExport();
    
    // Custom sorting
    initializeCustomSorting();
}

/**
 * Initialize row selection
 */
function initializeRowSelection() {
    // Select all functionality
    $(document).on('change', '.select-all', function() {
        const isChecked = $(this).prop('checked');
        const $table = $(this).closest('table');
        
        $table.find('.row-select').prop('checked', isChecked);
        updateBulkActions($table);
    });
    
    // Individual row selection
    $(document).on('change', '.row-select', function() {
        const $table = $(this).closest('table');
        const totalRows = $table.find('.row-select').length;
        const selectedRows = $table.find('.row-select:checked').length;
        
        // Update select all checkbox
        const $selectAll = $table.find('.select-all');
        $selectAll.prop('indeterminate', selectedRows > 0 && selectedRows < totalRows);
        $selectAll.prop('checked', selectedRows === totalRows);
        
        updateBulkActions($table);
    });
}

/**
 * Update bulk actions visibility
 */
function updateBulkActions($table) {
    const selectedRows = $table.find('.row-select:checked').length;
    const $bulkActions = $('.bulk-actions, .bulk-selection');
    
    if (selectedRows > 0) {
        $bulkActions.addClass('show');
        $bulkActions.find('.selected-count').text(selectedRows);
    } else {
        $bulkActions.removeClass('show');
    }
}

/**
 * Initialize bulk selection
 */
function initializeBulkSelection() {
    // Bulk action form submission
    $('.bulk-action-form').on('submit', function(e) {
        const selectedIds = getSelectedRowIds();
        
        if (selectedIds.length === 0) {
            e.preventDefault();
            AdminJS.showAlert('กรุณาเลือกรายการที่ต้องการดำเนินการ', 'warning');
            return false;
        }
        
        // Add selected IDs to form
        $(this).find('[name="selected_ids"]').val(selectedIds.join(','));
        
        // Show confirmation for destructive actions
        const action = $(this).find('[name="action"]').val();
        if (['delete', 'reject'].includes(action)) {
            e.preventDefault();
            
            Swal.fire({
                title: 'ยืนยันการดำเนินการ',
                text: `คุณต้องการ${action === 'delete' ? 'ลบ' : 'ไม่อนุมัติ'}รายการที่เลือก ${selectedIds.length} รายการหรือไม่?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    $(this)[0].submit();
                }
            });
        }
    });
    
    // Clear selection
    $('.clear-selection').on('click', function() {
        $('.row-select, .select-all').prop('checked', false);
        $('.bulk-actions, .bulk-selection').removeClass('show');
    });
}

/**
 * Get selected row IDs
 */
function getSelectedRowIds() {
    const ids = [];
    $('.row-select:checked').each(function() {
        ids.push($(this).val());
    });
    return ids;
}

/**
 * Initialize column visibility toggle
 */
function initializeColumnToggle() {
    $('.column-toggle').on('change', function() {
        const $table = $($(this).data('table'));
        const columnIndex = $(this).data('column');
        const dataTable = $table.data('dataTable');
        
        if (dataTable) {
            const column = dataTable.column(columnIndex);
            column.visible($(this).prop('checked'));
        }
    });
}

/**
 * Initialize table density controls
 */
function initializeTableDensity() {
    $('.table-density-control').on('change', function() {
        const density = $(this).val();
        const $table = $($(this).data('table'));
        
        // Remove existing density classes
        $table.removeClass('table-density-compact table-density-comfortable table-density-spacious');
        
        // Add new density class
        if (density !== 'default') {
            $table.addClass(`table-density-${density}`);
        }
        
        // Save preference
        localStorage.setItem('table-density', density);
    });
    
    // Restore saved density
    const savedDensity = localStorage.getItem('table-density');
    if (savedDensity) {
        $('.table-density-control').val(savedDensity).trigger('change');
    }
}

/**
 * Initialize table export
 */
function initializeTableExport() {
    $('.export-csv').on('click', function() {
        const $table = $($(this).data('table'));
        const dataTable = $table.data('dataTable');
        
        if (dataTable) {
            exportTableToCSV(dataTable, 'export.csv');
        }
    });
    
    $('.export-excel').on('click', function() {
        const $table = $($(this).data('table'));
        const dataTable = $table.data('dataTable');
        
        if (dataTable) {
            exportTableToExcel(dataTable, 'export.xlsx');
        }
    });
    
    $('.export-pdf').on('click', function() {
        const $table = $($(this).data('table'));
        const dataTable = $table.data('dataTable');
        
        if (dataTable) {
            exportTableToPDF(dataTable, 'export.pdf');
        }
    });
}

/**
 * Export table to CSV
 */
function exportTableToCSV(dataTable, filename) {
    const data = dataTable.rows().data().toArray();
    const headers = dataTable.columns().header().toArray().map(th => th.textContent);
    
    let csv = headers.join(',') + '\n';
    
    data.forEach(row => {
        const rowData = [];
        for (let i = 0; i < row.length; i++) {
            let cellData = typeof row[i] === 'string' ? row[i] : '';
            cellData = cellData.replace(/"/g, '""'); // Escape quotes
            cellData = cellData.replace(/<[^>]*>/g, ''); // Remove HTML tags
            rowData.push(`"${cellData}"`);
        }
        csv += rowData.join(',') + '\n';
    });
    
    downloadFile(csv, filename, 'text/csv');
}

/**
 * Export table to Excel
 */
function exportTableToExcel(dataTable, filename) {
    // This would require a library like SheetJS
    AdminJS.showAlert('ฟีเจอร์ Export Excel กำลังพัฒนา', 'info');
}

/**
 * Export table to PDF
 */
function exportTableToPDF(dataTable, filename) {
    // This would require a library like jsPDF
    AdminJS.showAlert('ฟีเจอร์ Export PDF กำลังพัฒนา', 'info');
}

/**
 * Download file
 */
function downloadFile(data, filename, type) {
    const blob = new Blob([data], { type: type });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);
}

/**
 * Initialize custom sorting
 */
function initializeCustomSorting() {
    // Thai text sorting
    $.fn.dataTable.ext.type.order['thai-string-pre'] = function(data) {
        return data ? data.toLowerCase().replace(/<[^>]*>/g, '') : '';
    };
    
    // File size sorting
    $.fn.dataTable.ext.type.order['file-size-pre'] = function(data) {
        const units = ['B', 'KB', 'MB', 'GB', 'TB'];
        const regex = /^(\d+(?:\.\d+)?)\s*(B|KB|MB|GB|TB)$/i;
        const match = data.match(regex);
        
        if (match) {
            const value = parseFloat(match[1]);
            const unit = match[2].toUpperCase();
            const multiplier = Math.pow(1024, units.indexOf(unit));
            return value * multiplier;
        }
        
        return 0;
    };
    
    // Date sorting (Thai format)
    $.fn.dataTable.ext.type.order['thai-date-pre'] = function(data) {
        const thaiMonths = {
            'ม.ค.': 1, 'ก.พ.': 2, 'มี.ค.': 3, 'เม.ย.': 4, 'พ.ค.': 5, 'มิ.ย.': 6,
            'ก.ค.': 7, 'ส.ค.': 8, 'ก.ย.': 9, 'ต.ค.': 10, 'พ.ย.': 11, 'ธ.ค.': 12
        };
        
        const regex = /(\d{1,2})\s+([ก-ฮ\.]+)\s+(\d{4})/;
        const match = data.match(regex);
        
        if (match) {
            const day = parseInt(match[1]);
            const month = thaiMonths[match[2]] || 1;
            const year = parseInt(match[3]) - 543; // Convert from Buddhist to Gregorian year
            
            return new Date(year, month - 1, day).getTime();
        }
        
        return 0;
    };
}

/**
 * Initialize responsive tables
 */
function initializeResponsiveTables() {
    // Mobile table toggle
    $('.table-mobile-toggle').on('click', function() {
        const $table = $($(this).data('table'));
        $table.closest('.table-responsive').toggleClass('table-mobile-stack active');
        
        const isStacked = $table.closest('.table-responsive').hasClass('active');
        $(this).text(isStacked ? 'แสดงแบบตาราง' : 'แสดงแบบ Stack');
    });
    
    // Auto-detect mobile and apply stacking
    function checkMobile() {
        if (window.innerWidth <= 768) {
            $('.table-responsive').addClass('table-mobile-stack');
        } else {
            $('.table-responsive').removeClass('table-mobile-stack active');
        }
    }
    
    checkMobile();
    $(window).on('resize', AdminJS.debounce(checkMobile, 250));
}

/**
 * Refresh table data
 */
function refreshTable(tableSelector) {
    const $table = $(tableSelector);
    const dataTable = $table.data('dataTable');
    
    if (dataTable) {
        dataTable.ajax.reload(null, false);
    } else {
        location.reload();
    }
}

/**
 * Update table row
 */
function updateTableRow(tableSelector, rowData, rowId) {
    const $table = $(tableSelector);
    const dataTable = $table.data('dataTable');
    
    if (dataTable) {
        const rowIndex = dataTable.row(`[data-id="${rowId}"]`).index();
        if (rowIndex !== undefined) {
            dataTable.row(rowIndex).data(rowData).draw(false);
        }
    }
}

/**
 * Add table row
 */
function addTableRow(tableSelector, rowData) {
    const $table = $(tableSelector);
    const dataTable = $table.data('dataTable');
    
    if (dataTable) {
        dataTable.row.add(rowData).draw(false);
    }
}

/**
 * Remove table row
 */
function removeTableRow(tableSelector, rowId) {
    const $table = $(tableSelector);
    const dataTable = $table.data('dataTable');
    
    if (dataTable) {
        dataTable.row(`[data-id="${rowId}"]`).remove().draw(false);
    }
}

// Export for global use
window.TableJS = {
    refreshTable,
    updateTableRow,
    addTableRow,
    removeTableRow,
    getSelectedRowIds,
    updateBulkActions,
    exportTableToCSV
};