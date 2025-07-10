/**
 * Tables JavaScript Functions
 * สำหรับจัดการ DataTables และตารางข้อมูล
 */

// ตัวแปรสำหรับเก็บ DataTable instances
let tableInstances = {};

$(document).ready(function() {
    // เริ่มต้น DataTables
    initializeDataTables();
    
    // เริ่มต้นฟังก์ชันตาราง
    initializeTableFeatures();
    
    // เริ่มต้น bulk actions
    initializeBulkActions();
});

/**
 * เริ่มต้น DataTables
 */
function initializeDataTables() {
    // กำหนดค่าเริ่มต้นสำหรับ DataTables
    $.extend($.fn.dataTable.defaults, {
        language: getThaiLanguage(),
        responsive: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "ทั้งหมด"]],
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        processing: true,
        drawCallback: function(settings) {
            // เพิ่ม TailwindCSS classes หลังจากวาดตาราง
            applyTailwindStylesToTable(this);
        }
    });
    
    // เริ่มต้น DataTables ทั้งหมด
    $('.data-table').each(function() {
        initializeDataTable(this);
    });
    
    // เริ่มต้น Advanced DataTables
    $('.advanced-table').each(function() {
        initializeAdvancedDataTable(this);
    });
}

/**
 * เริ่มต้น DataTable พื้นฐาน
 */
function initializeDataTable(table) {
    const $table = $(table);
    const tableId = $table.attr('id');
    
    if (!tableId) {
        console.warn('DataTable ต้องมี ID');
        return;
    }
    
    const options = {
        columnDefs: getColumnDefs($table),
        order: getDefaultOrder($table),
        buttons: getTableButtons($table),
        ...getCustomOptions($table)
    };
    
    // สร้าง DataTable
    const dataTable = $table.DataTable(options);
    
    // เก็บ instance
    tableInstances[tableId] = dataTable;
    
    // เพิ่ม event listeners
    addTableEventListeners(dataTable, $table);
    
    return dataTable;
}

/**
 * เริ่มต้น Advanced DataTable
 */
function initializeAdvancedDataTable(table) {
    const $table = $(table);
    const tableId = $table.attr('id');
    
    if (!tableId) {
        console.warn('Advanced DataTable ต้องมี ID');
        return;
    }
    
    const options = {
        serverSide: true,
        ajax: {
            url: $table.data('ajax-url'),
            type: 'POST',
            data: function(d) {
                // เพิ่มข้อมูลเพิ่มเติม
                d.filters = getTableFilters($table);
                d.csrf_token = $('meta[name="csrf-token"]').attr('content');
                return d;
            },
            error: function(xhr, error, thrown) {
                console.error('DataTable AJAX Error:', error, thrown);
                showAlert('เกิดข้อผิดพลาดในการโหลดข้อมูล', 'error');
            }
        },
        columnDefs: getColumnDefs($table),
        order: getDefaultOrder($table),
        buttons: getAdvancedButtons($table),
        select: {
            style: 'multi',
            selector: 'td:first-child input[type="checkbox"]'
        },
        ...getCustomOptions($table)
    };
    
    // สร้าง DataTable
    const dataTable = $table.DataTable(options);
    
    // เก็บ instance
    tableInstances[tableId] = dataTable;
    
    // เพิ่ม event listeners
    addAdvancedTableEventListeners(dataTable, $table);
    
    return dataTable;
}

/**
 * ได้รับการกำหนด columns
 */
