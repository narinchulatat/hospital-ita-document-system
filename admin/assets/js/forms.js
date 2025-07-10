/**
 * Forms JavaScript Functions
 * สำหรับจัดการฟอร์มและการ validate
 */

$(document).ready(function() {
    // เริ่มต้นฟอร์มฟีเจอร์
    initializeForms();
    
    // เริ่มต้น validation
    initializeValidation();
    
    // เริ่มต้น file uploads
    initializeFileUploads();
    
    // เริ่มต้น form enhancements
    initializeFormEnhancements();
});

/**
 * เริ่มต้นฟอร์มทั่วไป
 */
function initializeForms() {
    // เพิ่ม TailwindCSS classes ให้กับ form elements
    applyFormStyling();
    
    // เริ่มต้น Select2
    initializeSelect2();
    
    // เริ่มต้น date pickers
    initializeDatePickers();
    
    // เริ่มต้น text editors
    initializeTextEditors();
    
    // เริ่มต้น form submissions
    initializeFormSubmissions();
}

/**
 * เพิ่ม styling ให้กับ form elements
 */
function applyFormStyling() {
    // Input fields
    $('input[type="text"], input[type="email"], input[type="password"], input[type="number"], input[type="tel"], input[type="url"], textarea, select')
        .not('.no-style')
        .addClass('w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors');
    
    // File inputs
    $('input[type="file"]')
        .not('.no-style')
        .addClass('w-full px-3 py-2 border border-gray-300 rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100');
    
    // Checkboxes and radios
    $('input[type="checkbox"], input[type="radio"]')
        .not('.no-style')
        .addClass('text-blue-600 focus:ring-blue-500 border-gray-300 rounded');
    
    // Buttons
    $('.btn:not(.btn-styled)')
        .addClass('px-4 py-2 rounded-lg font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2')
        .addClass('btn-styled');
    
    // Primary buttons
    $('.btn-primary').addClass('bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500');
    
    // Secondary buttons
    $('.btn-secondary').addClass('bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500');
    
    // Success buttons
    $('.btn-success').addClass('bg-green-600 text-white hover:bg-green-700 focus:ring-green-500');
    
    // Warning buttons
    $('.btn-warning').addClass('bg-yellow-600 text-white hover:bg-yellow-700 focus:ring-yellow-500');
    
    // Danger buttons
    $('.btn-danger').addClass('bg-red-600 text-white hover:bg-red-700 focus:ring-red-500');
    
    // Outline buttons
    $('.btn-outline-primary').addClass('border border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white focus:ring-blue-500');
    $('.btn-outline-secondary').addClass('border border-gray-600 text-gray-600 hover:bg-gray-600 hover:text-white focus:ring-gray-500');
}

/**
 * เริ่มต้น Select2
 */
function initializeSelect2() {
    // Basic Select2
    $('.select2').each(function() {
        const $select = $(this);
        const options = {
            width: '100%',
            placeholder: $select.attr('placeholder') || 'เลือก...',
            allowClear: $select.data('allow-clear') !== false,
            theme: 'default'
        };
        
        // Multiple selection
        if ($select.attr('multiple')) {
            options.closeOnSelect = false;
        }
        
        // AJAX source
        if ($select.data('ajax-url')) {
            options.ajax = {
                url: $select.data('ajax-url'),
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term,
                        page: params.page || 1,
                        csrf_token: $('meta[name="csrf-token"]').attr('content')
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    
                    return {
                        results: data.results,
                        pagination: {
                            more: data.pagination.more
                        }
                    };
                },
                cache: true
            };
            options.minimumInputLength = 2;
        }
        
        $select.select2(options);
    });
    
    // Tags Select2
    $('.select2-tags').each(function() {
        $(this).select2({
            width: '100%',
            tags: true,
            tokenSeparators: [',', ' '],
            placeholder: 'เพิ่มแท็ก...'
        });
    });
}

/**
 * เริ่มต้น date pickers
 */
