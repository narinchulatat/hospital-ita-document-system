// DataTables configuration and functionality

$(document).ready(function() {
    initializeDataTables();
    initializeTableFilters();
    initializeTableActions();
});

/**
 * Initialize DataTables with TailwindCSS styling
 */
function initializeDataTables() {
    // Default DataTable configuration
    $.extend(true, $.fn.dataTable.defaults, {
        language: {
            url: getDataTablesLanguageUrl(),
            search: "ค้นหา:",
            lengthMenu: "แสดง _MENU_ รายการต่อหน้า",
            info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
            infoEmpty: "แสดง 0 ถึง 0 จาก 0 รายการ",
            infoFiltered: "(กรองจาก _MAX_ รายการทั้งหมด)",
            paginate: {
                first: "แรก",
                last: "สุดท้าย",
                next: "ถัดไป",
                previous: "ก่อนหน้า"
            },
            emptyTable: "ไม่มีข้อมูลในตาราง",
            zeroRecords: "ไม่พบข้อมูลที่ค้นหา"
        },
        responsive: true,
        processing: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "ทั้งหมด"]],
        dom: '<"flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4"<"mb-2 sm:mb-0"l><"flex items-center space-x-2"Bf>>rtip',
        buttons: {
            dom: {
                button: {
                    tag: 'button',
                    className: 'px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500'
                }
            },
            buttons: [
                {
                    extend: 'copy',
                    text: '<i class="fas fa-copy mr-1"></i>คัดลอก',
                    className: 'btn-copy'
                },
                {
                    extend: 'csv',
                    text: '<i class="fas fa-file-csv mr-1"></i>CSV',
                    className: 'btn-csv'
                },
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel mr-1"></i>Excel',
                    className: 'btn-excel'
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf mr-1"></i>PDF',
                    className: 'btn-pdf',
                    customize: function(doc) {
                        // Add Thai font support
                        doc.defaultStyle.font = 'THSarabunNew';
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print mr-1"></i>พิมพ์',
                    className: 'btn-print'
                }
            ]
        },
        columnDefs: [
            {
                targets: 'no-sort',
                orderable: false
            },
            {
                targets: 'text-center',
                className: 'text-center'
            },
            {
                targets: 'text-right',
                className: 'text-right'
            }
        ],
        drawCallback: function(settings) {
            // Apply TailwindCSS styling after draw
            applyTailwindStyling(this);
            
            // Initialize tooltips for new elements
            initializeTooltips();
            
            // Initialize action buttons
            initializeActionButtons();
        }
    });
    
    // Initialize all tables with .data-table class
    $('.data-table').each(function() {
        const $table = $(this);
        const config = getTableConfig($table);
        
        try {
            const table = $table.DataTable(config);
            
            // Store reference for later use
            $table.data('datatable', table);
            
            // Add custom search functionality
            addCustomSearch($table, table);
            
            // Add bulk actions if specified
            if ($table.hasClass('bulk-actions')) {
                addBulkActions($table, table);
            }
            
        } catch (error) {
            console.error('Error initializing DataTable:', error);
            showAlert('เกิดข้อผิดพลาดในการโหลดตาราง', 'error');
        }
    });
}

/**
 * Get DataTables language URL
 */
function getDataTablesLanguageUrl() {
    return 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/th.json';
}

/**
 * Get table configuration based on data attributes
 */
function getTableConfig($table) {
    const config = {};
    
    // Server-side processing
    if ($table.data('server-side')) {
        config.serverSide = true;
        config.ajax = {
            url: $table.data('ajax-url'),
            type: 'POST',
            data: function(d) {
                // Add custom filters
                const filters = getCustomFilters($table);
                $.extend(d, filters);
                
                // Add CSRF token
                d.csrf_token = $('meta[name="csrf-token"]').attr('content');
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables AJAX error:', error);
                showAlert('เกิดข้อผิดพลาดในการโหลดข้อมูล', 'error');
            }
        };
    }
    
    // Custom page length
    if ($table.data('page-length')) {
        config.pageLength = parseInt($table.data('page-length'));
    }
    
    // Disable sorting for specific columns
    if ($table.data('no-sort')) {
        const noSortColumns = $table.data('no-sort').split(',').map(col => parseInt(col.trim()));
        config.columnDefs = config.columnDefs || [];
        config.columnDefs.push({
            targets: noSortColumns,
            orderable: false
        });
    }
    
    // Custom ordering
    if ($table.data('order')) {
        const orderData = $table.data('order');
        if (Array.isArray(orderData)) {
            config.order = orderData;
        }
    }
    
    // Export filename
    if ($table.data('export-filename')) {
        config.buttons.forEach(button => {
            if (button.extend && ['copy', 'csv', 'excel', 'pdf'].includes(button.extend)) {
                button.filename = $table.data('export-filename');
            }
        });
    }
    
    return config;
}