function getColumnDefs($table) {
    const columnDefs = [];
    
    // หา columns ที่ไม่ต้องการเรียงลำดับ
    $table.find('thead th[data-orderable="false"]').each(function(index) {
        columnDefs.push({
            targets: index,
            orderable: false
        });
    });
    
    // หา columns ที่เป็น checkbox
    $table.find('thead th.checkbox-column').each(function(index) {
        columnDefs.push({
            targets: index,
            orderable: false,
            searchable: false,
            className: 'text-center',
            render: function(data, type, row) {
                return `<input type="checkbox" class="form-check-input row-checkbox" value="${row.id || row[0]}">`;
            }
        });
    });
    
    // หา columns ที่เป็น actions
    $table.find('thead th.actions-column').each(function(index) {
        columnDefs.push({
            targets: index,
            orderable: false,
            searchable: false,
            className: 'text-center',
            width: '150px'
        });
    });
    
    // หา columns ที่เป็นวันที่
    $table.find('thead th[data-type="date"]').each(function(index) {
        columnDefs.push({
            targets: index,
            render: function(data, type, row) {
                if (type === 'display' && data) {
                    return formatThaiDate(data);
                }
                return data;
            }
        });
    });
    
    // หา columns ที่เป็นตัวเลข
    $table.find('thead th[data-type="number"]').each(function(index) {
        columnDefs.push({
            targets: index,
            className: 'text-right',
            render: function(data, type, row) {
                if (type === 'display' && data) {
                    return formatNumber(data);
                }
                return data;
            }
        });
    });
    
    // หา columns ที่เป็น file size
    $table.find('thead th[data-type="filesize"]').each(function(index) {
        columnDefs.push({
            targets: index,
            className: 'text-right',
            render: function(data, type, row) {
                if (type === 'display' && data) {
                    return formatFileSize(data);
                }
                return data;
            }
        });
    });
    
    // หา columns ที่เป็น status
    $table.find('thead th[data-type="status"]').each(function(index) {
        columnDefs.push({
            targets: index,
            className: 'text-center',
            render: function(data, type, row) {
                if (type === 'display' && data) {
                    return getStatusBadge(data);
                }
                return data;
            }
        });
    });
    
    return columnDefs;
}

/**
 * ได้รับลำดับเริ่มต้น
 */
function getDefaultOrder($table) {
    const defaultOrder = $table.data('order');
    if (defaultOrder) {
        return defaultOrder;
    }
    
    // ค้นหา column ที่มี data-default-sort
    const sortColumn = $table.find('thead th[data-default-sort]');
    if (sortColumn.length) {
        const columnIndex = sortColumn.index();
        const sortDirection = sortColumn.data('default-sort') || 'asc';
        return [[columnIndex, sortDirection]];
    }
    
    // เริ่มต้นด้วย column แรก
    return [[0, 'asc']];
}

/**
 * ได้รับปุ่มต่างๆ สำหรับตาราง
 */
function getTableButtons($table) {
    const buttons = [];
    
    // ปุ่ม Export
    if ($table.data('export') !== false) {
        buttons.push(
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel mr-2"></i>ส่งออก Excel',
                className: 'bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm',
                exportOptions: {
                    columns: ':not(.actions-column):not(.checkbox-column)'
                }
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf mr-2"></i>ส่งออก PDF',
                className: 'bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm',
                exportOptions: {
                    columns: ':not(.actions-column):not(.checkbox-column)'
                }
            }
        );
    }
    
    // ปุ่ม Print
    if ($table.data('print') !== false) {
        buttons.push({
            extend: 'print',
            text: '<i class="fas fa-print mr-2"></i>พิมพ์',
            className: 'bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm',
            exportOptions: {
                columns: ':not(.actions-column):not(.checkbox-column)'
            }
        });
    }
    
    return buttons;
}

/**
 * ได้รับปุ่มขั้นสูงสำหรับตาราง
 */
function getAdvancedButtons($table) {
    const buttons = getTableButtons($table);
    
    // ปุ่ม Bulk Actions
    if ($table.data('bulk-actions') !== false) {
        buttons.unshift({
            text: '<i class="fas fa-tasks mr-2"></i>การดำเนินการหลายรายการ',
            className: 'bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm bulk-actions-btn',
            action: function(e, dt, node, config) {
                showBulkActionsModal(dt);
            }
        });
    }
    
    // ปุ่ม Refresh
    buttons.push({
        text: '<i class="fas fa-sync-alt mr-2"></i>รีเฟรช',
        className: 'bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm',
        action: function(e, dt, node, config) {
            dt.ajax.reload();
        }
    });
    
    return buttons;
}