function initializeDatePickers() {
    // ตรวจสอบว่ามี flatpickr หรือไม่
    if (typeof flatpickr !== 'undefined') {
        // Date picker
        $('.datepicker').each(function() {
            flatpickr(this, {
                dateFormat: 'Y-m-d',
                locale: 'th',
                defaultDate: $(this).val() || null
            });
        });
        
        // DateTime picker
        $('.datetimepicker').each(function() {
            flatpickr(this, {
                enableTime: true,
                dateFormat: 'Y-m-d H:i',
                locale: 'th',
                defaultDate: $(this).val() || null
            });
        });
        
        // Time picker
        $('.timepicker').each(function() {
            flatpickr(this, {
                enableTime: true,
                noCalendar: true,
                dateFormat: 'H:i',
                defaultDate: $(this).val() || null
            });
        });
        
        // Date range picker
        $('.daterangepicker').each(function() {
            flatpickr(this, {
                mode: 'range',
                dateFormat: 'Y-m-d',
                locale: 'th'
            });
        });
    } else {
        // Fallback to HTML5 date inputs
        $('.datepicker, .datetimepicker').attr('type', function() {
            return $(this).hasClass('datetimepicker') ? 'datetime-local' : 'date';
        });
        
        $('.timepicker').attr('type', 'time');
    }
}

/**
 * เริ่มต้น text editors
 */
function initializeTextEditors() {
    // TinyMCE
    if (typeof tinymce !== 'undefined') {
        $('.tinymce').each(function() {
            const editorId = $(this).attr('id');
            if (editorId) {
                tinymce.init({
                    selector: '#' + editorId,
                    height: 300,
                    menubar: false,
                    plugins: [
                        'advlist autolink lists link image charmap print preview anchor',
                        'searchreplace visualblocks code fullscreen',
                        'insertdatetime media table paste code help wordcount'
                    ],
                    toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
                    content_style: 'body { font-family: Sarabun, Arial, sans-serif; font-size:14px }'
                });
            }
        });
    }
    
    // Simple text editors
    $('.text-editor').each(function() {
        // เพิ่มเครื่องมือง่ายๆ
        const $textarea = $(this);
        const toolbar = $(`
            <div class="text-editor-toolbar mb-2 p-2 bg-gray-50 border border-gray-300 rounded-t-lg">
                <button type="button" class="editor-btn" data-command="bold" title="ตัวหนา">
                    <i class="fas fa-bold"></i>
                </button>
                <button type="button" class="editor-btn" data-command="italic" title="ตัวเอียง">
                    <i class="fas fa-italic"></i>
                </button>
                <button type="button" class="editor-btn" data-command="underline" title="ขีดเส้นใต้">
                    <i class="fas fa-underline"></i>
                </button>
                <span class="mx-2 text-gray-300">|</span>
                <button type="button" class="editor-btn" data-command="justifyLeft" title="จัดซ้าย">
                    <i class="fas fa-align-left"></i>
                </button>
                <button type="button" class="editor-btn" data-command="justifyCenter" title="จัดกลาง">
                    <i class="fas fa-align-center"></i>
                </button>
                <button type="button" class="editor-btn" data-command="justifyRight" title="จัดขวา">
                    <i class="fas fa-align-right"></i>
                </button>
            </div>
        `);
        
        $textarea.before(toolbar);
        $textarea.addClass('rounded-t-none');
        
        // Toolbar events
        toolbar.find('.editor-btn').on('click', function(e) {
            e.preventDefault();
            const command = $(this).data('command');
            document.execCommand(command, false, null);
        });
    });
}

/**
 * เริ่มต้น form submissions
 */
function initializeFormSubmissions() {
    // AJAX form submissions
    $('.ajax-form').on('submit', function(e) {
        e.preventDefault();
        submitFormAjax(this);
    });
    
    // Form with loading
    $('.form-with-loading').on('submit', function() {
        const $form = $(this);
        const submitBtn = $form.find('button[type="submit"]');
        
        // Disable submit button and show loading
        submitBtn.prop('disabled', true);
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>กำลังดำเนินการ...');
        
        // Re-enable after 10 seconds (fallback)
        setTimeout(() => {
            submitBtn.prop('disabled', false).html(originalText);
        }, 10000);
    });
    
    // Auto-save forms
    $('.auto-save-form').each(function() {
        const $form = $(this);
        const interval = $form.data('auto-save-interval') || 30000; // 30 seconds default
        
        setInterval(() => {
            autoSaveForm($form);
        }, interval);
    });
}

