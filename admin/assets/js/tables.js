// Tables JavaScript - DataTables with TailwindCSS

$(document).ready(function() {
    initializeDataTables();
    initializeTableFilters();
    initializeTableActions();
});

/**
 * Initialize DataTables with TailwindCSS styling
 */
function initializeDataTables() {
    // Default DataTables configuration for TailwindCSS
    const defaultConfig = {
        responsive: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "ทั้งหมด"]],
        language: {
            "sProcessing": "กำลังดำเนินการ...",
            "sLengthMenu": "แสดง _MENU_ รายการ",
            "sZeroRecords": "ไม่พบข้อมูลที่ค้นหา",
            "sInfo": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
            "sInfoEmpty": "แสดง 0 ถึง 0 จาก 0 รายการ",
            "sInfoFiltered": "(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)",
            "sInfoPostFix": "",
            "sSearch": "ค้นหา:",
            "sUrl": "",
            "sEmptyTable": "ไม่มีข้อมูลในตาราง",
            "sLoadingRecords": "กำลังโหลด...",
            "sInfoThousands": ",",
            "oPaginate": {
                "sFirst": "หน้าแรก",
                "sLast": "หน้าสุดท้าย",
                "sNext": "ถัดไป",
                "sPrevious": "ก่อนหน้า"
            },
            "oAria": {
                "sSortAscending": ": คลิกเพื่อเรียงลำดับคอลัมน์จากน้อยไปมาก",
                "sSortDescending": ": คลิกเพื่อเรียงลำดับคอลัมน์จากมากไปน้อย"
            }
        },
        dom: '<"flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4"<"flex items-center mb-2 sm:mb-0"l><"flex items-center"f>>rtip',
        initComplete: function() {
            // Apply TailwindCSS classes after initialization
            applyTailwindStyling(this);
        }
    };

    // Initialize standard data tables
    $('.data-table').each(function() {
        const $table = $(this);
        const config = $.extend({}, defaultConfig, $table.data('config') || {});
        
        // Add custom configuration based on table attributes
        if ($table.hasClass('ajax-table')) {
            config.processing = true;
            config.serverSide = true;
            config.ajax = {
                url: $table.data('ajax-url'),
                type: 'POST',
                data: function(d) {
                    // Add custom parameters
                    d.csrf_token = $('meta[name="csrf-token"]').attr('content');
                    
                    // Add filters
                    $('.table-filter').each(function() {
                        const name = $(this).attr('name');
                        const value = $(this).val();
                        if (value) {
                            d[name] = value;
                        }
                    });
                }
            };
        }
        
        const dataTable = $table.DataTable(config);
        
        // Store reference for later use
        $table.data('datatable', dataTable);
    });

    // Initialize user tables
    initializeUserTables();
    
    // Initialize document tables
    initializeDocumentTables();
    
    // Initialize category tables
    initializeCategoryTables();
    
    // Initialize activity log tables
    initializeActivityLogTables();
}

/**
 * Apply TailwindCSS styling to DataTables elements
 */
function applyTailwindStyling(table) {
    const $wrapper = $(table.table().container());
    
    // Style the wrapper
    $wrapper.addClass('bg-white shadow rounded-lg overflow-hidden');
    
    // Style the table
    $(table.table().node()).addClass('min-w-full divide-y divide-gray-200');
    
    // Style table headers
    $wrapper.find('thead th').addClass('px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider');
    
    // Style table body
    $wrapper.find('tbody tr').addClass('bg-white hover:bg-gray-50');
    $wrapper.find('tbody td').addClass('px-6 py-4 whitespace-nowrap text-sm text-gray-900');
    
    // Style pagination
    $wrapper.find('.dataTables_paginate .paginate_button').addClass('relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50');
    $wrapper.find('.dataTables_paginate .paginate_button.current').addClass('z-10 bg-blue-50 border-blue-500 text-blue-600');
    $wrapper.find('.dataTables_paginate .paginate_button.disabled').addClass('opacity-50 cursor-not-allowed');
    
    // Style search input
    $wrapper.find('.dataTables_filter input').addClass('block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm');
    
    // Style length select
    $wrapper.find('.dataTables_length select').addClass('block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md');
    
    // Add search icon
    const $searchWrapper = $wrapper.find('.dataTables_filter');
    $searchWrapper.addClass('relative');
    $searchWrapper.find('label').addClass('sr-only');
    $searchWrapper.prepend('<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-search text-gray-400"></i></div>');
}