/**
 * ได้รับตัวเลือกเพิ่มเติม
 */
function getCustomOptions($table) {
    const options = {};
    
    // Scroll options
    if ($table.data('scroll-x')) {
        options.scrollX = true;
    }
    
    if ($table.data('scroll-y')) {
        options.scrollY = $table.data('scroll-y');
    }
    
    // Fixed header
    if ($table.data('fixed-header') !== false) {
        options.fixedHeader = true;
    }
    
    // State saving
    if ($table.data('state-save') !== false) {
        options.stateSave = true;
        options.stateDuration = 60 * 60 * 24; // 1 day
    }
    
    return options;
}

/**
 * เพิ่ม event listeners ให้กับตาราง
 */
function addTableEventListeners(dataTable, $table) {
    // Row click event
    $table.on('click', 'tbody tr', function() {
        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
        } else {
            dataTable.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
        }
    });
    
    // Checkbox events
    $table.on('change', '.row-checkbox', function() {
        const row = $(this).closest('tr');
        if (this.checked) {
            row.addClass('selected');
        } else {
            row.removeClass('selected');
        }
        
        updateBulkActionsVisibility($table);
    });
    
    // Select all checkbox
    $table.on('change', '.select-all-checkbox', function() {
        const checked = this.checked;
        $table.find('.row-checkbox').prop('checked', checked).trigger('change');
    });
}

/**
 * เพิ่ม event listeners ขั้นสูงให้กับตาราง
 */
function addAdvancedTableEventListeners(dataTable, $table) {
    // เพิ่ม basic listeners
    addTableEventListeners(dataTable, $table);
    
    // Double click to view
    $table.on('dblclick', 'tbody tr', function() {
        const data = dataTable.row(this).data();
        if (data && data.id) {
            const viewUrl = $table.data('view-url');
            if (viewUrl) {
                window.location.href = viewUrl.replace(':id', data.id);
            }
        }
    });
    
    // Column filtering
    $table.find('thead th input[type="text"]').on('keyup change', function() {
        const columnIndex = $(this).closest('th').index();
        dataTable.column(columnIndex).search(this.value).draw();
    });
    
    // Advanced search
    $('#advanced-search-form').on('submit', function(e) {
        e.preventDefault();
        dataTable.ajax.reload();
    });
}

/**
 * เพิ่ม TailwindCSS styles ให้กับตาราง
 */
function applyTailwindStylesToTable(table) {
    const $table = $(table);
    
    // Table wrapper
    $table.closest('.dataTables_wrapper').addClass('bg-white rounded-lg shadow overflow-hidden');
    
    // Table header
    $table.find('thead').addClass('bg-gray-50');
    $table.find('thead th').addClass('px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider');
    
    // Table body
    $table.find('tbody tr').addClass('border-b border-gray-200 hover:bg-gray-50');
    $table.find('tbody td').addClass('px-6 py-4 whitespace-nowrap text-sm text-gray-900');
    
    // Pagination
    $('.dataTables_paginate .paginate_button').addClass('px-3 py-2 ml-1 text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50');
    $('.dataTables_paginate .paginate_button.current').addClass('bg-blue-500 text-white border-blue-500');
    
    // Length menu and search
    $('.dataTables_length select').addClass('form-select rounded-lg border-gray-300');
    $('.dataTables_filter input').addClass('form-input rounded-lg border-gray-300 ml-2');
    
    // Info text
    $('.dataTables_info').addClass('text-sm text-gray-700');
}

/**
 * ได้รับการตั้งค่าภาษาไทย
 */