/**
 * ส่งฟอร์มด้วย AJAX
 */
function submitFormAjax(form) {
    const $form = $(form);
    const url = $form.attr('action') || window.location.href;
    const method = $form.attr('method') || 'POST';
    const formData = new FormData(form);
    
    // Add CSRF token
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    if (csrfToken) {
        formData.append('csrf_token', csrfToken);
    }
    
    // Show loading
    showFormLoading($form);
    
    $.ajax({
        url: url,
        method: method,
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            hideFormLoading($form);
            
            if (response.success) {
                showAlert(response.message || 'บันทึกสำเร็จ', 'success');
                
                // Redirect if specified
                if (response.redirect) {
                    setTimeout(() => {
                        window.location.href = response.redirect;
                    }, 1500);
                }
                
                // Reset form if specified
                if (response.reset_form) {
                    form.reset();
                }
                
                // Trigger custom event
                $form.trigger('ajaxSuccess', response);
            } else {
                showAlert(response.message || 'เกิดข้อผิดพลาด', 'error');
                
                // Show validation errors
                if (response.errors) {
                    showValidationErrors($form, response.errors);
                }
            }
        },
        error: function(xhr, status, error) {
            hideFormLoading($form);
            
            let message = 'เกิดข้อผิดพลาดในการส่งข้อมูล';
            
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            } else if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                showValidationErrors($form, xhr.responseJSON.errors);
                return;
            }
            
            showAlert(message, 'error');
        }
    });
}

/**
 * แสดง loading สำหรับฟอร์ม
 */
function showFormLoading($form) {
    const submitBtn = $form.find('button[type="submit"]');
    submitBtn.prop('disabled', true);
    
    const originalText = submitBtn.data('original-text') || submitBtn.html();
    submitBtn.data('original-text', originalText);
    submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>กำลังดำเนินการ...');
    
    $form.addClass('form-loading');
}

/**
 * ซ่อน loading สำหรับฟอร์ม
 */
function hideFormLoading($form) {
    const submitBtn = $form.find('button[type="submit"]');
    submitBtn.prop('disabled', false);
    
    const originalText = submitBtn.data('original-text');
    if (originalText) {
        submitBtn.html(originalText);
    }
    
    $form.removeClass('form-loading');
}

/**
 * Auto-save ฟอร์ม
 */
function autoSaveForm($form) {
    const formData = new FormData($form[0]);
    formData.append('auto_save', '1');
    
    // Add CSRF token
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    if (csrfToken) {
        formData.append('csrf_token', csrfToken);
    }
    
    $.ajax({
        url: $form.attr('action') || window.location.href,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                showAutoSaveIndicator();
            }
        },
        error: function() {
            // Silent fail for auto-save
        }
    });
}

/**
 * แสดงตัวบ่งชี้ auto-save
 */
function showAutoSaveIndicator() {
    const indicator = $('#auto-save-indicator');
    if (indicator.length === 0) {
        $('body').append('<div id="auto-save-indicator" class="fixed top-4 right-4 bg-green-500 text-white px-3 py-2 rounded-lg text-sm shadow-lg z-50">บันทึกอัตโนมัติแล้ว</div>');
    } else {
        indicator.show();
    }
    
    setTimeout(() => {
        $('#auto-save-indicator').fadeOut();
    }, 2000);
}

/**
 * เริ่มต้น validation
 */
function initializeValidation() {
    // HTML5 validation
    $('.needs-validation').each(function() {
        const form = this;
        
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                // Focus first invalid field
                const firstInvalidField = form.querySelector(':invalid');
                if (firstInvalidField) {
                    firstInvalidField.focus();
                    firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
            
            form.classList.add('was-validated');
        }, false);
    });
    
    // Custom validation rules
    addCustomValidationRules();
    
    // Real-time validation
    initializeRealTimeValidation();
}

/**
 * เพิ่ม validation rules ที่กำหนดเอง
 */