/**
 * Apply TailwindCSS styling to DataTable elements
 */
function applyTailwindStyling(table) {
    const $wrapper = $(table.table().container());
    
    // Style the wrapper
    $wrapper.addClass('bg-white rounded-lg shadow overflow-hidden');
    
    // Style the table
    $(table.table().node())
        .addClass('min-w-full divide-y divide-gray-200')
        .removeClass('table table-striped table-bordered');
    
    // Style table header
    $(table.table().header())
        .addClass('bg-gray-50')
        .find('th')
        .addClass('px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider');
    
    // Style table body
    $(table.table().body())
        .find('tr')
        .addClass('hover:bg-gray-50 transition-colors')
        .find('td')
        .addClass('px-6 py-4 whitespace-nowrap text-sm text-gray-900');
    
    // Style pagination
    $wrapper.find('.dataTables_paginate')
        .addClass('bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6');
    
    $wrapper.find('.paginate_button')
        .addClass('relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50')
        .removeClass('paginate_button');
    
    // Style search input
    $wrapper.find('input[type="search"]')
        .addClass('block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm')
        .removeClass('form-control');
    
    // Style length select
    $wrapper.find('select')
        .addClass('block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm')
        .removeClass('form-select');
    
    // Style info text
    $wrapper.find('.dataTables_info')
        .addClass('text-sm text-gray-700');
    
    // Style processing indicator
    $wrapper.find('.dataTables_processing')
        .addClass('bg-white bg-opacity-75 text-center p-4 text-gray-600');
}

/**
 * Initialize table filters
 */
function initializeTableFilters() {
    // Date range filters
    $('.date-range-filter').each(function() {
        const $filter = $(this);
        const $table = $($filter.data('table'));
        
        $filter.on('change', function() {
            if ($table.length && $table.data('datatable')) {
                $table.data('datatable').ajax.reload();
            }
        });
    });
    
    // Select filters
    $('.select-filter').each(function() {
        const $filter = $(this);
        const $table = $($filter.data('table'));
        
        $filter.on('change', function() {
            if ($table.length && $table.data('datatable')) {
                $table.data('datatable').ajax.reload();
            }
        });
    });
    
    // Search filters
    $('.search-filter').each(function() {
        const $filter = $(this);
        const $table = $($filter.data('table'));
        
        $filter.on('keyup', debounce(function() {
            if ($table.length && $table.data('datatable')) {
                $table.data('datatable').ajax.reload();
            }
        }, 500));
    });
    
    // Clear filters button
    $('.clear-filters').on('click', function() {
        const $table = $($(this).data('table'));
        
        // Clear all filter inputs
        $('.date-range-filter, .select-filter, .search-filter').val('').trigger('change');
        
        // Reload table
        if ($table.length && $table.data('datatable')) {
            $table.data('datatable').ajax.reload();
        }
    });
}

/**
 * Get custom filters for server-side processing
 */
function getCustomFilters($table) {
    const filters = {};
    const tableId = $table.attr('id');
    
    // Date range filters
    $(`.date-range-filter[data-table="#${tableId}"]`).each(function() {
        const $filter = $(this);
        const name = $filter.attr('name') || $filter.data('filter');
        if (name && $filter.val()) {
            filters[name] = $filter.val();
        }
    });
    
    // Select filters
    $(`.select-filter[data-table="#${tableId}"]`).each(function() {
        const $filter = $(this);
        const name = $filter.attr('name') || $filter.data('filter');
        if (name && $filter.val()) {
            filters[name] = $filter.val();
        }
    });
    
    // Search filters
    $(`.search-filter[data-table="#${tableId}"]`).each(function() {
        const $filter = $(this);
        const name = $filter.attr('name') || $filter.data('filter');
        if (name && $filter.val()) {
            filters[name] = $filter.val();
        }
    });
    
    return filters;
}

/**
 * Add custom search functionality
 */
