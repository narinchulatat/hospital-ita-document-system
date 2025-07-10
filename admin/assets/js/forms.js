// Form handling and validation functionality

$(document).ready(function() {
    initializeForms();
    initializeValidation();
    initializeFileUploads();
    initializeFormEvents();
});

/**
 * Initialize form functionality
 */
function initializeForms() {
    // Initialize all forms with .admin-form class
    $('.admin-form').each(function() {
        const $form = $(this);
        setupFormFeatures($form);
    });
    
    // Auto-save functionality
    initializeAutoSave();
    
    // Form wizard functionality
    initializeFormWizard();
    
    // Dynamic form fields
    initializeDynamicFields();
}

/**
 * Setup form features
 */
function setupFormFeatures($form) {
    // Add CSRF token if not present
    if (!$form.find('input[name="csrf_token"]').length) {
        const token = $('meta[name="csrf-token"]').attr('content');
        if (token) {
            $form.append(`<input type="hidden" name="csrf_token" value="${token}">`);
        }
    }
    
    // Prevent double submission
    preventDoubleSubmission($form);
    
    // Enhanced styling
    applyTailwindFormStyling($form);
    
    // Initialize form plugins
    initializeFormPlugins($form);
}

/**
 * Apply TailwindCSS styling to form elements
 */
function applyTailwindFormStyling($form) {
    // Style text inputs
    $form.find('input[type="text"], input[type="email"], input[type="password"], input[type="number"], input[type="tel"], input[type="url"], input[type="date"], input[type="time"], input[type="datetime-local"]')
        .addClass('block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-200');
    
    // Style textareas
    $form.find('textarea')
        .addClass('block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-200');
    
    // Style select elements
    $form.find('select')
        .addClass('block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-200');
    
    // Style checkboxes and radios
    $form.find('input[type="checkbox"]')
        .addClass('h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500');
    
    $form.find('input[type="radio"]')
        .addClass('h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500');
    
    // Style file inputs
    $form.find('input[type="file"]')
        .addClass('block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100');
    
    // Style labels
    $form.find('label')
        .addClass('block text-sm font-medium text-gray-700 mb-1');
    
    // Style buttons
    $form.find('button[type="submit"]')
        .addClass('inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200');
    
    $form.find('button[type="button"], .btn-secondary')
        .addClass('inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200');
    
    // Style form groups
    $form.find('.form-group, .field-group')
        .addClass('mb-4');
    
    // Style help text
    $form.find('.help-text, .form-help')
        .addClass('mt-1 text-sm text-gray-500');
    
    // Style error messages
    $form.find('.error-message, .invalid-feedback')
        .addClass('mt-1 text-sm text-red-600');
}

/**
 * Initialize form plugins
 */
function initializeFormPlugins($form) {
    // Initialize Select2
    $form.find('.select2').select2({
        width: '100%',
        dropdownCssClass: 'select2-tailwind'
    });
    
    // Initialize date pickers
    $form.find('.datepicker').flatpickr({
        dateFormat: 'Y-m-d',
        locale: 'th'
    });
    
    // Initialize datetime pickers
    $form.find('.datetimepicker').flatpickr({
        enableTime: true,
        dateFormat: 'Y-m-d H:i',
        locale: 'th'
    });
    
    // Initialize time pickers
    $form.find('.timepicker').flatpickr({
        enableTime: true,
        noCalendar: true,
        dateFormat: 'H:i',
        locale: 'th'
    });
    
    // Initialize rich text editors
    $form.find('.richtext').each(function() {
        initializeRichTextEditor($(this));
    });
    
    // Initialize auto-resize textareas
    $form.find('textarea[data-auto-resize]').each(function() {
        autoResizeTextarea($(this));
    });
    
    // Initialize character counters
    $form.find('[data-max-length]').each(function() {
        initializeCharacterCounter($(this));
    });
}

/**
 * Initialize validation
 */