function addCustomValidationRules() {
    // Email validation
    $('input[type="email"]').on('blur', function() {
        const email = $(this).val();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && !emailRegex.test(email)) {
            setFieldError($(this), 'รูปแบบอีเมลไม่ถูกต้อง');
        } else {
            clearFieldError($(this));
        }
    });
    
    // Phone validation
    $('input[data-validate="phone"]').on('blur', function() {
        const phone = $(this).val();
        const phoneRegex = /^[0-9]{9,10}$/;
        
        if (phone && !phoneRegex.test(phone)) {
            setFieldError($(this), 'หมายเลขโทรศัพท์ไม่ถูกต้อง (9-10 หลัก)');
        } else {
            clearFieldError($(this));
        }
    });
    
    // Password confirmation
    $('input[data-confirm]').on('blur', function() {
        const confirmField = $(this);
        const passwordField = $(confirmField.data('confirm'));
        
        if (confirmField.val() && passwordField.val() !== confirmField.val()) {
            setFieldError(confirmField, 'รหัสผ่านไม่ตรงกัน');
        } else {
            clearFieldError(confirmField);
        }
    });
    
    // Thai ID validation
    $('input[data-validate="thai-id"]').on('blur', function() {
        const thaiId = $(this).val();
        
        if (thaiId && !validateThaiId(thaiId)) {
            setFieldError($(this), 'เลขบัตรประจำตัวประชาชนไม่ถูกต้อง');
        } else {
            clearFieldError($(this));
        }
    });
}

/**
 * เริ่มต้น real-time validation
 */
function initializeRealTimeValidation() {
    $('input, textarea, select').on('input change', function() {
        const field = $(this);
        
        // Clear previous errors
        clearFieldError(field);
        
        // Validate required fields
        if (field.prop('required') && !field.val()) {
            return; // Don't show error until blur
        }
        
        // Validate by type
        const validationType = field.data('validate');
        if (validationType) {
            validateField(field, validationType);
        }
    });
}

/**
 * Validate field
 */
function validateField(field, type) {
    const value = field.val();
    let isValid = true;
    let errorMessage = '';
    
    switch (type) {
        case 'email':
            isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
            errorMessage = 'รูปแบบอีเมลไม่ถูกต้อง';
            break;
        case 'phone':
            isValid = /^[0-9]{9,10}$/.test(value);
            errorMessage = 'หมายเลขโทรศัพท์ไม่ถูกต้อง';
            break;
        case 'thai-id':
            isValid = validateThaiId(value);
            errorMessage = 'เลขบัตรประจำตัวประชาชนไม่ถูกต้อง';
            break;
        case 'url':
            isValid = /^https?:\/\/.+/.test(value);
            errorMessage = 'รูปแบบ URL ไม่ถูกต้อง';
            break;
    }
    
    if (!isValid) {
        setFieldError(field, errorMessage);
    }
    
    return isValid;
}

/**
 * ตั้งค่าข้อผิดพลาดของฟิลด์
 */
function setFieldError(field, message) {
    field.addClass('border-red-500 focus:border-red-500 focus:ring-red-500');
    field.removeClass('border-gray-300 focus:border-blue-500 focus:ring-blue-500');
    
    // Remove existing error message
    field.siblings('.field-error').remove();
    
    // Add error message
    field.after(`<div class="field-error text-sm text-red-600 mt-1">${message}</div>`);
}

/**
 * ล้างข้อผิดพลาดของฟิลด์
 */
function clearFieldError(field) {
    field.removeClass('border-red-500 focus:border-red-500 focus:ring-red-500');
    field.addClass('border-gray-300 focus:border-blue-500 focus:ring-blue-500');
    field.siblings('.field-error').remove();
}

/**
 * แสดงข้อผิดพลาด validation
 */
function showValidationErrors(form, errors) {
    // Clear existing errors
    form.find('.field-error').remove();
    form.find('.border-red-500').removeClass('border-red-500 focus:border-red-500 focus:ring-red-500');
    
    // Show new errors
    Object.keys(errors).forEach(fieldName => {
        const field = form.find(`[name="${fieldName}"]`);
        if (field.length) {
            setFieldError(field, errors[fieldName][0]);
        }
    });
}

/**
 * เริ่มต้น file uploads
 */
