/**
 * Main JavaScript Application
 * Hospital ITA Document System
 */

class HospitalApp {
    constructor() {
        this.config = window.APP_CONFIG || {};
        this.init();
    }

    init() {
        this.setupCSRF();
        this.setupAjax();
        this.setupFormValidation();
        this.setupFileUpload();
        this.setupDataTables();
        this.setupNotifications();
        this.setupTooltips();
    }

    // CSRF Token Management
    setupCSRF() {
        // Add CSRF token to all AJAX requests
        const self = this;
        if (typeof $ !== 'undefined') {
            $.ajaxSetup({
                beforeSend: function(xhr, settings) {
                    if (!/^(GET|HEAD|OPTIONS|TRACE)$/i.test(settings.type) && !this.crossDomain) {
                        xhr.setRequestHeader("X-CSRFToken", self.config.CSRF_TOKEN);
                    }
                }
            });
        }
    }

    // AJAX Setup
    setupAjax() {
        const self = this;
        
        // Global AJAX error handler
        if (typeof $ !== 'undefined') {
            $(document).ajaxError(function(event, xhr, settings, error) {
                console.error('AJAX Error:', error);
                
                if (xhr.status === 403) {
                    self.showError('ไม่มีสิทธิ์เข้าถึง');
                } else if (xhr.status === 500) {
                    self.showError('เกิดข้อผิดพลาดในระบบ กรุณาลองใหม่อีกครั้ง');
                } else if (xhr.status === 0) {
                    self.showError('ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้');
                }
            });
        }
    }

    // Form Validation
    setupFormValidation() {
        // Thai-specific validation
        this.addThaiValidation();
        
        // Real-time validation
        document.querySelectorAll('input, textarea, select').forEach(input => {
            input.addEventListener('blur', (e) => {
                this.validateField(e.target);
            });
        });
    }

    addThaiValidation() {
        // Add custom validation methods if using a validation library
        if (typeof $.validator !== 'undefined') {
            $.validator.addMethod('thaiText', function(value, element) {
                return this.optional(element) || /^[\u0E00-\u0E7Fa-zA-Z0-9\s\.,\-_()]+$/.test(value);
            }, 'กรุณากรอกข้อมูลเป็นภาษาไทยหรือภาษาอังกฤษเท่านั้น');

            $.validator.addMethod('thaiPhone', function(value, element) {
                return this.optional(element) || /^[0-9\-\s\(\)]{8,15}$/.test(value);
            }, 'กรุณากรอกหมายเลขโทรศัพท์ให้ถูกต้อง');
        }
    }

    validateField(field) {
        const value = field.value.trim();
        const type = field.type;
        let isValid = true;
        let message = '';

        // Remove previous error styling
        this.clearFieldError(field);

        // Required field validation
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            message = 'กรุณากรอกข้อมูลในช่องนี้';
        }