function addCustomSearch($table, dataTable) {
    const $searchInput = $table.closest('.dataTables_wrapper').find('input[type="search"]');
    
    // Add search delay
    $searchInput.off('keyup.DT search.DT input.DT paste.DT cut.DT');
    $searchInput.on('keyup', debounce(function() {
        if (dataTable.search() !== this.value) {
            dataTable.search(this.value).draw();
        }
    }, 500));
}

/**
 * Add bulk actions functionality
 */
function addBulkActions($table, dataTable) {
    // Add select all checkbox to header
    const $headerCheckbox = $('<input type="checkbox" class="select-all-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">');
    $table.find('thead tr th:first-child').html($headerCheckbox);
    
    // Add individual checkboxes to each row
    dataTable.on('draw', function() {
        $table.find('tbody tr').each(function() {
            const $row = $(this);
            const id = $row.data('id');
            if (id) {
                const $checkbox = $('<input type="checkbox" class="row-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">');
                $checkbox.val(id);
                $row.find('td:first-child').html($checkbox);
            }
        });
        
        updateSelectAllState();
    });
    
    // Handle select all
    $table.on('change', '.select-all-checkbox', function() {
        const isChecked = $(this).prop('checked');
        $table.find('.row-checkbox').prop('checked', isChecked);
        updateBulkActionsVisibility();
    });
    
    // Handle individual row selection
    $table.on('change', '.row-checkbox', function() {
        updateSelectAllState();
        updateBulkActionsVisibility();
    });
    
    // Initialize bulk actions toolbar
    initializeBulkActionsToolbar($table);
}

/**
 * Update select all checkbox state
 */
function updateSelectAllState() {
    $('.data-table').each(function() {
        const $table = $(this);
        const $selectAll = $table.find('.select-all-checkbox');
        const $checkboxes = $table.find('.row-checkbox');
        const $checked = $checkboxes.filter(':checked');
        
        if ($checked.length === 0) {
            $selectAll.prop('indeterminate', false).prop('checked', false);
        } else if ($checked.length === $checkboxes.length) {
            $selectAll.prop('indeterminate', false).prop('checked', true);
        } else {
            $selectAll.prop('indeterminate', true).prop('checked', false);
        }
    });
}

/**
 * Update bulk actions visibility
 */
function updateBulkActionsVisibility() {
    $('.data-table').each(function() {
        const $table = $(this);
        const $checked = $table.find('.row-checkbox:checked');
        const $bulkActions = $table.closest('.table-container').find('.bulk-actions-toolbar');
        
        if ($checked.length > 0) {
            $bulkActions.removeClass('hidden');
            $bulkActions.find('.selected-count').text($checked.length);
        } else {
            $bulkActions.addClass('hidden');
        }
    });
}

/**
 * Initialize bulk actions toolbar
 */
function initializeBulkActionsToolbar($table) {
    const tableId = $table.attr('id');
    const $container = $table.closest('.table-container');
    
    const toolbar = `
        <div class="bulk-actions-toolbar hidden bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-blue-700">
                        เลือกแล้ว <span class="selected-count font-semibold">0</span> รายการ
                    </span>
                    <button type="button" class="text-sm text-blue-600 hover:text-blue-800" onclick="clearSelection('${tableId}')">
                        ยกเลิกการเลือก
                    </button>
                </div>
                <div class="flex items-center space-x-2">
                    ${getBulkActionButtons($table)}
                </div>
            </div>
        </div>
    `;
    
    $container.prepend(toolbar);
}

/**
 * Get bulk action buttons based on table configuration
 */
function getBulkActionButtons($table) {
    const actions = $table.data('bulk-actions') || '';
    const buttons = [];
    
    if (actions.includes('delete')) {
        buttons.push(`
            <button type="button" class="bulk-delete px-3 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                <i class="fas fa-trash mr-1"></i>
                ลบ
            </button>
        `);
    }
    
    if (actions.includes('export')) {
        buttons.push(`
            <button type="button" class="bulk-export px-3 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                <i class="fas fa-download mr-1"></i>
                ส่งออก
            </button>
        `);
    }
    
    if (actions.includes('archive')) {
        buttons.push(`
            <button type="button" class="bulk-archive px-3 py-2 bg-yellow-600 text-white text-sm rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                <i class="fas fa-archive mr-1"></i>
                เก็บถาวร
            </button>
        `);
    }
    
    return buttons.join('');
}

