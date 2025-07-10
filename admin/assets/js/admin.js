// Admin Panel JavaScript - TailwindCSS Compatible

$(document).ready(function() {
    // Initialize admin features
    initializeAdmin();
    
    // Initialize common features
    initializeDeleteConfirmation();
    initializeFormValidation();
    initializeAutoHideAlerts();
    initializeTooltips();
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
            .html('<i class="fas fa-spinner fa-spin mr-2"></i>กำลังดำเนินการ...');
        
        // Re-enable after 10 seconds (fallback)
        setTimeout(() => {
            $btn.prop('disabled', false).html(originalText);
        }, 10000);
    });
    
    // Initialize Select2 with TailwindCSS styling
    if ($.fn.select2) {
        $('.select2').select2({
            width: '100%',
            dropdownParent: $('body'),
            language: {
                noResults: function() {
                    return "ไม่พบข้อมูล";
                },
                searching: function() {
                    return "กำลังค้นหา...";
                }
            }
        });
        
        $('.select2-multiple').select2({
            width: '100%',
            placeholder: 'เลือกรายการ...',
            allowClear: true,
            dropdownParent: $('body'),
            language: {
                noResults: function() {
                    return "ไม่พบข้อมูล";
                },
                searching: function() {
                    return "กำลังค้นหา...";
                }
            }
        });
    }
    
    // Auto-resize textareas
    $('textarea[data-auto-resize]').each(function() {
        this.setAttribute('style', 'height:' + (this.scrollHeight) + 'px;overflow-y:hidden;');
    }).on('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
    
    // Toggle password visibility
    $('.toggle-password').on('click', function() {
        const target = $(this).data('target');
        const $input = $(target);
        const $icon = $(this).find('i');
        
        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            $input.attr('type', 'password');
            $icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
    
    // Initialize drag and drop for file uploads
    initializeDragAndDrop();
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
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'ใช่, ลบเลย!',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true,
            customClass: {
                popup: 'font-sans'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'กำลังลบ...',
                    allowOutsideClick: false,
                    customClass: {
                        popup: 'font-sans'
                    },
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
                showConfirmButton: false,
                customClass: {
                    popup: 'font-sans'
                }
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
                icon: 'error',
                customClass: {
                    popup: 'font-sans'
                }
            });
        }
    });
}

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    // TailwindCSS form validation
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
                    firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    
                    // Add error styling
                    firstInvalidField.classList.add('border-red-500', 'ring-red-500');
                    firstInvalidField.classList.remove('border-gray-300');
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
            $(this).addClass('border-red-500 ring-red-500').removeClass('border-gray-300');
            showFieldError($(this), 'รูปแบบอีเมลไม่ถูกต้อง');
        } else {
            $(this).removeClass('border-red-500 ring-red-500').addClass('border-gray-300');
            hideFieldError($(this));
        }
    });
    
    $('input[data-validate="phone"]').on('blur', function() {
        const phone = $(this).val();
        const phoneRegex = /^[0-9]{9,10}$/;
        
        if (phone && !phoneRegex.test(phone)) {
            $(this).addClass('border-red-500 ring-red-500').removeClass('border-gray-300');
            showFieldError($(this), 'หมายเลขโทรศัพท์ไม่ถูกต้อง');
        } else {
            $(this).removeClass('border-red-500 ring-red-500').addClass('border-gray-300');
            hideFieldError($(this));
        }
    });
    
    // Password confirmation
    $('input[data-confirm]').on('blur', function() {
        const password = $($(this).data('confirm')).val();
        const confirmPassword = $(this).val();
        
        if (confirmPassword && password !== confirmPassword) {
            $(this).addClass('border-red-500 ring-red-500').removeClass('border-gray-300');
            showFieldError($(this), 'รหัสผ่านไม่ตรงกัน');
        } else {
            $(this).removeClass('border-red-500 ring-red-500').addClass('border-gray-300');
            hideFieldError($(this));
        }
    });
}

/**
 * Show field error message
 */