function getThaiLanguage() {
    return {
        "sProcessing": "กำลังดำเนินการ...",
        "sLengthMenu": "แสดง _MENU_ รายการ",
        "sZeroRecords": "ไม่พบข้อมูลที่ตรงกับคำค้นหา",
        "sInfo": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
        "sInfoEmpty": "แสดง 0 ถึง 0 จาก 0 รายการ",
        "sInfoFiltered": "(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)",
        "sSearch": "ค้นหา:",
        "sLoadingRecords": "กำลังโหลดข้อมูล...",
        "oPaginate": {
            "sFirst": "หน้าแรก",
            "sPrevious": "ก่อนหน้า",
            "sNext": "ถัดไป",
            "sLast": "หน้าสุดท้าย"
        },
        "oAria": {
            "sSortAscending": ": เปิดใช้งานการเรียงลำดับจากน้อยไปมาก",
            "sSortDescending": ": เปิดใช้งานการเรียงลำดับจากมากไปน้อย"
        }
    };
}

/**
 * ได้รับ status badge
 */
function getStatusBadge(status) {
    const statusConfig = {
        'active': { class: 'bg-green-100 text-green-800', text: 'ใช้งาน' },
        'inactive': { class: 'bg-red-100 text-red-800', text: 'ไม่ใช้งาน' },
        'pending': { class: 'bg-yellow-100 text-yellow-800', text: 'รออนุมัติ' },
        'approved': { class: 'bg-green-100 text-green-800', text: 'อนุมัติแล้ว' },
        'rejected': { class: 'bg-red-100 text-red-800', text: 'ปฏิเสธ' },
        'draft': { class: 'bg-gray-100 text-gray-800', text: 'ร่าง' }
    };
    
    const config = statusConfig[status] || { class: 'bg-gray-100 text-gray-800', text: status };
    
    return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${config.class}">
                ${config.text}
            </span>`;
}

/**
 * ได้รับตัวกรองตาราง
 */
function getTableFilters($table) {
    const filters = {};
    
    // ค้นหาจาก form filters
    const filterForm = $table.closest('.table-container').find('.table-filters');
    if (filterForm.length) {
        filterForm.find('input, select').each(function() {
            const name = $(this).attr('name');
            const value = $(this).val();
            if (name && value) {
                filters[name] = value;
            }
        });
    }
    
    return filters;
}

/**
 * เริ่มต้นฟีเจอร์ตาราง
 */
function initializeTableFeatures() {
    // Column visibility toggle
    $('.column-visibility-toggle').on('change', function() {
        const tableId = $(this).data('table');
        const columnIndex = $(this).data('column');
        const table = tableInstances[tableId];
        
        if (table) {
            const column = table.column(columnIndex);
            column.visible(this.checked);
        }
    });
    
    // Table filters
    $('.table-filter').on('change', function() {
        const tableId = $(this).data('table');
        const table = tableInstances[tableId];
        
        if (table && table.ajax) {
            table.ajax.reload();
        }
    });
    
    // Quick search
    $('.table-quick-search').on('keyup', debounce(function() {
        const tableId = $(this).data('table');
        const table = tableInstances[tableId];
        
        if (table) {
            table.search(this.value).draw();
        }
    }, 300));
}

/**
 * เริ่มต้น bulk actions
 */
function initializeBulkActions() {
    // Bulk action form
    $('#bulk-action-form').on('submit', function(e) {
        e.preventDefault();
        
        const action = $('#bulk-action-select').val();
        const selectedIds = getSelectedRowIds();
        
        if (!action) {
            showAlert('กรุณาเลือกการดำเนินการ', 'warning');
            return;
        }
        
        if (selectedIds.length === 0) {
            showAlert('กรุณาเลือกรายการที่ต้องการดำเนินการ', 'warning');
            return;
        }
        
        performBulkAction(action, selectedIds);
    });
}

/**
 * แสดง bulk actions modal
 */
function showBulkActionsModal(table) {
    const selectedRows = table.rows('.selected').data();
    if (selectedRows.length === 0) {
        showAlert('กรุณาเลือกรายการที่ต้องการดำเนินการ', 'warning');
        return;
    }
    
    // สร้าง modal content
    const modalContent = `
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="bulk-actions-modal">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">การดำเนินการหลายรายการ</h3>
                    <p class="text-sm text-gray-600 mb-4">เลือกรายการ: ${selectedRows.length} รายการ</p>
                    
                    <form id="bulk-action-form">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">เลือกการดำเนินการ</label>
                            <select id="bulk-action-select" class="w-full rounded-lg border-gray-300" required>
                                <option value="">-- เลือกการดำเนินการ --</option>
                                <option value="delete">ลบรายการที่เลือก</option>
                                <option value="activate">เปิดใช้งาน</option>
                                <option value="deactivate">ปิดใช้งาน</option>
                                <option value="export">ส่งออกข้อมูล</option>
                            </select>
                        </div>
                        
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeBulkActionsModal()" 
                                    class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                                ยกเลิก
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                                ดำเนินการ
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    $('body').append(modalContent);
}

