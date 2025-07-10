// Admin Panel JavaScript

$(document).ready(function() {
    // Initialize admin features
    initializeAdmin();
    
    // Sidebar toggle functionality
    initializeSidebar();
    
    // Initialize tooltips and popovers
    initializeBootstrapComponents();
    
    // Initialize AJAX setup
    initializeAjax();
    
    // Initialize common features
    initializeDeleteConfirmation();
    initializeFormValidation();
    initializeAutoHideAlerts();
});

/**
 * Initialize admin features
 */
function initializeAdmin() {
    // Add loading state to buttons
    $('.btn-loading').on('click', function() {
        const $btn = $(this);
        const originalText = $btn.html();
        
        $btn.prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin me-2"></i>กำลังดำเนินการ...');
        
        // Re-enable after 10 seconds (fallback)
        setTimeout(() => {
            $btn.prop('disabled', false).html(originalText);
        }, 10000);
    });
    
    // Initialize Select2
    if ($.fn.select2) {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
        
        $('.select2-multiple').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'เลือกรายการ...',
            allowClear: true
        });
    }
    
    // Initialize date pickers
    if ($.fn.datepicker) {
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true,
            language: 'th'
        });
    }
    
    // Auto-resize textareas
    $('textarea[data-auto-resize]').each(function() {
        this.setAttribute('style', 'height:' + (this.scrollHeight) + 'px;overflow-y:hidden;');
    }).on('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
}

/**
 * Initialize sidebar functionality
 */
function initializeSidebar() {
    const sidebar = $('#sidebar');
    const mainContent = $('#main-content');
    const sidebarToggle = $('#sidebarToggle');
    
    // Toggle sidebar
    sidebarToggle.on('click', function() {
        sidebar.toggleClass('collapsed');
        mainContent.toggleClass('expanded');
        
        // Save state to localStorage
        const isCollapsed = sidebar.hasClass('collapsed');
        localStorage.setItem('sidebarCollapsed', isCollapsed);
    });
    
    // Restore sidebar state
    const savedState = localStorage.getItem('sidebarCollapsed');
    if (savedState === 'true') {
        sidebar.addClass('collapsed');
        mainContent.addClass('expanded');
    }
    
    // Mobile sidebar overlay
    if (window.innerWidth <= 768) {
        sidebarToggle.on('click', function() {
            sidebar.toggleClass('show');
            
            // Add overlay
            if (sidebar.hasClass('show')) {
                $('<div class="sidebar-overlay"></div>')
                    .appendTo('body')
                    .on('click', function() {
                        sidebar.removeClass('show');
                        $(this).remove();
                    });
            } else {
                $('.sidebar-overlay').remove();
            }
        });
    }
    
    // Submenu toggle
    $('.nav-link[data-bs-toggle="collapse"]').on('click', function(e) {
        e.preventDefault();
        const target = $($(this).data('bs-target'));
        
        // Close other submenus
        $('.submenu.show').not(target).removeClass('show');
        
        // Toggle current submenu
        target.toggleClass('show');
    });
}

/**
 * Initialize Bootstrap components
 */
function initializeBootstrapComponents() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    const popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Initialize modals with backdrop options
    $('.modal').on('show.bs.modal', function() {
        $(this).find('.modal-body').scrollTop(0);
    });
}

/**
 * Initialize AJAX setup
 */
