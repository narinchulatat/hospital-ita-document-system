// Admin Panel JavaScript - TailwindCSS Version

document.addEventListener('DOMContentLoaded', function() {
    // Initialize admin features
    initializeAdmin();
    
    // Sidebar toggle functionality
    initializeSidebar();
    
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
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-loading')) {
            const btn = e.target;
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>กำลังดำเนินการ...';
            
            // Re-enable after 10 seconds (fallback)
            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }, 10000);
        }
    });
    
    // Auto-resize textareas
    const autoResizeTextareas = document.querySelectorAll('textarea[data-auto-resize]');
    autoResizeTextareas.forEach(function(textarea) {
        textarea.style.height = textarea.scrollHeight + 'px';
        textarea.style.overflowY = 'hidden';
        
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    });
}

/**
 * Initialize sidebar functionality
 */
function initializeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');
    const sidebarToggle = document.getElementById('sidebarToggle');
    
    if (!sidebar || !mainContent || !sidebarToggle) return;
    
    // Toggle sidebar
    sidebarToggle.addEventListener('click', function() {
        const isCollapsed = sidebar.classList.contains('sidebar-collapsed');
        
        if (isCollapsed) {
            sidebar.classList.remove('sidebar-collapsed', 'w-18');
            sidebar.classList.add('w-64');
            mainContent.classList.remove('main-content-expanded');
        } else {
            sidebar.classList.add('sidebar-collapsed', 'w-18');
            sidebar.classList.remove('w-64');
            mainContent.classList.add('main-content-expanded');
        }
        
        // Toggle text visibility
        const textElements = sidebar.querySelectorAll('.nav-text, .brand-text');
        textElements.forEach(el => {
            if (sidebar.classList.contains('sidebar-collapsed')) {
                el.classList.add('hidden');
            } else {
                el.classList.remove('hidden');
            }
        });
        
        // Save state to localStorage
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('sidebar-collapsed'));
    });
    
    // Restore sidebar state
    const savedState = localStorage.getItem('sidebarCollapsed');
    if (savedState === 'true') {
        sidebar.classList.add('sidebar-collapsed', 'w-18');
        sidebar.classList.remove('w-64');
        mainContent.classList.add('main-content-expanded');
        
        const textElements = sidebar.querySelectorAll('.nav-text, .brand-text');
        textElements.forEach(el => el.classList.add('hidden'));
    }
    
    // Mobile sidebar handling
    if (window.innerWidth <= 768) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            
            // Add/remove overlay
            let overlay = document.querySelector('.sidebar-overlay');
            if (sidebar.classList.contains('show') && !overlay) {
                overlay = document.createElement('div');
                overlay.className = 'sidebar-overlay fixed inset-0 bg-black bg-opacity-50 z-40';
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    this.remove();
                });
                document.body.appendChild(overlay);
            } else if (overlay) {
                overlay.remove();
            }
        });
    }
    
    // Submenu toggle
    const submenuToggles = document.querySelectorAll('.nav-link[data-toggle="collapse"]');
    submenuToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.dataset.target);
            
            if (target) {
                // Close other submenus
                const otherSubmenus = document.querySelectorAll('.submenu.show');
                otherSubmenus.forEach(function(submenu) {
                    if (submenu !== target) {
                        submenu.classList.remove('show');
                    }
                });
                
                // Toggle current submenu
                target.classList.toggle('show');
            }
        });
    });
}

/**
 * Initialize AJAX setup
 */
function initializeAjax() {
    // Set default AJAX options if jQuery is available
    if (typeof $ !== 'undefined') {
        $.ajaxSetup({
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            beforeSend: function(xhr, settings) {
                // Add CSRF token if available
                const token = document.querySelector('meta[name="csrf-token"]');
                if (token) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', token.getAttribute('content'));
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
}

/**
 * Initialize delete confirmation
 */
function initializeDeleteConfirmation() {
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-delete') || e.target.classList.contains('delete-btn') || e.target.closest('.btn-delete')) {
            e.preventDefault();
            
            const btn = e.target.closest('.btn-delete') || e.target;
            const url = btn.getAttribute('href') || btn.dataset.url;
            const title = btn.dataset.title || 'ยืนยันการลบ';
            const text = btn.dataset.text || 'คุณแน่ใจหรือไม่ที่จะลบรายการนี้? การดำเนินการนี้ไม่สามารถยกเลิกได้';
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: title,
                    text: text,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
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
                        if (btn.classList.contains('ajax-delete')) {
                            performAjaxDelete(url);
                        } else {
                            window.location.href = url;
                        }
                    }
                });
            } else {
                // Fallback if SweetAlert2 is not available
                if (confirm(text)) {
                    window.location.href = url;
                }
            }
        }
    });
}

/**
 * Perform AJAX delete
 */