/**
 * ปิด bulk actions modal
 */
function closeBulkActionsModal() {
    $('#bulk-actions-modal').remove();
}

/**
 * ดำเนินการ bulk action
 */
function performBulkAction(action, selectedIds) {
    const confirmText = getBulkActionConfirmText(action, selectedIds.length);
    
    Swal.fire({
        title: 'ยืนยันการดำเนินการ',
        text: confirmText,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'ยืนยัน',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            // ส่งข้อมูลไปยัง server
            $.ajax({
                url: BASE_URL + '/admin/api/bulk-actions.php',
                method: 'POST',
                data: {
                    action: action,
                    ids: selectedIds,
                    csrf_token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showAlert(response.message, 'success');
                        
                        // รีโหลดตาราง
                        Object.values(tableInstances).forEach(table => {
                            if (table.ajax) {
                                table.ajax.reload();
                            } else {
                                table.draw();
                            }
                        });
                        
                        closeBulkActionsModal();
                    } else {
                        showAlert(response.message || 'เกิดข้อผิดพลาด', 'error');
                    }
                },
                error: function() {
                    showAlert('เกิดข้อผิดพลาดในการดำเนินการ', 'error');
                }
            });
        }
    });
}

/**
 * ได้รับข้อความยืนยันสำหรับ bulk action
 */
function getBulkActionConfirmText(action, count) {
    const texts = {
        'delete': `คุณแน่ใจหรือไม่ที่จะลบ ${count} รายการ? การดำเนินการนี้ไม่สามารถยกเลิกได้`,
        'activate': `คุณต้องการเปิดใช้งาน ${count} รายการหรือไม่?`,
        'deactivate': `คุณต้องการปิดใช้งาน ${count} รายการหรือไม่?`,
        'export': `คุณต้องการส่งออกข้อมูล ${count} รายการหรือไม่?`
    };
    
    return texts[action] || `คุณต้องการดำเนินการกับ ${count} รายการหรือไม่?`;
}

/**
 * ได้รับ ID ของแถวที่เลือก
 */
function getSelectedRowIds() {
    const ids = [];
    $('.row-checkbox:checked').each(function() {
        ids.push($(this).val());
    });
    return ids;
}

/**
 * อัปเดตการแสดงปุ่ม bulk actions
 */
function updateBulkActionsVisibility($table) {
    const selectedCount = $table.find('.row-checkbox:checked').length;
    const bulkActionsBtn = $('.bulk-actions-btn');
    
    if (selectedCount > 0) {
        bulkActionsBtn.removeClass('hidden').find('.selected-count').text(selectedCount);
    } else {
        bulkActionsBtn.addClass('hidden');
    }
}

/**
 * รีเซ็ตตาราง
 */
function resetTable(tableId) {
    const table = tableInstances[tableId];
    if (table) {
        table.search('').columns().search('').draw();
        table.$('.row-checkbox').prop('checked', false);
        table.$('tr.selected').removeClass('selected');
    }
}

/**
 * Export table functions
 */
window.Tables = {
    initialize: initializeDataTables,
    getInstance: (id) => tableInstances[id],
    reset: resetTable,
    refresh: (id) => {
        const table = tableInstances[id];
        if (table && table.ajax) {
            table.ajax.reload();
        }
    }
};