/**
 * Initialize table actions
 */
function initializeTableActions() {
    // Bulk delete
    $(document).on('click', '.bulk-delete', function() {
        const $table = $(this).closest('.table-container').find('.data-table');
        const $checked = $table.find('.row-checkbox:checked');
        const ids = $checked.map(function() { return $(this).val(); }).get();
        
        if (ids.length === 0) {
            showAlert('กรุณาเลือกรายการที่ต้องการลบ', 'warning');
            return;
        }
        
        confirmBulkDelete(ids, $table);
    });
    
    // Bulk export
    $(document).on('click', '.bulk-export', function() {
        const $table = $(this).closest('.table-container').find('.data-table');
        const $checked = $table.find('.row-checkbox:checked');
        const ids = $checked.map(function() { return $(this).val(); }).get();
        
        if (ids.length === 0) {
            showAlert('กรุณาเลือกรายการที่ต้องการส่งออก', 'warning');
            return;
        }
        
        performBulkExport(ids, $table);
    });
    
    // Bulk archive
    $(document).on('click', '.bulk-archive', function() {
        const $table = $(this).closest('.table-container').find('.data-table');
        const $checked = $table.find('.row-checkbox:checked');
        const ids = $checked.map(function() { return $(this).val(); }).get();
        
        if (ids.length === 0) {
            showAlert('กรุณาเลือกรายการที่ต้องการเก็บถาวร', 'warning');
            return;
        }
        
        confirmBulkArchive(ids, $table);
    });
}

/**
 * Confirm bulk delete
 */
function confirmBulkDelete(ids, $table) {
    Swal.fire({
        title: 'ยืนยันการลบ',
        text: `คุณต้องการลบ ${ids.length} รายการที่เลือกหรือไม่?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'ใช่, ลบ!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            performBulkDelete(ids, $table);
        }
    });
}

/**
 * Perform bulk delete
 */
function performBulkDelete(ids, $table) {
    const deleteUrl = $table.data('bulk-delete-url');
    if (!deleteUrl) {
        showAlert('ไม่พบ URL สำหรับการลบ', 'error');
        return;
    }
    
    showLoading('กำลังลบข้อมูล...');
    
    $.ajax({
        url: deleteUrl,
        method: 'POST',
        data: {
            ids: ids,
            csrf_token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            hideLoading();
            
            if (response.success) {
                Swal.fire('สำเร็จ!', `ลบ ${response.deleted_count} รายการเรียบร้อยแล้ว`, 'success');
                
                // Reload table
                if ($table.data('datatable')) {
                    $table.data('datatable').ajax.reload();
                } else {
                    location.reload();
                }
                
                // Clear selection
                clearSelection($table.attr('id'));
            } else {
                Swal.fire('เกิดข้อผิดพลาด!', response.message || 'ไม่สามารถลบข้อมูลได้', 'error');
            }
        },
        error: function() {
            hideLoading();
            Swal.fire('เกิดข้อผิดพลาด!', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้', 'error');
        }
    });
}

/**
 * Perform bulk export
 */
function performBulkExport(ids, $table) {
    const exportUrl = $table.data('bulk-export-url');
    if (!exportUrl) {
        showAlert('ไม่พบ URL สำหรับการส่งออก', 'error');
        return;
    }
    
    // Create form for download
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = exportUrl;
    form.target = '_blank';
    
    // Add IDs
    ids.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = id;
        form.appendChild(input);
    });
    
    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    csrfInput.value = $('meta[name="csrf-token"]').attr('content');
    form.appendChild(csrfInput);
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
    
    showAlert('กำลังเตรียมไฟล์สำหรับดาวน์โหลด...', 'info', 3000);
}

/**
 * Confirm bulk archive
 */
function confirmBulkArchive(ids, $table) {
    Swal.fire({
        title: 'ยืนยันการเก็บถาวร',
        text: `คุณต้องการเก็บถาวร ${ids.length} รายการที่เลือกหรือไม่?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#F59E0B',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'ใช่, เก็บถาวร!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            performBulkArchive(ids, $table);
        }
    });
}

/**
 * Perform bulk archive
 */