function showFieldError($field, message) {
    hideFieldError($field);
    const errorDiv = $('<div class="text-red-500 text-sm mt-1 field-error">' + message + '</div>');
    $field.after(errorDiv);
}

/**
 * Hide field error message
 */
function hideFieldError($field) {
    $field.siblings('.field-error').remove();
}

/**
 * Initialize auto-hide alerts
 */
function initializeAutoHideAlerts() {
    setTimeout(function() {
        $('.alert-auto-dismiss').each(function() {
            $(this).fadeOut('slow');
        });
    }, 5000);
}

/**
 * Initialize tooltips for TailwindCSS
 */
function initializeTooltips() {
    $('[data-tooltip]').hover(
        function() {
            const tooltipText = $(this).data('tooltip');
            const tooltip = $('<div class="absolute z-50 px-2 py-1 text-sm text-white bg-gray-800 rounded shadow-lg pointer-events-none" id="tooltip">' + tooltipText + '</div>');
            $('body').append(tooltip);
            
            const position = $(this).offset();
            const elementWidth = $(this).outerWidth();
            const elementHeight = $(this).outerHeight();
            const tooltipWidth = tooltip.outerWidth();
            const tooltipHeight = tooltip.outerHeight();
            
            tooltip.css({
                top: position.top - tooltipHeight - 5,
                left: position.left + (elementWidth / 2) - (tooltipWidth / 2)
            });
        },
        function() {
            $('#tooltip').remove();
        }
    );
}

/**
 * Show alert message with TailwindCSS
 */
function showAlert(message, type = 'info', duration = 5000) {
    const alertTypes = {
        'success': 'bg-green-100 border-green-400 text-green-700',
        'error': 'bg-red-100 border-red-400 text-red-700',
        'warning': 'bg-yellow-100 border-yellow-400 text-yellow-700',
        'info': 'bg-blue-100 border-blue-400 text-blue-700'
    };
    
    const iconTypes = {
        'success': 'fa-check-circle',
        'error': 'fa-exclamation-triangle',
        'warning': 'fa-exclamation-triangle',
        'info': 'fa-info-circle'
    };
    
    const alertClass = alertTypes[type] || alertTypes['info'];
    const iconClass = iconTypes[type] || iconTypes['info'];
    
    const alertHtml = `
        <div class="border px-4 py-3 rounded-lg mb-4 flex items-center ${alertClass}" role="alert">
            <i class="fas ${iconClass} mr-2"></i>
            <span>${message}</span>
            <button class="ml-auto pl-3" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    // Remove existing alerts
    $('.alert').remove();
    
    // Add new alert to top of main content
    $('.main-content .p-6').prepend(alertHtml);
    
    // Auto-hide after duration
    if (duration > 0) {
        setTimeout(() => {
            $('.alert').fadeOut('slow');
        }, duration);
    }
}

/**
 * Initialize drag and drop for file upload
 */
function initializeDragAndDrop() {
    $('.drag-drop-area').on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('border-blue-500 bg-blue-50');
    });
    
    $('.drag-drop-area').on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('border-blue-500 bg-blue-50');
    });
    
    $('.drag-drop-area').on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('border-blue-500 bg-blue-50');
        
        const files = e.originalEvent.dataTransfer.files;
        handleFileUpload(files, $(this));
    });
}

/**
 * Handle file upload
 */
function handleFileUpload(files, $container) {
    // This function should be customized based on specific upload requirements
    console.log('Files to upload:', files);
    
    // Example implementation
    Array.from(files).forEach(file => {
        if (file.size > 10 * 1024 * 1024) { // 10MB limit
            showAlert('ไฟล์ ' + file.name + ' มีขนาดใหญ่เกินไป (สูงสุด 10MB)', 'error');
            return;
        }
        
        // Add file to upload queue or process immediately
        showAlert('เพิ่มไฟล์ ' + file.name + ' แล้ว', 'success', 2000);
    });
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

// Export functions for global use
window.AdminJS = {
    showAlert,
    formatNumber,
    formatFileSize,
    formatThaiDate,
    copyToClipboard,
    debounce,
    showFieldError,
    hideFieldError,
    handleFileUpload
};