function initializeFileUploads() {
    // Drag and drop file upload
    $('.file-drop-zone').each(function() {
        const dropZone = $(this);
        const fileInput = dropZone.find('input[type="file"]');
        
        dropZone.on('dragover dragenter', function(e) {
            e.preventDefault();
            dropZone.addClass('border-blue-500 bg-blue-50');
        });
        
        dropZone.on('dragleave', function(e) {
            e.preventDefault();
            dropZone.removeClass('border-blue-500 bg-blue-50');
        });
        
        dropZone.on('drop', function(e) {
            e.preventDefault();
            dropZone.removeClass('border-blue-500 bg-blue-50');
            
            const files = e.originalEvent.dataTransfer.files;
            fileInput[0].files = files;
            fileInput.trigger('change');
        });
    });
    
    // File input preview
    $('input[type="file"]').on('change', function() {
        const fileInput = $(this);
        const files = this.files;
        
        if (files.length > 0) {
            showFilePreview(fileInput, files);
        }
    });
    
    // Multiple file upload with progress
    $('.multiple-file-upload').each(function() {
        initializeMultipleFileUpload($(this));
    });
}

/**
 * แสดงตัวอย่างไฟล์
 */
function showFilePreview(fileInput, files) {
    const previewContainer = fileInput.siblings('.file-preview');
    if (previewContainer.length === 0) {
        fileInput.after('<div class="file-preview mt-2"></div>');
    }
    
    const preview = fileInput.siblings('.file-preview');
    preview.empty();
    
    Array.from(files).forEach((file, index) => {
        const fileItem = $(`
            <div class="file-item flex items-center p-3 bg-gray-50 rounded-lg mb-2">
                <div class="file-icon mr-3">
                    <i class="fas fa-${getFileIcon(file.type)} text-2xl text-gray-600"></i>
                </div>
                <div class="file-info flex-1">
                    <div class="file-name font-medium">${file.name}</div>
                    <div class="file-size text-sm text-gray-500">${formatFileSize(file.size)}</div>
                </div>
                <div class="file-actions">
                    <button type="button" class="remove-file text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `);
        
        // Remove file functionality
        fileItem.find('.remove-file').on('click', function() {
            removeFileFromInput(fileInput[0], index);
            fileItem.remove();
        });
        
        preview.append(fileItem);
    });
}

/**
 * ได้รับไอคอนไฟล์
 */
function getFileIcon(mimeType) {
    if (mimeType.startsWith('image/')) return 'file-image';
    if (mimeType.startsWith('video/')) return 'file-video';
    if (mimeType.startsWith('audio/')) return 'file-audio';
    if (mimeType.includes('pdf')) return 'file-pdf';
    if (mimeType.includes('word')) return 'file-word';
    if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return 'file-excel';
    if (mimeType.includes('powerpoint') || mimeType.includes('presentation')) return 'file-powerpoint';
    if (mimeType.includes('zip') || mimeType.includes('rar')) return 'file-archive';
    return 'file';
}

/**
 * ลบไฟล์จาก input
 */
function removeFileFromInput(fileInput, indexToRemove) {
    const dt = new DataTransfer();
    const files = fileInput.files;
    
    for (let i = 0; i < files.length; i++) {
        if (i !== indexToRemove) {
            dt.items.add(files[i]);
        }
    }
    
    fileInput.files = dt.files;
}

/**
 * เริ่มต้น multiple file upload
 */
function initializeMultipleFileUpload($container) {
    const fileInput = $container.find('input[type="file"]');
    const progressContainer = $container.find('.upload-progress');
    
    fileInput.on('change', function() {
        const files = this.files;
        if (files.length === 0) return;
        
        // Upload each file
        Array.from(files).forEach((file, index) => {
            uploadFileWithProgress(file, index, progressContainer);
        });
    });
}

/**
 * อัปโหลดไฟล์พร้อม progress bar
 */