/**
 * Initialize user tables
 */
function initializeUserTables() {
    $('#usersTable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[0, 'desc']],
        columnDefs: [
            {
                targets: [4], // Actions column
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `
                        <div class="flex items-center space-x-2">
                            <a href="${BASE_URL}/admin/users/view.php?id=${row.id}" 
                               class="text-blue-600 hover:text-blue-900" 
                               data-tooltip="ดูรายละเอียด">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="${BASE_URL}/admin/users/edit.php?id=${row.id}" 
                               class="text-green-600 hover:text-green-900"
                               data-tooltip="แก้ไข">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="${BASE_URL}/admin/users/delete.php?id=${row.id}" 
                               class="text-red-600 hover:text-red-900 btn-delete"
                               data-tooltip="ลบ"
                               data-title="ยืนยันการลบผู้ใช้"
                               data-text="คุณแน่ใจหรือไม่ที่จะลบผู้ใช้นี้?">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    `;
                }
            },
            {
                targets: [3], // Status column
                render: function(data, type, row) {
                    const statusClasses = {
                        'active': 'bg-green-100 text-green-800',
                        'inactive': 'bg-red-100 text-red-800',
                        'pending': 'bg-yellow-100 text-yellow-800'
                    };
                    
                    const statusTexts = {
                        'active': 'ใช้งาน',
                        'inactive': 'ไม่ใช้งาน',
                        'pending': 'รอดำเนินการ'
                    };
                    
                    const statusClass = statusClasses[data] || 'bg-gray-100 text-gray-800';
                    const statusText = statusTexts[data] || data;
                    
                    return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">${statusText}</span>`;
                }
            }
        ],
        language: {
            "sProcessing": "กำลังดำเนินการ...",
            "sLengthMenu": "แสดง _MENU_ รายการ",
            "sZeroRecords": "ไม่พบข้อมูลผู้ใช้",
            "sInfo": "แสดง _START_ ถึง _END_ จาก _TOTAL_ ผู้ใช้",
            "sInfoEmpty": "แสดง 0 ถึง 0 จาก 0 ผู้ใช้",
            "sInfoFiltered": "(กรองข้อมูลจากทั้งหมด _MAX_ ผู้ใช้)",
            "sSearch": "ค้นหาผู้ใช้:",
            "sEmptyTable": "ไม่มีข้อมูลผู้ใช้"
        }
    });
}

/**
 * Initialize document tables
 */