function performAjaxDelete(url) {
    if (typeof $ !== 'undefined') {
        $.ajax({
            url: url,
            method: 'POST',
            data: {
                _method: 'DELETE',
                csrf_token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            success: function(response) {
                if (typeof Swal !== 'undefined') {
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
                } else {
                    location.reload();
                }
            },
            error: function(xhr) {
                let message = 'เกิดข้อผิดพลาดในการลบ';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด!',
                        text: message,
                        icon: 'error'
                    });
                } else {
                    alert(message);
                }
            }
        });
    }
}

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    // HTML5 validation
    const forms = document.querySelectorAll('.needs-validation');
    
    forms.forEach(function(form) {
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
    const emailFields = document.querySelectorAll('input[data-validate="email"]');
    emailFields.forEach(function(field) {
        field.addEventListener('blur', function() {
            const email = this.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                this.classList.add('border-red-500');
                showFieldError(this, 'รูปแบบอีเมลไม่ถูกต้อง');
            } else {
                this.classList.remove('border-red-500');
                hideFieldError(this);
            }
        });
    });
    
    const phoneFields = document.querySelectorAll('input[data-validate="phone"]');
    phoneFields.forEach(function(field) {
        field.addEventListener('blur', function() {
            const phone = this.value;
            const phoneRegex = /^[0-9]{9,10}$/;
            
            if (phone && !phoneRegex.test(phone)) {
                this.classList.add('border-red-500');
                showFieldError(this, 'หมายเลขโทรศัพท์ไม่ถูกต้อง');
            } else {
                this.classList.remove('border-red-500');
                hideFieldError(this);
            }
        });
    });
    
    // Password confirmation
    const confirmFields = document.querySelectorAll('input[data-confirm]');
    confirmFields.forEach(function(field) {
        field.addEventListener('blur', function() {
            const password = document.querySelector(this.dataset.confirm)?.value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.classList.add('border-red-500');
                showFieldError(this, 'รหัสผ่านไม่ตรงกัน');
            } else {
                this.classList.remove('border-red-500');
                hideFieldError(this);
            }
        });
    });
}

/**
 * Show field error message
 */
function showFieldError(field, message) {
    hideFieldError(field);
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error text-red-500 text-sm mt-1';
    errorDiv.textContent = message;
    field.parentNode.appendChild(errorDiv);
}

/**
 * Hide field error message
 */
function hideFieldError(field) {
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
}

/**
 * Initialize auto-hide alerts
 */
function initializeAutoHideAlerts() {
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert-auto-dismiss');
        alerts.forEach(function(alert) {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s ease';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);
}

/**
 * Show alert message
 */
function showAlert(message, type = 'info', duration = 5000) {
    const alertColors = {
        'success': 'bg-green-50 border-green-400 text-green-700',
        'error': 'bg-red-50 border-red-400 text-red-700',
        'danger': 'bg-red-50 border-red-400 text-red-700',
        'warning': 'bg-yellow-50 border-yellow-400 text-yellow-700',
        'info': 'bg-blue-50 border-blue-400 text-blue-700'
    };
    
    const iconClasses = {
        'success': 'fa-check-circle text-green-400',
        'error': 'fa-exclamation-triangle text-red-400',
        'danger': 'fa-exclamation-triangle text-red-400',
        'warning': 'fa-exclamation-triangle text-yellow-400',
        'info': 'fa-info-circle text-blue-400'
    };
    
    const alertClass = alertColors[type] || alertColors['info'];
    const iconClass = iconClasses[type] || iconClasses['info'];
    
    const alertHtml = `
        <div class="alert-dismissible border-l-4 p-4 mb-4 rounded ${alertClass}">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas ${iconClass}"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm">${message}</p>
                </div>
                <div class="ml-auto pl-3">
                    <button class="inline-flex text-gray-400 hover:text-gray-600" onclick="this.parentElement.parentElement.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert-dismissible');
    existingAlerts.forEach(alert => alert.remove());
    
    // Add new alert
    const container = document.querySelector('.p-6') || document.body;
    container.insertAdjacentHTML('afterbegin', alertHtml);
    
    // Auto-hide after duration
    if (duration > 0) {
        setTimeout(() => {
            const alert = document.querySelector('.alert-dismissible');
            if (alert) {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease';
                setTimeout(() => alert.remove(), 500);
            }
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
 * Show loading overlay
 */
function showLoading(message = 'กำลังโหลด...') {
    let loadingOverlay = document.getElementById('loadingOverlay');
    if (!loadingOverlay) {
        const overlay = `
            <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white p-6 rounded-lg text-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-4"></div>
                    <div class="text-gray-700">${message}</div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', overlay);
    } else {
        loadingOverlay.style.display = 'flex';
    }
}

/**
 * Hide loading overlay
 */
function hideLoading() {
    const loadingOverlay = document.getElementById('loadingOverlay');
    if (loadingOverlay) {
        loadingOverlay.style.display = 'none';
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
 * Initialize drag and drop for file upload
 */
function initializeDragAndDrop(element, callback) {
    const el = document.querySelector(element);
    if (!el) return;
    
    el.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('border-blue-500', 'bg-blue-50');
    });
    
    el.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('border-blue-500', 'bg-blue-50');
    });
    
    el.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('border-blue-500', 'bg-blue-50');
        
        const files = e.dataTransfer.files;
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
    initializeDragAndDrop
};