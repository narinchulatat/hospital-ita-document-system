// Forms specific JavaScript

$(document).ready(function() {
    // Initialize form features
    initializeForms();
    
    // Initialize file uploads
    initializeFileUploads();
    
    // Initialize form validation
    initializeFormValidation();
    
    // Initialize dynamic forms
    initializeDynamicForms();
    
    // Initialize form wizards
    initializeFormWizards();
});

/**
 * Initialize form features
 */
function initializeForms() {
    // Auto-resize textareas
    initializeAutoResize();
    
    // Initialize Select2
    initializeSelect2();
    
    // Initialize date pickers
    initializeDatePickers();
    
    // Initialize form auto-save
    initializeAutoSave();
    
    // Initialize form shortcuts
    initializeFormShortcuts();
}

/**
 * Initialize auto-resize textareas
 */
function initializeAutoResize() {
    $('textarea[data-auto-resize]').each(function() {
        autoResize(this);
    }).on('input', function() {
        autoResize(this);
    });
    
    function autoResize(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    }
}

/**
 * Initialize Select2
 */
function initializeSelect2() {
    if ($.fn.select2) {
        // Basic Select2
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'เลือก...'
        });
        
        // Multiple Select2
        $('.select2-multiple').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'เลือกรายการ...',
            allowClear: true
        });
        
        // Tags Select2
        $('.select2-tags').select2({
            theme: 'bootstrap-5',
            width: '100%',
            tags: true,
            placeholder: 'เพิ่มแท็ก...',
            tokenSeparators: [',', ' ']
        });
        
        // AJAX Select2
        $('.select2-ajax').each(function() {
            const $select = $(this);
            const url = $select.data('url');
            
            $select.select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'ค้นหา...',
                minimumInputLength: 2,
                ajax: {
                    url: url,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term,
                            page: params.page || 1
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        
                        return {
                            results: data.items,
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                }
            });
        });
    }
}

/**
 * Initialize date pickers
 */
function initializeDatePickers() {
    // Basic date picker
    $('.datepicker').each(function() {
        if (typeof $(this).datepicker === 'function') {
            $(this).datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true,
                language: 'th'
            });
        }
    });
    
    // Date range picker
    $('.daterangepicker').each(function() {
        if (typeof $(this).daterangepicker === 'function') {
            $(this).daterangepicker({
                locale: {
                    format: 'YYYY-MM-DD',
                    separator: ' ถึง ',
                    applyLabel: 'ยืนยัน',
                    cancelLabel: 'ยกเลิก',
                    fromLabel: 'จาก',
                    toLabel: 'ถึง',
                    customRangeLabel: 'กำหนดเอง',
                    weekLabel: 'สัปดาห์',
                    daysOfWeek: ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'],
                    monthNames: ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
                                'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'],
                    firstDay: 1
                }
            });
        }
    });
}

/**
 * Initialize file uploads
 */