function uploadFileWithProgress(file, index, progressContainer) {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('csrf_token', $('meta[name="csrf-token"]').attr('content'));
    
    const progressItem = $(`
        <div class="upload-item mb-3" data-index="${index}">
            <div class="flex items-center justify-between mb-1">
                <span class="text-sm font-medium">${file.name}</span>
                <span class="text-sm text-gray-500">0%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full" style="width: 0%"></div>
            </div>
        </div>
    `);
    
    progressContainer.append(progressItem);
    
    $.ajax({
        url: BASE_URL + '/admin/api/upload.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        xhr: function() {
            const xhr = new window.XMLHttpRequest();
            
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    const progressBar = progressItem.find('.bg-blue-600');
                    const progressText = progressItem.find('.text-gray-500');
                    
                    progressBar.css('width', percentComplete + '%');
                    progressText.text(Math.round(percentComplete) + '%');
                }
            }, false);
            
            return xhr;
        },
        success: function(response) {
            if (response.success) {
                progressItem.find('.text-gray-500').text('สำเร็จ').addClass('text-green-600');
                progressItem.find('.bg-blue-600').addClass('bg-green-600').removeClass('bg-blue-600');
            } else {
                progressItem.find('.text-gray-500').text('ล้มเหลว').addClass('text-red-600');
                progressItem.find('.bg-blue-600').addClass('bg-red-600').removeClass('bg-blue-600');
            }
        },
        error: function() {
            progressItem.find('.text-gray-500').text('ข้อผิดพลาด').addClass('text-red-600');
            progressItem.find('.bg-blue-600').addClass('bg-red-600').removeClass('bg-blue-600');
        }
    });
}

/**
 * เริ่มต้น form enhancements
 */
function initializeFormEnhancements() {
    // Auto-growing textareas
    $('.auto-grow').each(function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    }).on('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
    
    // Character counters
    $('[data-max-length]').each(function() {
        const input = $(this);
        const maxLength = input.data('max-length');
        const counter = $(`<div class="character-counter text-sm text-gray-500 mt-1">${input.val().length}/${maxLength}</div>`);
        
        input.after(counter);
        
        input.on('input', function() {
            const currentLength = this.value.length;
            counter.text(`${currentLength}/${maxLength}`);
            
            if (currentLength > maxLength * 0.9) {
                counter.addClass('text-yellow-600').removeClass('text-gray-500');
            } else if (currentLength >= maxLength) {
                counter.addClass('text-red-600').removeClass('text-gray-500 text-yellow-600');
            } else {
                counter.addClass('text-gray-500').removeClass('text-yellow-600 text-red-600');
            }
        });
    });
    
    // Input masks
    if (typeof Inputmask !== 'undefined') {
        $('[data-mask]').each(function() {
            const mask = $(this).data('mask');
            Inputmask(mask).mask(this);
        });
    }
    
    // Dependent dropdowns
    $('.dependent-dropdown').each(function() {
        const dropdown = $(this);
        const parentSelector = dropdown.data('parent');
        const parent = $(parentSelector);
        
        parent.on('change', function() {
            loadDependentOptions(dropdown, this.value);
        });
    });
}

/**
 * โหลดตัวเลือกของ dependent dropdown
 */
function loadDependentOptions(dropdown, parentValue) {
    const url = dropdown.data('url');
    
    if (!url || !parentValue) {
        dropdown.empty().append('<option value="">เลือก...</option>');
        return;
    }
    
    dropdown.prop('disabled', true).empty().append('<option value="">กำลังโหลด...</option>');
    
    $.ajax({
        url: url,
        method: 'GET',
        data: {
            parent_value: parentValue,
            csrf_token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            dropdown.empty().append('<option value="">เลือก...</option>');
            
            if (response.success && response.data) {
                response.data.forEach(option => {
                    dropdown.append(`<option value="${option.value}">${option.text}</option>`);
                });
            }
            
            dropdown.prop('disabled', false);
        },
        error: function() {
            dropdown.empty().append('<option value="">เกิดข้อผิดพลาด</option>');
            dropdown.prop('disabled', false);
        }
    });
}

/**
 * ตรวจสอบเลขบัตรประชาชนไทย
 */
function validateThaiId(id) {
    if (!/^\d{13}$/.test(id)) return false;
    
    let sum = 0;
    for (let i = 0; i < 12; i++) {
        sum += parseInt(id.charAt(i)) * (13 - i);
    }
    
    const checkDigit = (11 - (sum % 11)) % 10;
    return checkDigit === parseInt(id.charAt(12));
}

/**
 * Export forms functions
 */
window.Forms = {
    submitAjax: submitFormAjax,
    validate: validateField,
    setError: setFieldError,
    clearError: clearFieldError,
    showLoading: showFormLoading,
    hideLoading: hideFormLoading
};