function initializeValidation() {
    // Custom validation rules
    setupCustomValidationRules();
    
    // Real-time validation
    setupRealTimeValidation();
    
    // Form submission validation
    setupSubmissionValidation();
}

/**
 * Setup custom validation rules
 */
function setupCustomValidationRules() {
    // Thai ID card validation
    $.validator.addMethod('thaiId', function(value, element) {
        if (value.length !== 13) return false;
        
        let sum = 0;
        for (let i = 0; i < 12; i++) {
            sum += parseInt(value.charAt(i)) * (13 - i);
        }
        
        const checkDigit = (11 - (sum % 11)) % 10;
        return checkDigit === parseInt(value.charAt(12));
    }, 'รูปแบบเลขบัตรประชาชนไม่ถูกต้อง');
    
    // Thai phone number validation
    $.validator.addMethod('thaiPhone', function(value, element) {
        return /^[0-9]{9,10}$/.test(value) || /^(\+66|0)[0-9]{8,9}$/.test(value);
    }, 'รูปแบบหมายเลขโทรศัพท์ไม่ถูกต้อง');
    
    // Password strength validation
    $.validator.addMethod('strongPassword', function(value, element) {
        return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/.test(value);
    }, 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร ประกอบด้วยตัวพิมพ์เล็ก พิมพ์ใหญ่ ตัวเลข และอักขระพิเศษ');
    
    // File size validation
    $.validator.addMethod('filesize', function(value, element, param) {
        if (element.files.length === 0) return true;
        
        const maxSize = param * 1024 * 1024; // Convert MB to bytes
        return element.files[0].size <= maxSize;
    }, function(param) {
        return `ขนาดไฟล์ต้องไม่เกิน ${param} MB`;
    });
    
    // File type validation
    $.validator.addMethod('filetype', function(value, element, param) {
        if (element.files.length === 0) return true;
        
        const allowedTypes = param.split(',');
        const fileType = element.files[0].type;
        const fileName = element.files[0].name;
        const fileExtension = fileName.split('.').pop().toLowerCase();
        
        return allowedTypes.includes(fileType) || allowedTypes.includes('.' + fileExtension);
    }, function(param) {
        return `ประเภทไฟล์ที่อนุญาต: ${param}`;
    });
}

/**
 * Setup real-time validation
 */
function setupRealTimeValidation() {
    // Email validation
    $(document).on('blur', 'input[type="email"]', function() {
        const $input = $(this);
        const email = $input.val();
        
        if (email && !isValidEmail(email)) {
            showFieldError($input, 'รูปแบบอีเมลไม่ถูกต้อง');
        } else {
            clearFieldError($input);
        }
    });
    
    // Password confirmation
    $(document).on('blur', 'input[data-confirm-password]', function() {
        const $input = $(this);
        const $passwordInput = $($input.data('confirm-password'));
        
        if ($input.val() && $passwordInput.val() && $input.val() !== $passwordInput.val()) {
            showFieldError($input, 'รหัสผ่านไม่ตรงกัน');
        } else {
            clearFieldError($input);
        }
    });
    
    // Required field validation
    $(document).on('blur', 'input[required], textarea[required], select[required]', function() {
        const $input = $(this);
        
        if (!$input.val() || $input.val().trim() === '') {
            showFieldError($input, 'กรุณากรอกข้อมูลในช่องนี้');
        } else {
            clearFieldError($input);
        }
    });
    
    // Character limit validation
    $(document).on('input', '[data-max-length]', function() {
        const $input = $(this);
        const maxLength = parseInt($input.data('max-length'));
        const currentLength = $input.val().length;
        
        updateCharacterCounter($input, currentLength, maxLength);
        
        if (currentLength > maxLength) {
            showFieldError($input, `ความยาวไม่เกิน ${maxLength} ตัวอักษร`);
        } else {
            clearFieldError($input);
        }
    });
}

/**
 * Setup submission validation
 */
function setupSubmissionValidation() {
    $(document).on('submit', '.admin-form', function(e) {
        const $form = $(this);
        
        // Clear previous errors
        clearAllErrors($form);
        
        // Validate form
        if (!validateForm($form)) {
            e.preventDefault();
            return false;
        }
        
        // Show loading state
        showFormLoading($form);
        
        return true;
    });
}

/**
 * Validate form
 */
function validateForm($form) {
    let isValid = true;
    const errors = [];
    
    // Check required fields
    $form.find('[required]').each(function() {
        const $field = $(this);
        
        if (!$field.val() || $field.val().trim() === '') {
            const label = getFieldLabel($field);
            showFieldError($field, `กรุณากรอก${label}`);
            errors.push(`กรุณากรอก${label}`);
            isValid = false;
        }
    });
    
    // Check email fields
    $form.find('input[type="email"]').each(function() {
        const $field = $(this);
        const email = $field.val();
        
        if (email && !isValidEmail(email)) {
            showFieldError($field, 'รูปแบบอีเมลไม่ถูกต้อง');
            errors.push('รูปแบบอีเมลไม่ถูกต้อง');
            isValid = false;
        }
    });
    
    // Check password confirmation
    $form.find('input[data-confirm-password]').each(function() {
        const $field = $(this);
        const $passwordInput = $($field.data('confirm-password'));
        
        if ($field.val() !== $passwordInput.val()) {
            showFieldError($field, 'รหัสผ่านไม่ตรงกัน');
            errors.push('รหัสผ่านไม่ตรงกัน');
            isValid = false;
        }
    });
    
    // Check file uploads
    $form.find('input[type="file"][required]').each(function() {
        const $field = $(this);
        
        if ($field[0].files.length === 0) {
            const label = getFieldLabel($field);
            showFieldError($field, `กรุณาเลือก${label}`);
            errors.push(`กรุณาเลือก${label}`);
            isValid = false;
        }
    });
    
    // Show summary of errors if any
    if (!isValid && errors.length > 0) {
        showFormErrors($form, errors);
        
        // Focus first error field
        const $firstErrorField = $form.find('.error, .is-invalid').first();
        if ($firstErrorField.length) {
            $firstErrorField.focus();
        }
    }
    
    return isValid;
}

/**
 * Show field error
 */
function showFieldError($field, message) {
    $field.addClass('border-red-500 focus:border-red-500 focus:ring-red-500');
    $field.removeClass('border-gray-300 focus:border-blue-500 focus:ring-blue-500');
    
    // Remove existing error message
    $field.siblings('.error-message').remove();
    
    // Add new error message
    const errorHtml = `<div class="error-message mt-1 text-sm text-red-600">${message}</div>`;
    $field.after(errorHtml);
}

/**
 * Clear field error
 */
function clearFieldError($field) {
    $field.removeClass('border-red-500 focus:border-red-500 focus:ring-red-500');
    $field.addClass('border-gray-300 focus:border-blue-500 focus:ring-blue-500');
    $field.siblings('.error-message').remove();
}

/**
 * Clear all errors in form
 */
function clearAllErrors($form) {
    $form.find('.error-message').remove();
    $form.find('.border-red-500').removeClass('border-red-500 focus:border-red-500 focus:ring-red-500')
         .addClass('border-gray-300 focus:border-blue-500 focus:ring-blue-500');
}

/**
 * Show form errors summary
 */
function showFormErrors($form, errors) {
    const errorHtml = `
        <div class="form-errors bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">
                        พบข้อผิดพลาดในการกรอกข้อมูล
                    </h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc pl-5 space-y-1">
                            ${errors.map(error => `<li>${error}</li>`).join('')}
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing error summary
    $form.find('.form-errors').remove();
    
    // Add new error summary at the top of form
    $form.prepend(errorHtml);
    
    // Scroll to top of form
    $form[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
}

/**
 * Get field label
 */
function getFieldLabel($field) {
    const $label = $field.siblings('label').first();
    if ($label.length) {
        return $label.text().replace('*', '').trim();
    }
    
    const placeholder = $field.attr('placeholder');
    if (placeholder) {
        return placeholder;
    }
    
    const name = $field.attr('name');
    return name ? name.replace('_', ' ') : 'ช่องนี้';
}

/**
 * Show form loading state
 */
function showFormLoading($form) {
    const $submitBtn = $form.find('button[type="submit"]');
    const originalText = $submitBtn.html();
    
    $submitBtn.prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin mr-2"></i>กำลังบันทึก...')
            .data('original-text', originalText);
    
    // Disable all form inputs
    $form.find('input, textarea, select, button').not($submitBtn).prop('disabled', true);
}

/**
 * Hide form loading state
 */
function hideFormLoading($form) {
    const $submitBtn = $form.find('button[type="submit"]');
    const originalText = $submitBtn.data('original-text');
    
    if (originalText) {
        $submitBtn.prop('disabled', false).html(originalText);
    }
    
    // Re-enable all form inputs
    $form.find('input, textarea, select, button').prop('disabled', false);
}

/**
 * Initialize file uploads
 */
function initializeFileUploads() {
    // Drag and drop file upload
    $('.file-drop-zone').each(function() {
        const $zone = $(this);
        const $input = $zone.find('input[type="file"]');
        
        $zone.on('dragover', function(e) {
            e.preventDefault();
            $zone.addClass('border-blue-500 bg-blue-50');
        });
        
        $zone.on('dragleave', function(e) {
            e.preventDefault();
            $zone.removeClass('border-blue-500 bg-blue-50');
        });
        
        $zone.on('drop', function(e) {
            e.preventDefault();
            $zone.removeClass('border-blue-500 bg-blue-50');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                $input[0].files = files;
                $input.trigger('change');
            }
        });
    });
    
    // File preview functionality
    $(document).on('change', 'input[type="file"]', function() {
        const $input = $(this);
        const $preview = $input.siblings('.file-preview');
        
        if ($preview.length && this.files.length > 0) {
            showFilePreview($preview, this.files[0]);
        }
        
        // Update file info
        updateFileInfo($input);
    });
    
    // Multiple file upload
    $('.multiple-file-upload').each(function() {
        initializeMultipleFileUpload($(this));
    });
}

/**
 * Show file preview
 */
function showFilePreview($preview, file) {
    $preview.empty();
    
    const fileType = file.type;
    const fileName = file.name;
    const fileSize = formatFileSize(file.size);
    
    if (fileType.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $preview.html(`
                <div class="file-preview-item">
                    <img src="${e.target.result}" alt="${fileName}" class="max-w-full h-32 object-cover rounded-md border">
                    <div class="mt-2 text-sm text-gray-600">
                        <div class="font-medium">${fileName}</div>
                        <div class="text-gray-500">${fileSize}</div>
                    </div>
                </div>
            `);
        };
        reader.readAsDataURL(file);
    } else {
        const icon = getFileIcon(fileType);
        $preview.html(`
            <div class="file-preview-item flex items-center space-x-3 p-3 border rounded-md">
                <div class="flex-shrink-0">
                    <i class="fas ${icon} text-2xl text-gray-400"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-gray-900 truncate">${fileName}</div>
                    <div class="text-sm text-gray-500">${fileSize}</div>
                </div>
            </div>
        `);
    }
}

/**
 * Get file icon based on file type
 */
function getFileIcon(fileType) {
    if (fileType.includes('pdf')) return 'fa-file-pdf';
    if (fileType.includes('word')) return 'fa-file-word';
    if (fileType.includes('excel') || fileType.includes('spreadsheet')) return 'fa-file-excel';
    if (fileType.includes('powerpoint') || fileType.includes('presentation')) return 'fa-file-powerpoint';
    if (fileType.includes('zip') || fileType.includes('rar')) return 'fa-file-archive';
    if (fileType.includes('image')) return 'fa-file-image';
    if (fileType.includes('video')) return 'fa-file-video';
    if (fileType.includes('audio')) return 'fa-file-audio';
    if (fileType.includes('text')) return 'fa-file-alt';
    return 'fa-file';
}

/**
 * Update file info
 */
function updateFileInfo($input) {
    const $info = $input.siblings('.file-info');
    if ($info.length && $input[0].files.length > 0) {
        const file = $input[0].files[0];
        const fileName = file.name;
        const fileSize = formatFileSize(file.size);
        
        $info.html(`
            <div class="text-sm text-gray-600">
                <span class="font-medium">${fileName}</span>
                <span class="text-gray-500">(${fileSize})</span>
            </div>
        `);
    }
}

/**
 * Initialize multiple file upload
 */
function initializeMultipleFileUpload($container) {
    const $input = $container.find('input[type="file"]');
    const $list = $container.find('.file-list');
    
    $input.on('change', function() {
        $list.empty();
        
        Array.from(this.files).forEach((file, index) => {
            const fileItem = createFileListItem(file, index);
            $list.append(fileItem);
        });
    });
}

/**
 * Create file list item
 */
function createFileListItem(file, index) {
    const fileName = file.name;
    const fileSize = formatFileSize(file.size);
    const icon = getFileIcon(file.type);
    
    return $(`
        <div class="file-item flex items-center justify-between p-3 border rounded-md mb-2" data-index="${index}">
            <div class="flex items-center space-x-3">
                <i class="fas ${icon} text-gray-400"></i>
                <div>
                    <div class="text-sm font-medium text-gray-900">${fileName}</div>
                    <div class="text-sm text-gray-500">${fileSize}</div>
                </div>
            </div>
            <button type="button" class="remove-file text-red-600 hover:text-red-800">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `);
}

/**
 * Initialize auto-save functionality
 */
function initializeAutoSave() {
    $('.auto-save-form').each(function() {
        const $form = $(this);
        const autoSaveInterval = $form.data('auto-save-interval') || 30000; // 30 seconds default
        
        let autoSaveTimer;
        
        $form.on('change input', function() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => {
                performAutoSave($form);
            }, autoSaveInterval);
        });
    });
}

/**
 * Perform auto-save
 */
function performAutoSave($form) {
    const autoSaveUrl = $form.data('auto-save-url');
    if (!autoSaveUrl) return;
    
    const formData = new FormData($form[0]);
    
    $.ajax({
        url: autoSaveUrl,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                showAutoSaveStatus('บันทึกอัตโนมัติเรียบร้อย', 'success');
            }
        },
        error: function() {
            showAutoSaveStatus('ไม่สามารถบันทึกอัตโนมัติได้', 'error');
        }
    });
}

/**
 * Show auto-save status
 */
function showAutoSaveStatus(message, type) {
    const statusClass = type === 'success' ? 'text-green-600' : 'text-red-600';
    const icon = type === 'success' ? 'fa-check' : 'fa-exclamation-triangle';
    
    const $status = $('.auto-save-status');
    if ($status.length) {
        $status.html(`<i class="fas ${icon} mr-1"></i>${message}`)
               .removeClass('text-green-600 text-red-600')
               .addClass(statusClass);
        
        setTimeout(() => {
            $status.fadeOut();
        }, 3000);
    }
}

/**
 * Initialize form wizard
 */
function initializeFormWizard() {
    $('.form-wizard').each(function() {
        const $wizard = $(this);
        setupFormWizard($wizard);
    });
}

/**
 * Setup form wizard
 */
function setupFormWizard($wizard) {
    const $steps = $wizard.find('.wizard-step');
    const $nav = $wizard.find('.wizard-nav');
    let currentStep = 0;
    
    // Initialize navigation
    $nav.find('.step-nav').on('click', function() {
        const targetStep = $(this).data('step');
        if (targetStep <= currentStep + 1) {
            goToStep(targetStep);
        }
    });
    
    // Next button
    $wizard.find('.btn-next').on('click', function() {
        if (validateCurrentStep()) {
            goToStep(currentStep + 1);
        }
    });
    
    // Previous button
    $wizard.find('.btn-prev').on('click', function() {
        goToStep(currentStep - 1);
    });
    
    function goToStep(stepIndex) {
        if (stepIndex < 0 || stepIndex >= $steps.length) return;
        
        // Hide all steps
        $steps.addClass('hidden');
        
        // Show target step
        $steps.eq(stepIndex).removeClass('hidden');
        
        // Update navigation
        $nav.find('.step-nav').removeClass('active current');
        $nav.find('.step-nav').eq(stepIndex).addClass('active current');
        
        // Update buttons
        $wizard.find('.btn-prev').toggle(stepIndex > 0);
        $wizard.find('.btn-next').toggle(stepIndex < $steps.length - 1);
        $wizard.find('.btn-submit').toggle(stepIndex === $steps.length - 1);
        
        currentStep = stepIndex;
    }
    
    function validateCurrentStep() {
        const $currentStepEl = $steps.eq(currentStep);
        return validateForm($currentStepEl);
    }
    
    // Initialize first step
    goToStep(0);
}

/**
 * Initialize dynamic fields
 */
function initializeDynamicFields() {
    // Add field buttons
    $(document).on('click', '.add-field', function() {
        const $button = $(this);
        const template = $button.data('template');
        const container = $button.data('container');
        
        if (template && container) {
            addDynamicField(template, container);
        }
    });
    
    // Remove field buttons
    $(document).on('click', '.remove-field', function() {
        const $button = $(this);
        const $fieldGroup = $button.closest('.dynamic-field-group');
        
        Swal.fire({
            title: 'ยืนยันการลบ',
            text: 'คุณต้องการลบช่องนี้หรือไม่?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'ใช่, ลบ',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                $fieldGroup.remove();
                updateFieldIndices();
            }
        });
    });
}

/**
 * Add dynamic field
 */
function addDynamicField(templateId, containerId) {
    const template = document.getElementById(templateId);
    const container = document.getElementById(containerId);
    
    if (!template || !container) return;
    
    const fieldCount = container.children.length;
    const templateContent = template.innerHTML;
    
    // Replace placeholders with actual indices
    const newFieldHtml = templateContent.replace(/\[INDEX\]/g, fieldCount)
                                      .replace(/\{INDEX\}/g, fieldCount);
    
    const $newField = $(newFieldHtml);
    $(container).append($newField);
    
    // Apply styling and initialize plugins
    applyTailwindFormStyling($newField);
    initializeFormPlugins($newField);
    
    // Focus first input in new field
    $newField.find('input, textarea, select').first().focus();
}

/**
 * Update field indices
 */
function updateFieldIndices() {
    $('.dynamic-field-container').each(function() {
        const $container = $(this);
        
        $container.find('.dynamic-field-group').each(function(index) {
            const $fieldGroup = $(this);
            
            // Update names and IDs
            $fieldGroup.find('input, textarea, select').each(function() {
                const $field = $(this);
                const name = $field.attr('name');
                const id = $field.attr('id');
                
                if (name) {
                    $field.attr('name', name.replace(/\[\d+\]/, `[${index}]`));
                }
                
                if (id) {
                    $field.attr('id', id.replace(/_\d+$/, `_${index}`));
                }
            });
            
            // Update labels
            $fieldGroup.find('label').each(function() {
                const $label = $(this);
                const forAttr = $label.attr('for');
                
                if (forAttr) {
                    $label.attr('for', forAttr.replace(/_\d+$/, `_${index}`));
                }
            });
        });
    });
}

/**
 * Initialize rich text editor
 */
function initializeRichTextEditor($element) {
    // Simple rich text editor implementation
    // You can replace this with your preferred editor (CKEditor, TinyMCE, etc.)
    
    const toolbar = `
        <div class="richtext-toolbar border-b border-gray-200 p-2 flex items-center space-x-2 bg-gray-50">
            <button type="button" class="p-1 hover:bg-gray-200 rounded" data-command="bold">
                <i class="fas fa-bold"></i>
            </button>
            <button type="button" class="p-1 hover:bg-gray-200 rounded" data-command="italic">
                <i class="fas fa-italic"></i>
            </button>
            <button type="button" class="p-1 hover:bg-gray-200 rounded" data-command="underline">
                <i class="fas fa-underline"></i>
            </button>
            <div class="border-l border-gray-300 h-6 mx-2"></div>
            <button type="button" class="p-1 hover:bg-gray-200 rounded" data-command="insertUnorderedList">
                <i class="fas fa-list-ul"></i>
            </button>
            <button type="button" class="p-1 hover:bg-gray-200 rounded" data-command="insertOrderedList">
                <i class="fas fa-list-ol"></i>
            </button>
        </div>
    `;
    
    const editor = `
        <div class="richtext-editor border border-gray-300 min-h-32 p-3 focus-within:ring-blue-500 focus-within:border-blue-500" contenteditable="true">
            ${$element.val()}
        </div>
    `;
    
    const $wrapper = $('<div class="richtext-wrapper border border-gray-300 rounded-md overflow-hidden">');
    $wrapper.html(toolbar + editor);
    
    $element.after($wrapper).hide();
    
    const $editor = $wrapper.find('.richtext-editor');
    const $toolbar = $wrapper.find('.richtext-toolbar');
    
    // Toolbar events
    $toolbar.on('click', 'button', function(e) {
        e.preventDefault();
        const command = $(this).data('command');
        document.execCommand(command, false, null);
        $editor.focus();
    });
    
    // Update original textarea
    $editor.on('input', function() {
        $element.val($editor.html());
    });
}

/**
 * Auto-resize textarea
 */
function autoResizeTextarea($textarea) {
    $textarea.on('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    }).trigger('input');
}

/**
 * Initialize character counter
 */
function initializeCharacterCounter($field) {
    const maxLength = parseInt($field.data('max-length'));
    const $counter = $(`<div class="character-counter text-xs text-gray-500 mt-1 text-right"></div>`);
    
    $field.after($counter);
    
    updateCharacterCounter($field, $field.val().length, maxLength);
}

/**
 * Update character counter
 */
function updateCharacterCounter($field, currentLength, maxLength) {
    const $counter = $field.siblings('.character-counter');
    const remaining = maxLength - currentLength;
    
    $counter.text(`${currentLength}/${maxLength}`);
    
    if (remaining < 0) {
        $counter.addClass('text-red-600').removeClass('text-gray-500 text-yellow-600');
    } else if (remaining < maxLength * 0.1) {
        $counter.addClass('text-yellow-600').removeClass('text-gray-500 text-red-600');
    } else {
        $counter.addClass('text-gray-500').removeClass('text-red-600 text-yellow-600');
    }
}

/**
 * Initialize form events
 */
function initializeFormEvents() {
    // Prevent accidental navigation away from unsaved forms
    let formChanged = false;
    
    $('.admin-form').on('change input', function() {
        formChanged = true;
    });
    
    $('.admin-form').on('submit', function() {
        formChanged = false;
    });
    
    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
    
    // Form reset functionality
    $('.btn-reset').on('click', function(e) {
        e.preventDefault();
        
        const $form = $(this).closest('form');
        
        Swal.fire({
            title: 'ยืนยันการรีเซ็ต',
            text: 'คุณต้องการล้างข้อมูลทั้งหมดในฟอร์มหรือไม่?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'ใช่, รีเซ็ต',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                $form[0].reset();
                clearAllErrors($form);
                formChanged = false;
            }
        });
    });
}

/**
 * Helper functions
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function preventDoubleSubmission($form) {
    $form.on('submit', function() {
        const $submitBtn = $form.find('button[type="submit"]');
        setTimeout(() => {
            $submitBtn.prop('disabled', true);
        }, 0);
    });
}

// Global functions for external access
window.FormUtils = {
    validateForm,
    showFieldError,
    clearFieldError,
    clearAllErrors,
    showFormLoading,
    hideFormLoading,
    showFilePreview,
    updateCharacterCounter
};