function initializeFileUploads() {
    // Drag and drop file upload
    $('.file-drop-zone').each(function() {
        const $dropZone = $(this);
        const $fileInput = $dropZone.find('input[type="file"]');
        
        // Click to select files
        $dropZone.on('click', function(e) {
            if (!$(e.target).is('input')) {
                $fileInput.click();
            }
        });
        
        // File input change
        $fileInput.on('change', function() {
            handleFiles(this.files, $dropZone);
        });
        
        // Drag and drop events
        $dropZone.on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('drag-over');
        });
        
        $dropZone.on('dragleave', function(e) {
            e.preventDefault();
            $(this).removeClass('drag-over');
        });
        
        $dropZone.on('drop', function(e) {
            e.preventDefault();
            $(this).removeClass('drag-over');
            
            const files = e.originalEvent.dataTransfer.files;
            handleFiles(files, $dropZone);
        });
    });
    
    // Profile image preview
    $('input[type="file"][data-preview]').on('change', function() {
        const file = this.files[0];
        const previewSelector = $(this).data('preview');
        
        if (file && previewSelector) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $(previewSelector).attr('src', e.target.result).show();
                $(previewSelector).siblings('.placeholder').hide();
            };
            reader.readAsDataURL(file);
        }
    });
    
    function handleFiles(files, $dropZone) {
        const maxFiles = parseInt($dropZone.data('max-files')) || 10;
        const maxSize = parseInt($dropZone.data('max-size')) || 10 * 1024 * 1024; // 10MB
        const allowedTypes = $dropZone.data('allowed-types') ? $dropZone.data('allowed-types').split(',') : [];
        
        if (files.length > maxFiles) {
            AdminJS.showAlert(`สามารถอัปโหลดได้สูงสุด ${maxFiles} ไฟล์`, 'warning');
            return;
        }
        
        const fileList = [];
        
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            
            // Check file size
            if (file.size > maxSize) {
                AdminJS.showAlert(`ไฟล์ ${file.name} มีขนาดใหญ่เกิน ${AdminJS.formatFileSize(maxSize)}`, 'warning');
                continue;
            }
            
            // Check file type
            if (allowedTypes.length > 0 && !allowedTypes.includes(file.type)) {
                AdminJS.showAlert(`ไฟล์ ${file.name} ไม่ใช่ประเภทที่อนุญาต`, 'warning');
                continue;
            }
            
            fileList.push(file);
        }
        
        if (fileList.length > 0) {
            displayFileList(fileList, $dropZone);
            
            // Trigger custom event
            $dropZone.trigger('files-selected', [fileList]);
        }
    }
    
    function displayFileList(files, $dropZone) {
        let $fileList = $dropZone.find('.file-list');
        
        if ($fileList.length === 0) {
            $fileList = $('<div class="file-list mt-3"></div>');
            $dropZone.append($fileList);
        }
        
        $fileList.empty();
        
        files.forEach((file, index) => {
            const fileItem = `
                <div class="file-item d-flex align-items-center justify-content-between p-2 border rounded mb-2">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-file-alt me-2 text-primary"></i>
                        <div>
                            <div class="fw-medium">${file.name}</div>
                            <small class="text-muted">${AdminJS.formatFileSize(file.size)}</small>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-file" data-index="${index}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            $fileList.append(fileItem);
        });
        
        // Remove file functionality
        $fileList.find('.remove-file').on('click', function() {
            const index = parseInt($(this).data('index'));
            $(this).closest('.file-item').remove();
            
            // Trigger custom event
            $dropZone.trigger('file-removed', [index]);
        });
    }
}

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    // Real-time validation
    $('input, textarea, select').on('blur', function() {
        validateField($(this));
    });
    
    // Custom validation rules
    addCustomValidationRules();
    
    // Form submission validation
    $('form.needs-validation').on('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            
            // Focus first invalid field
            const firstInvalid = $(this).find(':invalid').first();
            if (firstInvalid.length) {
                firstInvalid.focus();
                
                // Scroll to field if needed
                $('html, body').animate({
                    scrollTop: firstInvalid.offset().top - 100
                }, 300);
            }
        }
        
        $(this).addClass('was-validated');
    });
}

/**
 * Add custom validation rules
 */
function addCustomValidationRules() {
    // Thai ID validation
    $('input[data-validate="thai-id"]').on('input', function() {
        const id = $(this).val().replace(/\D/g, '');
        
        if (id.length === 13) {
            if (validateThaiID(id)) {
                $(this)[0].setCustomValidity('');
            } else {
                $(this)[0].setCustomValidity('เลขบัตรประชาชนไม่ถูกต้อง');
            }
        } else if (id.length > 0) {
            $(this)[0].setCustomValidity('เลขบัตรประชาชนต้องมี 13 หลัก');
        } else {
            $(this)[0].setCustomValidity('');
        }
    });
    
    // Phone number validation
    $('input[data-validate="phone"]').on('input', function() {
        const phone = $(this).val().replace(/\D/g, '');
        
        if (phone.length >= 9 && phone.length <= 10) {
            $(this)[0].setCustomValidity('');
        } else if (phone.length > 0) {
            $(this)[0].setCustomValidity('หมายเลขโทรศัพท์ไม่ถูกต้อง');
        } else {
            $(this)[0].setCustomValidity('');
        }
    });
    
    // Password confirmation
    $('input[data-confirm]').on('input', function() {
        const confirmInput = $(this);
        const passwordInput = $($(this).data('confirm'));
        
        if (confirmInput.val() !== passwordInput.val()) {
            confirmInput[0].setCustomValidity('รหัสผ่านไม่ตรงกัน');
        } else {
            confirmInput[0].setCustomValidity('');
        }
    });
    
    // Also validate when the original password changes
    $('input[type="password"]').on('input', function() {
        const passwordInput = $(this);
        const confirmInput = $(`input[data-confirm="#${passwordInput.attr('id')}"]`);
        
        if (confirmInput.length && confirmInput.val() !== passwordInput.val()) {
            confirmInput[0].setCustomValidity('รหัสผ่านไม่ตรงกัน');
        } else if (confirmInput.length) {
            confirmInput[0].setCustomValidity('');
        }
    });
}

/**
 * Validate Thai ID
 */
function validateThaiID(id) {
    if (id.length !== 13) return false;
    
    const digits = id.split('').map(Number);
    let sum = 0;
    
    for (let i = 0; i < 12; i++) {
        sum += digits[i] * (13 - i);
    }
    
    const checkDigit = (11 - (sum % 11)) % 10;
    return checkDigit === digits[12];
}

/**
 * Validate single field
 */
function validateField($field) {
    if ($field[0].checkValidity()) {
        $field.removeClass('is-invalid').addClass('is-valid');
    } else {
        $field.removeClass('is-valid').addClass('is-invalid');
    }
}

/**
 * Initialize auto-save
 */
function initializeAutoSave() {
    $('form[data-auto-save]').each(function() {
        const $form = $(this);
        const interval = parseInt($form.data('auto-save')) || 30000; // 30 seconds
        const storageKey = `form-autosave-${$form.attr('id') || 'default'}`;
        
        // Load saved data
        const savedData = localStorage.getItem(storageKey);
        if (savedData) {
            try {
                const data = JSON.parse(savedData);
                Object.keys(data).forEach(name => {
                    const $field = $form.find(`[name="${name}"]`);
                    if ($field.length && !$field.val()) {
                        $field.val(data[name]);
                    }
                });
            } catch (e) {
                console.error('Error loading auto-saved data:', e);
            }
        }
        
        // Auto-save on interval
        setInterval(() => {
            const formData = $form.serializeArray();
            const data = {};
            
            formData.forEach(field => {
                data[field.name] = field.value;
            });
            
            localStorage.setItem(storageKey, JSON.stringify(data));
        }, interval);
        
        // Clear saved data on successful submission
        $form.on('submit', function() {
            localStorage.removeItem(storageKey);
        });
    });
}

/**
 * Initialize form shortcuts
 */
function initializeFormShortcuts() {
    $(document).on('keydown', function(e) {
        // Ctrl+S to save form
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            const $submitBtn = $('form:visible').find('button[type="submit"], input[type="submit"]').first();
            if ($submitBtn.length) {
                $submitBtn.click();
            }
        }
        
        // Ctrl+Enter to submit form
        if (e.ctrlKey && e.key === 'Enter') {
            const $form = $(':focus').closest('form');
            if ($form.length) {
                $form.submit();
            }
        }
    });
}

/**
 * Initialize dynamic forms
 */
function initializeDynamicForms() {
    // Add repeater fields
    $(document).on('click', '.add-field', function() {
        const $template = $($(this).data('template'));
        const $container = $($(this).data('container'));
        
        if ($template.length && $container.length) {
            const index = $container.children().length;
            let html = $template.html();
            
            // Replace placeholders
            html = html.replace(/\[INDEX\]/g, index);
            html = html.replace(/\{INDEX\}/g, index);
            
            $container.append(html);
            
            // Reinitialize components for new fields
            initializeSelect2();
            initializeDatePickers();
        }
    });
    
    // Remove repeater fields
    $(document).on('click', '.remove-field', function() {
        $(this).closest('.repeater-item').remove();
    });
    
    // Conditional fields
    $('[data-toggle-field]').on('change', function() {
        const targetField = $(this).data('toggle-field');
        const targetValue = $(this).data('toggle-value');
        const $target = $(targetField);
        
        if ($(this).val() === targetValue || ($(this).is(':checkbox') && $(this).prop('checked'))) {
            $target.show().find('input, select, textarea').prop('disabled', false);
        } else {
            $target.hide().find('input, select, textarea').prop('disabled', true);
        }
    }).trigger('change');
}

/**
 * Initialize form wizards
 */
function initializeFormWizards() {
    $('.form-wizard').each(function() {
        const $wizard = $(this);
        const $steps = $wizard.find('.form-step');
        const $nextBtn = $wizard.find('.btn-next');
        const $prevBtn = $wizard.find('.btn-prev');
        const $submitBtn = $wizard.find('.btn-submit');
        
        let currentStep = 0;
        
        function showStep(step) {
            $steps.removeClass('active').eq(step).addClass('active');
            
            // Update step indicators
            $wizard.find('.step-indicator').removeClass('active completed');
            $wizard.find('.step-indicator').eq(step).addClass('active');
            $wizard.find('.step-indicator').slice(0, step).addClass('completed');
            
            // Update buttons
            $prevBtn.toggle(step > 0);
            $nextBtn.toggle(step < $steps.length - 1);
            $submitBtn.toggle(step === $steps.length - 1);
        }
        
        $nextBtn.on('click', function() {
            const $currentStep = $steps.eq(currentStep);
            
            // Validate current step
            if (validateStep($currentStep)) {
                currentStep++;
                showStep(currentStep);
            }
        });
        
        $prevBtn.on('click', function() {
            currentStep--;
            showStep(currentStep);
        });
        
        // Step indicator clicks
        $wizard.find('.step-indicator').on('click', function() {
            const targetStep = $(this).index();
            
            // Only allow going to completed steps or next step
            if (targetStep <= currentStep || targetStep === currentStep + 1) {
                currentStep = targetStep;
                showStep(currentStep);
            }
        });
        
        showStep(0);
    });
}

/**
 * Validate wizard step
 */
function validateStep($step) {
    const $form = $step.closest('form');
    const $fields = $step.find('input, select, textarea').filter('[required]');
    
    let isValid = true;
    
    $fields.each(function() {
        if (!this.checkValidity()) {
            $(this).addClass('is-invalid');
            isValid = false;
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    return isValid;
}

// Export for global use
window.FormJS = {
    validateField,
    validateThaiID,
    initializeSelect2,
    initializeDatePickers
};