        // Email validation
        if (type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                message = 'กรุณากรอกอีเมลให้ถูกต้อง';
            }
        }

        // Password validation
        if (type === 'password' && value && value.length < 6) {
            isValid = false;
            message = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
        }

        if (!isValid) {
            this.showFieldError(field, message);
        }

        return isValid;
    }

    showFieldError(field, message) {
        field.classList.add('border-red-500');
        
        // Remove existing error message
        const existingError = field.parentElement.querySelector('.form-error');
        if (existingError) {
            existingError.remove();
        }

        // Add error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'form-error';
        errorDiv.textContent = message;
        field.parentElement.appendChild(errorDiv);
    }

    clearFieldError(field) {
        field.classList.remove('border-red-500');
        const errorDiv = field.parentElement.querySelector('.form-error');
        if (errorDiv) {
            errorDiv.remove();
        }
    }

    // File Upload
    setupFileUpload() {
        const self = this;
        
        // Drag and drop functionality
        document.querySelectorAll('.upload-area').forEach(area => {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                area.addEventListener(eventName, this.preventDefaults, false);
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                area.addEventListener(eventName, () => area.classList.add('drag-over'), false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                area.addEventListener(eventName, () => area.classList.remove('drag-over'), false);
            });

            area.addEventListener('drop', (e) => {
                const files = e.dataTransfer.files;
                self.handleFiles(files, area);
            }, false);
        });

        // File input change
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', (e) => {
                self.handleFiles(e.target.files, e.target.closest('.upload-area'));
            });
        });
    }

    preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    handleFiles(files, uploadArea) {
        const allowedTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
        const maxSize = 50 * 1024 * 1024; // 50MB

        Array.from(files).forEach(file => {
            const extension = file.name.split('.').pop().toLowerCase();
            
            if (!allowedTypes.includes(extension)) {
                this.showError(`ไฟล์ ${file.name} ไม่ได้รับอนุญาต`);
                return;
            }

            if (file.size > maxSize) {
                this.showError(`ไฟล์ ${file.name} มีขนาดใหญ่เกิน 50MB`);
                return;
            }

            this.previewFile(file, uploadArea);
        });
    }

    previewFile(file, uploadArea) {
        const preview = uploadArea.querySelector('.file-preview') || this.createFilePreview(uploadArea);
        
        const fileItem = document.createElement('div');
        fileItem.className = 'flex items-center justify-between p-3 bg-gray-50 rounded border mb-2';
        
        fileItem.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-file text-gray-400 mr-3"></i>
                <div>
                    <div class="text-sm font-medium text-gray-900">${file.name}</div>
                    <div class="text-xs text-gray-500">${this.formatFileSize(file.size)}</div>
                </div>
            </div>
            <button type="button" class="text-red-500 hover:text-red-700">
                <i class="fas fa-times"></i>
            </button>
        `;

        fileItem.querySelector('button').addEventListener('click', () => {
            fileItem.remove();
        });

        preview.appendChild(fileItem);
    }

    createFilePreview(uploadArea) {
        const preview = document.createElement('div');
        preview.className = 'file-preview mt-4';
        uploadArea.appendChild(preview);
        return preview;
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // DataTables Setup
    setupDataTables() {
        if (typeof $.fn.DataTable === 'undefined') return;

        // Thai language configuration
        const thaiLanguage = {
            "sProcessing": "กำลังดำเนินการ...",
            "sLengthMenu": "แสดง _MENU_ รายการ",
            "sZeroRecords": "ไม่พบข้อมูลที่ค้นหา",
            "sInfo": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
            "sInfoEmpty": "แสดง 0 ถึง 0 จาก 0 รายการ",
            "sInfoFiltered": "(กรองข้อมูล _MAX_ ทุกรายการ)",
            "sSearch": "ค้นหา:",
            "sUrl": "",
            "oPaginate": {
                "sFirst": "หน้าแรก",
                "sPrevious": "ก่อนหน้า",
                "sNext": "ถัดไป",
                "sLast": "หน้าสุดท้าย"
            }
        };

        // Apply to all tables with class 'datatable'
        $('.datatable').DataTable({
            language: thaiLanguage,
            responsive: true,
            pageLength: 20,
            order: [[0, 'desc']],
            columnDefs: [
                { orderable: false, targets: 'no-sort' }
            ]
        });
    }

    // Notifications
    setupNotifications() {
        // Auto-hide success messages
        setTimeout(() => {
            document.querySelectorAll('.alert-success').forEach(alert => {
                this.fadeOut(alert);
            });
        }, 5000);
    }

    // Tooltips
    setupTooltips() {
        // Simple tooltip implementation
        document.querySelectorAll('[data-tooltip]').forEach(element => {
            element.addEventListener('mouseenter', (e) => {
                this.showTooltip(e.target, e.target.dataset.tooltip);
            });

            element.addEventListener('mouseleave', () => {
                this.hideTooltip();
            });
        });
    }

    showTooltip(element, text) {
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip fixed bg-gray-800 text-white text-xs rounded py-1 px-2 z-50';
        tooltip.textContent = text;
        tooltip.id = 'tooltip';

        document.body.appendChild(tooltip);

        const rect = element.getBoundingClientRect();
        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
    }

    hideTooltip() {
        const tooltip = document.getElementById('tooltip');
        if (tooltip) {
            tooltip.remove();
        }
    }

    // Utility Methods
    showSuccess(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ!',
                text: message,
                confirmButtonColor: '#3b82f6',
                timer: 3000,
                timerProgressBar: true
            });
        } else {
            this.showAlert(message, 'success');
        }
    }

    showError(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด!',
                text: message,
                confirmButtonColor: '#3b82f6'
            });
        } else {
            this.showAlert(message, 'error');
        }
    }

    showConfirm(message, callback) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'ยืนยันการดำเนินการ?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed && callback) {
                    callback();
                }
            });
        } else {
            if (confirm(message) && callback) {
                callback();
            }
        }
    }

    showAlert(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `notification notification-${type} fade-in`;
        alertDiv.innerHTML = `
            <div class="p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-${this.getAlertIcon(type)} text-${this.getAlertColor(type)}-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-800">${message}</p>
                    </div>
                    <div class="ml-auto pl-3">
                        <button class="text-gray-400 hover:text-gray-600" onclick="this.parentElement.parentElement.parentElement.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(alertDiv);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentElement) {
                this.fadeOut(alertDiv);
            }
        }, 5000);
    }

    getAlertIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'times-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }

    getAlertColor(type) {
        const colors = {
            success: 'green',
            error: 'red',
            warning: 'yellow',
            info: 'blue'
        };
        return colors[type] || 'blue';
    }

    fadeOut(element) {
        element.style.opacity = '0';
        element.style.transition = 'opacity 0.3s ease';
        setTimeout(() => {
            if (element.parentElement) {
                element.remove();
            }
        }, 300);
    }

    // Loading Management
    showLoading() {
        const loading = document.getElementById('loading');
        if (loading) {
            loading.style.display = 'block';
        }
    }

    hideLoading() {
        const loading = document.getElementById('loading');
        if (loading) {
            loading.style.display = 'none';
        }
    }

    // AJAX Helper
    ajax(url, options = {}) {
        const defaults = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRFToken': this.config.CSRF_TOKEN
            }
        };

        const config = { ...defaults, ...options };

        this.showLoading();

        return fetch(url, config)
            .then(response => {
                this.hideLoading();
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .catch(error => {
                this.hideLoading();
                console.error('Fetch error:', error);
                this.showError('เกิดข้อผิดพลาดในการเชื่อมต่อ');
                throw error;
            });
    }
}

// Initialize application when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.HospitalApp = new HospitalApp();
});

// Global utility functions
function formatThaiDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('th-TH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function formatThaiDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('th-TH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}