function initializeDocumentTables() {
    $('#documentsTable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[0, 'desc']],
        columnDefs: [
            {
                targets: [5], // Actions column
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    let actions = `
                        <div class="flex items-center space-x-2">
                            <a href="${BASE_URL}/admin/documents/view.php?id=${row.id}" 
                               class="text-blue-600 hover:text-blue-900" 
                               data-tooltip="ดูรายละเอียด">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="${BASE_URL}/public/documents/download.php?id=${row.id}" 
                               class="text-green-600 hover:text-green-900"
                               data-tooltip="ดาวน์โหลด">
                                <i class="fas fa-download"></i>
                            </a>
                    `;
                    
                    if (row.status === 'pending') {
                        actions += `
                            <a href="${BASE_URL}/admin/documents/approve.php?id=${row.id}" 
                               class="text-yellow-600 hover:text-yellow-900"
                               data-tooltip="อนุมัติ">
                                <i class="fas fa-check"></i>
                            </a>
                        `;
                    }
                    
                    actions += `
                            <a href="${BASE_URL}/admin/documents/edit.php?id=${row.id}" 
                               class="text-purple-600 hover:text-purple-900"
                               data-tooltip="แก้ไข">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="${BASE_URL}/admin/documents/delete.php?id=${row.id}" 
                               class="text-red-600 hover:text-red-900 btn-delete"
                               data-tooltip="ลบ"
                               data-title="ยืนยันการลบเอกสาร"
                               data-text="คุณแน่ใจหรือไม่ที่จะลบเอกสารนี้?">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    `;
                    
                    return actions;
                }
            },
            {
                targets: [4], // Status column
                render: function(data, type, row) {
                    const statusClasses = {
                        'approved': 'bg-green-100 text-green-800',
                        'pending': 'bg-yellow-100 text-yellow-800',
                        'rejected': 'bg-red-100 text-red-800',
                        'draft': 'bg-gray-100 text-gray-800'
                    };
                    
                    const statusTexts = {
                        'approved': 'อนุมัติแล้ว',
                        'pending': 'รออนุมัติ',
                        'rejected': 'ไม่อนุมัติ',
                        'draft': 'ฉบับร่าง'
                    };
                    
                    const statusClass = statusClasses[data] || 'bg-gray-100 text-gray-800';
                    const statusText = statusTexts[data] || data;
                    
                    return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">${statusText}</span>`;
                }
            },
            {
                targets: [3], // File size column
                render: function(data, type, row) {
                    if (type === 'display') {
                        return formatFileSize(data);
                    }
                    return data;
                }
            }
        ],
        language: {
            "sProcessing": "กำลังดำเนินการ...",
            "sLengthMenu": "แสดง _MENU_ รายการ",
            "sZeroRecords": "ไม่พบเอกสาร",
            "sInfo": "แสดง _START_ ถึง _END_ จาก _TOTAL_ เอกสาร",
            "sInfoEmpty": "แสดง 0 ถึง 0 จาก 0 เอกสาร",
            "sInfoFiltered": "(กรองข้อมูลจากทั้งหมด _MAX_ เอกสาร)",
            "sSearch": "ค้นหาเอกสาร:",
            "sEmptyTable": "ไม่มีเอกสาร"
        }
    });
}

/**
 * Initialize category tables
 */
function initializeCategoryTables() {
    $('#categoriesTable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[1, 'asc']],
        columnDefs: [
            {
                targets: [3], // Actions column
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `
                        <div class="flex items-center space-x-2">
                            <a href="${BASE_URL}/admin/categories/view.php?id=${row.id}" 
                               class="text-blue-600 hover:text-blue-900" 
                               data-tooltip="ดูรายละเอียด">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="${BASE_URL}/admin/categories/edit.php?id=${row.id}" 
                               class="text-green-600 hover:text-green-900"
                               data-tooltip="แก้ไข">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="${BASE_URL}/admin/categories/delete.php?id=${row.id}" 
                               class="text-red-600 hover:text-red-900 btn-delete"
                               data-tooltip="ลบ"
                               data-title="ยืนยันการลบหมวดหมู่"
                               data-text="คุณแน่ใจหรือไม่ที่จะลบหมวดหมู่นี้? เอกสารในหมวดหมู่นี้จะถูกย้ายไป 'ไม่มีหมวดหมู่'">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    `;
                }
            },
            {
                targets: [2], // Document count column
                render: function(data, type, row) {
                    return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">${data}</span>`;
                }
            }
        ],
        language: {
            "sProcessing": "กำลังดำเนินการ...",
            "sLengthMenu": "แสดง _MENU_ รายการ",
            "sZeroRecords": "ไม่พบหมวดหมู่",
            "sInfo": "แสดง _START_ ถึง _END_ จาก _TOTAL_ หมวดหมู่",
            "sInfoEmpty": "แสดง 0 ถึง 0 จาก 0 หมวดหมู่",
            "sInfoFiltered": "(กรองข้อมูลจากทั้งหมด _MAX_ หมวดหมู่)",
            "sSearch": "ค้นหาหมวดหมู่:",
            "sEmptyTable": "ไม่มีหมวดหมู่"
        }
    });
}