function initializeAjax() {
    // Set default AJAX options
    $.ajaxSetup({
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        beforeSend: function(xhr, settings) {
            // Add CSRF token if available
            const token = $('meta[name="csrf-token"]').attr('content');
            if (token) {
                xhr.setRequestHeader('X-CSRF-TOKEN', token);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            
            if (xhr.status === 401) {
                window.location.href = '/admin/login.php';
            } else if (xhr.status === 403) {
                showAlert('คุณไม่มีสิทธิ์ในการดำเนินการนี้', 'error');
            } else if (xhr.status === 500) {
                showAlert('เกิดข้อผิดพลาดเซิร์ฟเวอร์', 'error');
            } else {
                showAlert('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
            }
        }
    });
}

/**
 * Initialize delete confirmation
 */
function initializeDeleteConfirmation() {
    $(document).on('click', '.btn-delete, .delete-btn', function(e) {
        e.preventDefault();
        
        const url = $(this).attr('href') || $(this).data('url');
        const title = $(this).data('title') || 'ยืนยันการลบ';
        const text = $(this).data('text') || 'คุณแน่ใจหรือไม่ที่จะลบรายการนี้? การดำเนินการนี้ไม่สามารถยกเลิกได้';
        
        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'ใช่, ลบเลย!',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'กำลังลบ...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Perform delete action
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
 * Perform AJAX delete
 */
function performAjaxDelete(url) {
    $.ajax({
        url: url,
        method: 'POST',
        data: {
            _method: 'DELETE',
            csrf_token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            Swal.fire({
                title: 'ลบเรียบร้อย!',
                text: 'รายการถูกลบเรียบร้อยแล้ว',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                if (response.redirect) {
                    window.location.href = response.redirect;
                } else {
                    location.reload();
                }
            });
        },
        error: function(xhr) {
            let message = 'เกิดข้อผิดพลาดในการลบ';
            
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            
            Swal.fire({
                title: 'เกิดข้อผิดพลาด!',
                text: message,
                icon: 'error'
            });
        }
    });
}

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    // Bootstrap validation
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                // Focus first invalid field
                const firstInvalidField = form.querySelector(':invalid');
                if (firstInvalidField) {
                    firstInvalidField.focus();
                }
            }
            
            form.classList.add('was-validated');
        }, false);
    });
    
    // Custom validation rules
    $('input[data-validate="email"]').on('blur', function() {
        const email = $(this).val();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && !emailRegex.test(email)) {
            $(this).addClass('is-invalid');
            $(this).siblings('.invalid-feedback').text('รูปแบบอีเมลไม่ถูกต้อง');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    $('input[data-validate="phone"]').on('blur', function() {
        const phone = $(this).val();
        const phoneRegex = /^[0-9]{9,10}$/;
        
        if (phone && !phoneRegex.test(phone)) {
            $(this).addClass('is-invalid');
            $(this).siblings('.invalid-feedback').text('หมายเลขโทรศัพท์ไม่ถูกต้อง');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    // Password confirmation
    $('input[data-confirm]').on('blur', function() {
        const password = $($(this).data('confirm')).val();
        const confirmPassword = $(this).val();
        
        if (confirmPassword && password !== confirmPassword) {
            $(this).addClass('is-invalid');
            $(this).siblings('.invalid-feedback').text('รหัสผ่านไม่ตรงกัน');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
}

/**
 * Initialize auto-hide alerts
 */
function initializeAutoHideAlerts() {
    setTimeout(function() {
        $('.alert-auto-dismiss').fadeOut('slow');
    }, 5000);
}

/**
 * Show alert message
 */
function showAlert(message, type = 'info', duration = 5000) {
    const alertClass = type === 'error' ? 'danger' : type;
    const iconClass = type === 'success' ? 'check-circle' : 
                     type === 'error' || type === 'danger' ? 'exclamation-triangle' : 
                     type === 'warning' ? 'exclamation-triangle' : 'info-circle';
    
    const alertHtml = `
        <div class="alert alert-${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas fa-${iconClass} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Remove existing alerts
    $('.alert-dismissible').remove();
    
    // Add new alert
    $('.container-fluid').prepend(alertHtml);
    
    // Auto-hide after duration
    if (duration > 0) {
        setTimeout(() => {
            $('.alert-dismissible').fadeOut('slow');
        }, duration);
    }
}

/**
 * Format number with thousand separators
 */
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

/**
 * Format file size
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
 * Debounce function
 */
function debounce(func, wait, immediate) {
    let timeout;
    return function executedFunction() {
        const context = this;
        const args = arguments;
        
        const later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        
        const callNow = immediate && !timeout;
        
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        
        if (callNow) func.apply(context, args);
    };
}

/**
 * Copy text to clipboard
 */
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showAlert('คัดลอกไปยัง clipboard แล้ว', 'success', 2000);
        });
    } else {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showAlert('คัดลอกไปยัง clipboard แล้ว', 'success', 2000);
    }
}

/**
 * Show loading overlay
 */
function showLoading(message = 'กำลังโหลด...') {
    if ($('#loadingOverlay').length === 0) {
        const overlay = `
            <div id="loadingOverlay" class="loading-overlay">
                <div class="loading-content">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="loading-text mt-3">${message}</div>
                </div>
            </div>
        `;
        $('body').append(overlay);
    }
    $('#loadingOverlay').show();
}

/**
 * Hide loading overlay
 */
function hideLoading() {
    $('#loadingOverlay').hide();
}

/**
 * Validate form data
 */
function validateForm(formData) {
    const errors = [];
    
    // Add custom validation logic here
    
    return errors;
}

/**
 * Generate random string
 */
function generateRandomString(length = 8) {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let result = '';
    for (let i = 0; i < length; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
}

/**
 * Format Thai date
 */
function formatThaiDate(dateString, showTime = false) {
    const date = new Date(dateString);
    const thaiMonths = [
        'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
        'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
    ];
    
    const day = date.getDate();
    const month = thaiMonths[date.getMonth()];
    const year = date.getFullYear() + 543;
    
    let formatted = `${day} ${month} ${year}`;
    
    if (showTime) {
        const hours = date.getHours().toString().padStart(2, '0');
        const minutes = date.getMinutes().toString().padStart(2, '0');
        formatted += ` ${hours}:${minutes} น.`;
    }
    
    return formatted;
}

/**
 * Initialize drag and drop for file upload
 */
function initializeDragAndDrop(element, callback) {
    const $element = $(element);
    
    $element.on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('drag-over');
    });
    
    $element.on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-over');
    });
    
    $element.on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-over');
        
        const files = e.originalEvent.dataTransfer.files;
        if (callback && typeof callback === 'function') {
            callback(files);
        }
    });
}

// Export functions for global use
window.AdminJS = {
    showAlert,
    showLoading,
    hideLoading,
    formatNumber,
    formatFileSize,
    formatThaiDate,
    copyToClipboard,
    debounce,
    generateRandomString,
    validateForm,
    initializeDragAndDrop
};