function performBulkArchive(ids, $table) {
    const archiveUrl = $table.data('bulk-archive-url');
    if (!archiveUrl) {
        showAlert('ไม่พบ URL สำหรับการเก็บถาวร', 'error');
        return;
    }
    
    showLoading('กำลังเก็บถาวรข้อมูล...');
    
    $.ajax({
        url: archiveUrl,
        method: 'POST',
        data: {
            ids: ids,
            csrf_token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            hideLoading();
            
            if (response.success) {
                Swal.fire('สำเร็จ!', `เก็บถาวร ${response.archived_count} รายการเรียบร้อยแล้ว`, 'success');
                
                // Reload table
                if ($table.data('datatable')) {
                    $table.data('datatable').ajax.reload();
                } else {
                    location.reload();
                }
                
                // Clear selection
                clearSelection($table.attr('id'));
            } else {
                Swal.fire('เกิดข้อผิดพลาด!', response.message || 'ไม่สามารถเก็บถาวรข้อมูลได้', 'error');
            }
        },
        error: function() {
            hideLoading();
            Swal.fire('เกิดข้อผิดพลาด!', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้', 'error');
        }
    });
}

/**
 * Clear selection for a specific table
 */
function clearSelection(tableId) {
    const $table = $(`#${tableId}`);
    $table.find('.select-all-checkbox, .row-checkbox').prop('checked', false);
    updateBulkActionsVisibility();
}

/**
 * Initialize tooltips for table elements
 */
function initializeTooltips() {
    $('[data-tooltip]').each(function() {
        // Remove existing tooltip events
        $(this).off('mouseenter.tooltip mouseleave.tooltip');
        
        // Add new tooltip events
        $(this).on('mouseenter.tooltip', function() {
            const title = $(this).data('tooltip');
            if (title) {
                showTooltip($(this), title);
            }
        }).on('mouseleave.tooltip', function() {
            hideTooltip();
        });
    });
}

/**
 * Show tooltip
 */
function showTooltip($element, text) {
    hideTooltip(); // Hide any existing tooltip
    
    const tooltip = $(`
        <div class="tooltip fixed z-50 px-2 py-1 text-xs text-white bg-gray-900 rounded shadow-lg whitespace-nowrap pointer-events-none">
            ${text}
        </div>
    `);
    
    $('body').append(tooltip);
    
    const elementRect = $element[0].getBoundingClientRect();
    const tooltipRect = tooltip[0].getBoundingClientRect();
    
    const left = elementRect.left + (elementRect.width - tooltipRect.width) / 2;
    const top = elementRect.top - tooltipRect.height - 5;
    
    tooltip.css({
        left: Math.max(5, Math.min(left, window.innerWidth - tooltipRect.width - 5)),
        top: Math.max(5, top)
    });
}

/**
 * Hide tooltip
 */
function hideTooltip() {
    $('.tooltip').remove();
}

/**
 * Initialize action buttons
 */
function initializeActionButtons() {
    // Re-initialize delete confirmation for new buttons
    $('.btn-delete').off('click.delete').on('click.delete', function(e) {
        e.preventDefault();
        
        const url = $(this).attr('href') || $(this).data('url');
        const title = $(this).data('title') || 'ยืนยันการลบ';
        const text = $(this).data('text') || 'คุณแน่ใจหรือไม่ที่จะลบรายการนี้?';
        
        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'ใช่, ลบ!',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                if ($(this).hasClass('ajax-delete')) {
                    performAjaxDelete(url);
                } else {
                    window.location.href = url;
                }
            }
        });
    });
}

/**
 * Refresh specific table
 */
function refreshTable(tableId) {
    const $table = $(`#${tableId}`);
    if ($table.length && $table.data('datatable')) {
        $table.data('datatable').ajax.reload();
    }
}

/**
 * Export table data
 */
function exportTable(tableId, format) {
    const $table = $(`#${tableId}`);
    if ($table.length && $table.data('datatable')) {
        const table = $table.data('datatable');
        
        // Trigger export based on format
        switch (format) {
            case 'csv':
                table.button('.buttons-csv').trigger();
                break;
            case 'excel':
                table.button('.buttons-excel').trigger();
                break;
            case 'pdf':
                table.button('.buttons-pdf').trigger();
                break;
            case 'print':
                table.button('.buttons-print').trigger();
                break;
            default:
                console.error('Unsupported export format:', format);
        }
    }
}

// Global functions for external access
window.TableUtils = {
    refreshTable,
    exportTable,
    clearSelection,
    showTooltip,
    hideTooltip
};