/**
 * Initialize activity log tables
 */
function initializeActivityLogTables() {
    $('#activityLogsTable').DataTable({
        responsive: true,
        pageLength: 50,
        order: [[0, 'desc']],
        columnDefs: [
            {
                targets: [1], // Action column
                render: function(data, type, row) {
                    const actionClasses = {
                        'CREATE': 'bg-green-100 text-green-800',
                        'UPDATE': 'bg-blue-100 text-blue-800',
                        'DELETE': 'bg-red-100 text-red-800',
                        'LOGIN': 'bg-purple-100 text-purple-800',
                        'LOGOUT': 'bg-gray-100 text-gray-800'
                    };
                    
                    const actionTexts = {
                        'CREATE': 'สร้าง',
                        'UPDATE': 'แก้ไข',
                        'DELETE': 'ลบ',
                        'LOGIN': 'เข้าสู่ระบบ',
                        'LOGOUT': 'ออกจากระบบ'
                    };
                    
                    const actionClass = actionClasses[data] || 'bg-gray-100 text-gray-800';
                    const actionText = actionTexts[data] || data;
                    
                    return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${actionClass}">${actionText}</span>`;
                }
            },
            {
                targets: [4], // Created at column
                render: function(data, type, row) {
                    if (type === 'display') {
                        return formatThaiDate(data, true);
                    }
                    return data;
                }
            }
        ],
        language: {
            "sProcessing": "กำลังดำเนินการ...",
            "sLengthMenu": "แสดง _MENU_ รายการ",
            "sZeroRecords": "ไม่พบบันทึกกิจกรรม",
            "sInfo": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
            "sInfoEmpty": "แสดง 0 ถึง 0 จาก 0 รายการ",
            "sInfoFiltered": "(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)",
            "sSearch": "ค้นหากิจกรรม:",
            "sEmptyTable": "ไม่มีบันทึกกิจกรรม"
        }
    });
}

/**
 * Initialize table filters
 */
function initializeTableFilters() {
    // Status filter
    $('.status-filter').on('change', function() {
        const table = $(this).closest('.table-container').find('.data-table').DataTable();
        const column = $(this).data('column');
        const value = $(this).val();
        
        table.column(column).search(value).draw();
    });
    
    // Date range filter
    $('.date-filter').on('change', function() {
        const table = $(this).closest('.table-container').find('.data-table').DataTable();
        table.draw();
    });
    
    // Category filter
    $('.category-filter').on('change', function() {
        const table = $(this).closest('.table-container').find('.data-table').DataTable();
        const column = $(this).data('column');
        const value = $(this).val();
        
        table.column(column).search(value).draw();
    });
    
    // Reset filters button
    $('.reset-filters').on('click', function() {
        const $container = $(this).closest('.table-container');
        const table = $container.find('.data-table').DataTable();
        
        // Reset all filters
        $container.find('.table-filter').val('').trigger('change');
        
        // Clear table search and redraw
        table.search('').columns().search('').draw();
    });
}

/**
 * Initialize table actions
 */
function initializeTableActions() {
    // Bulk actions
    $('.select-all').on('change', function() {
        const isChecked = $(this).is(':checked');
        $(this).closest('table').find('.select-row').prop('checked', isChecked);
        updateBulkActionButtons();
    });
    
    $(document).on('change', '.select-row', function() {
        updateBulkActionButtons();
    });
    
    // Bulk delete
    $('.bulk-delete').on('click', function() {
        const selectedRows = $('.select-row:checked');
        
        if (selectedRows.length === 0) {
            Swal.fire({
                title: 'กรุณาเลือกรายการ',
                text: 'กรุณาเลือกรายการที่ต้องการลบ',
                icon: 'warning',
                customClass: {
                    popup: 'font-sans'
                }
            });
            return;
        }
        
        Swal.fire({
            title: 'ยืนยันการลบหลายรายการ',
            text: `คุณแน่ใจหรือไม่ที่จะลบ ${selectedRows.length} รายการ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ใช่, ลบ!',
            cancelButtonText: 'ยกเลิก',
            customClass: {
                popup: 'font-sans'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                performBulkDelete(selectedRows);
            }
        });
    });
    
    // Export actions
    $('.export-csv').on('click', function() {
        const table = $(this).closest('.table-container').find('.data-table').DataTable();
        exportTableData(table, 'csv');
    });
    
    $('.export-excel').on('click', function() {
        const table = $(this).closest('.table-container').find('.data-table').DataTable();
        exportTableData(table, 'excel');
    });
    
    $('.export-pdf').on('click', function() {
        const table = $(this).closest('.table-container').find('.data-table').DataTable();
        exportTableData(table, 'pdf');
    });
}

/**
 * Update bulk action buttons
 */
function updateBulkActionButtons() {
    const selectedCount = $('.select-row:checked').length;
    const $bulkActions = $('.bulk-actions');
    
    if (selectedCount > 0) {
        $bulkActions.removeClass('hidden');
        $bulkActions.find('.selected-count').text(selectedCount);
    } else {
        $bulkActions.addClass('hidden');
    }
}

/**
 * Perform bulk delete
 */
function performBulkDelete(selectedRows) {
    const ids = [];
    selectedRows.each(function() {
        ids.push($(this).val());
    });
    
    showLoading('กำลังลบ...');
    
    $.ajax({
        url: window.location.pathname.replace('/index.php', '') + '/bulk-delete.php',
        method: 'POST',
        data: {
            ids: ids,
            csrf_token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            hideLoading();
            
            if (response.success) {
                Swal.fire({
                    title: 'ลบเรียบร้อย',
                    text: `ลบ ${response.deleted_count} รายการเรียบร้อยแล้ว`,
                    icon: 'success',
                    customClass: {
                        popup: 'font-sans'
                    }
                }).then(() => {
                    location.reload();
                });
            } else {
                throw new Error(response.message || 'เกิดข้อผิดพลาดในการลบ');
            }
        },
        error: function(xhr, status, error) {
            hideLoading();
            Swal.fire({
                title: 'เกิดข้อผิดพลาด',
                text: 'ไม่สามารถลบรายการได้ กรุณาลองใหม่อีกครั้ง',
                icon: 'error',
                customClass: {
                    popup: 'font-sans'
                }
            });
        }
    });
}

/**
 * Export table data
 */
function exportTableData(table, format) {
    const data = table.data().toArray();
    const headers = table.columns().header().toArray().map(header => $(header).text());
    
    // Create export request
    $.ajax({
        url: BASE_URL + '/admin/api/export.php',
        method: 'POST',
        data: {
            format: format,
            headers: headers,
            data: data,
            csrf_token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                // Download file
                const link = document.createElement('a');
                link.href = response.file_url;
                link.download = response.filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                showAlert('ส่งออกข้อมูลเรียบร้อยแล้ว', 'success');
            } else {
                throw new Error(response.message || 'เกิดข้อผิดพลาดในการส่งออกข้อมูล');
            }
        },
        error: function() {
            showAlert('เกิดข้อผิดพลาดในการส่งออกข้อมูล', 'error');
        }
    });
}

/**
 * Format file size
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
 * Format Thai date
 */
function formatThaiDate(dateString, showTime = false) {
    if (window.AdminJS && window.AdminJS.formatThaiDate) {
        return window.AdminJS.formatThaiDate(dateString, showTime);
    }
    return new Date(dateString).toLocaleDateString('th-TH');
}

/**
 * Show alert
 */
function showAlert(message, type) {
    if (window.AdminJS && window.AdminJS.showAlert) {
        window.AdminJS.showAlert(message, type);
    }
}

/**
 * Show/hide loading
 */
function showLoading(message) {
    if (window.AdminJS && window.AdminJS.showLoading) {
        window.AdminJS.showLoading(message);
    }
}

function hideLoading() {
    if (window.AdminJS && window.AdminJS.hideLoading) {
        window.AdminJS.hideLoading();
    }
}