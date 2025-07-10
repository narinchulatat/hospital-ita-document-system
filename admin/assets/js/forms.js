// Forms JavaScript - TailwindCSS Compatible

$(document).ready(function() {
    initializeForms();
    initializeFileUploads();
    initializeFormValidation();
    initializeFormWizards();
    initializeRichTextEditors();
});

/**
 * Initialize form features
 */
function initializeForms() {
    // Initialize Select2 with TailwindCSS
    initializeSelect2();
    
    // Initialize date pickers
    initializeDatePickers();
    
    // Initialize form toggles
    initializeFormToggles();
    
    // Initialize dynamic fields
    initializeDynamicFields();
    
    // Initialize auto-save
    initializeAutoSave();
    
    // Initialize form dependencies
    initializeFormDependencies();
}

/**
 * Initialize Select2 dropdowns
 */
function initializeSelect2() {
    // Basic Select2
    $('.select2').select2({
        width: '100%',
        language: {
            noResults: function() {
                return "ไม่พบข้อมูล";
            },
            searching: function() {
                return "กำลังค้นหา...";
            },
            loadingMore: function() {
                return "กำลังโหลดข้อมูลเพิ่มเติม...";
            }
        }
    });
    
    // Multiple Select2
    $('.select2-multiple').select2({
        width: '100%',
        placeholder: 'เลือกรายการ...',
        allowClear: true,
        language: {
            noResults: function() {
                return "ไม่พบข้อมูล";
            },
            searching: function() {
                return "กำลังค้นหา...";
            }
        }
    });
    
    // Ajax Select2
    $('.select2-ajax').each(function() {
        const $select = $(this);
        const ajaxUrl = $select.data('ajax-url');
        
        $select.select2({
            width: '100%',
            placeholder: 'ค้นหาและเลือก...',
            minimumInputLength: 2,
            ajax: {
                url: ajaxUrl,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term,
                        page: params.page || 1,
                        csrf_token: $('meta[name="csrf-token"]').attr('content')
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
            },
            language: {
                inputTooShort: function() {
                    return "กรุณาพิมพ์อย่างน้อย 2 ตัวอักษร";
                },
                noResults: function() {
                    return "ไม่พบข้อมูล";
                },
                searching: function() {
                    return "กำลังค้นหา...";
                }
            }
        });
    });
}

/**
 * Initialize date pickers
 */
function initializeDatePickers() {
    // Basic date picker
    $('.datepicker').each(function() {
        const $input = $(this);
        const options = {
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true,
            language: 'th-th',
            weekStart: 1
        };
        
        // Add custom options from data attributes
        if ($input.data('min-date')) {
            options.startDate = $input.data('min-date');
        }
        
        if ($input.data('max-date')) {
            options.endDate = $input.data('max-date');
        }
        
        $input.datepicker(options);
    });
    
    // Date range picker
    $('.daterange-picker').each(function() {
        const $input = $(this);
        
        $input.daterangepicker({
            locale: {
                format: 'YYYY-MM-DD',
                separator: ' ถึง ',
                applyLabel: 'ตกลง',
                cancelLabel: 'ยกเลิก',
                fromLabel: 'จาก',
                toLabel: 'ถึง',
                customRangeLabel: 'กำหนดเอง',
                weekLabel: 'สัปดาห์',
                daysOfWeek: ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'],
                monthNames: [
                    'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
                    'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
                ]
            },
            ranges: {
                'วันนี้': [moment(), moment()],
                'เมื่อวาน': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'สัปดาห์นี้': [moment().startOf('week'), moment().endOf('week')],
                'สัปดาห์ที่แล้ว': [moment().subtract(1, 'week').startOf('week'), moment().subtract(1, 'week').endOf('week')],
                'เดือนนี้': [moment().startOf('month'), moment().endOf('month')],
                'เดือนที่แล้ว': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });
    });
    
    // Time picker
    $('.timepicker').timepicker({
        timeFormat: 'HH:mm',
        interval: 15,
        minTime: '00:00',
        maxTime: '23:59',
        defaultTime: '09:00',
        startTime: '00:00',
        dynamic: false,
        dropdown: true,
        scrollbar: true
    });
}

/**
 * Initialize form toggles
 */
function initializeFormToggles() {
    // Toggle switches
    $('.form-toggle').on('change', function() {
        const target = $(this).data('toggle-target');
        const $target = $(target);
        
        if ($(this).is(':checked')) {
            $target.removeClass('hidden').slideDown();
        } else {
            $target.slideUp().addClass('hidden');
        }
    });
    
    // Radio toggles
    $('input[type="radio"][data-toggle-target]').on('change', function() {
        const target = $(this).data('toggle-target');
        const $target = $(target);
        const name = $(this).attr('name');
        
        // Hide all targets for this radio group
        $(`input[name="${name}"][data-toggle-target]`).each(function() {
            const hideTarget = $(this).data('toggle-target');
            $(hideTarget).slideUp().addClass('hidden');
        });
        
        // Show target for selected radio
        if ($(this).is(':checked')) {
            $target.removeClass('hidden').slideDown();
        }
    });
    
    // Select toggles
    $('select[data-toggle-target]').on('change', function() {
        const $select = $(this);
        const toggleConfig = $select.data('toggle-config');
        
        // Hide all targets first
        Object.values(toggleConfig || {}).forEach(target => {
            $(target).slideUp().addClass('hidden');
        });
        
        // Show target for selected value
        const selectedValue = $select.val();
        if (toggleConfig && toggleConfig[selectedValue]) {
            $(toggleConfig[selectedValue]).removeClass('hidden').slideDown();
        }
    });
}

/**
 * Initialize dynamic fields
 */
function initializeDynamicFields() {
    // Add field button
    $('.add-field').on('click', function() {
        const $container = $($(this).data('container'));
        const template = $(this).data('template');
        const $template = $(template);
        
        if ($template.length) {
            const newField = $template.html();
            const index = $container.children().length;
            
            // Replace placeholders with actual index
            const processedField = newField.replace(/\[INDEX\]/g, `[${index}]`)
                                           .replace(/INDEX/g, index);
            
            $container.append(processedField);
            
            // Reinitialize plugins for new field
            reinitializePlugins($container.children().last());
        }
    });
    
    // Remove field button
    $(document).on('click', '.remove-field', function() {
        const $field = $(this).closest('.dynamic-field');
        
        Swal.fire({
            title: 'ยืนยันการลบ',
            text: 'คุณแน่ใจหรือไม่ที่จะลบฟิลด์นี้?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก',
            customClass: {
                popup: 'font-sans'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $field.slideUp(() => $field.remove());
            }
        });
    });
}

/**
 * Initialize auto-save
 */
function initializeAutoSave() {
    $('.auto-save-form').each(function() {
        const $form = $(this);
        const interval = $form.data('auto-save-interval') || 30000; // 30 seconds default
        
        setInterval(() => {
            autoSaveForm($form);
        }, interval);
        
        // Save on form change
        $form.on('change input', debounce(() => {
            autoSaveForm($form);
        }, 2000));
    });
}

/**
 * Auto save form data
 */
function autoSaveForm($form) {
    const formData = new FormData($form[0]);
    formData.append('auto_save', '1');
    formData.append('csrf_token', $('meta[name="csrf-token"]').attr('content'));
    
    $.ajax({
        url: $form.attr('action') || window.location.href,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                showAutoSaveNotification('บันทึกอัตโนมัติเรียบร้อย');
            }
        },
        error: function() {
            showAutoSaveNotification('ไม่สามารถบันทึกอัตโนมัติได้', 'error');
        }
    });
}

/**
 * Show auto-save notification
 */
function showAutoSaveNotification(message, type = 'success') {
    const $notification = $('#autoSaveNotification');
    
    if ($notification.length === 0) {
        $('body').append(`
            <div id="autoSaveNotification" class="fixed bottom-4 right-4 z-50 hidden">
                <div class="bg-white border border-gray-300 rounded-lg shadow-lg p-3">
                    <div class="flex items-center">
                        <i class="fas fa-save text-green-500 mr-2"></i>
                        <span class="text-sm text-gray-700"></span>
                    </div>
                </div>
            </div>
        `);
    }
    
    const iconClass = type === 'success' ? 'fa-save text-green-500' : 'fa-exclamation-triangle text-red-500';
    
    $('#autoSaveNotification')
        .find('i').removeClass().addClass(`fas ${iconClass}`)
        .siblings('span').text(message);
    
    $('#autoSaveNotification').removeClass('hidden');
    
    setTimeout(() => {
        $('#autoSaveNotification').addClass('hidden');
    }, 3000);
}

/**
 * Initialize form dependencies
 */
function initializeFormDependencies() {
    // Category dependencies
    $('.category-select').on('change', function() {
        const categoryId = $(this).val();
        const $subcategorySelect = $('.subcategory-select');
        
        if (categoryId) {
            loadSubcategories(categoryId, $subcategorySelect);
        } else {
            $subcategorySelect.empty().append('<option value="">เลือกหมวดหมู่ย่อย</option>');
        }
    });
    
    // Province/District/Subdistrict dependencies
    $('.province-select').on('change', function() {
        const provinceId = $(this).val();
        const $districtSelect = $('.district-select');
        const $subdistrictSelect = $('.subdistrict-select');
        
        $districtSelect.empty().append('<option value="">เลือกอำเภอ</option>');
        $subdistrictSelect.empty().append('<option value="">เลือกตำบล</option>');
        
        if (provinceId) {
            loadDistricts(provinceId, $districtSelect);
        }
    });
    
    $('.district-select').on('change', function() {
        const districtId = $(this).val();
        const $subdistrictSelect = $('.subdistrict-select');
        
        $subdistrictSelect.empty().append('<option value="">เลือกตำบล</option>');
        
        if (districtId) {
            loadSubdistricts(districtId, $subdistrictSelect);
        }
    });
}

/**
 * Load subcategories
 */
function loadSubcategories(categoryId, $select) {
    $select.empty().append('<option value="">กำลังโหลด...</option>');
    
    $.ajax({
        url: BASE_URL + '/admin/api/subcategories.php',
        method: 'GET',
        data: {
            category_id: categoryId,
            csrf_token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $select.empty().append('<option value="">เลือกหมวดหมู่ย่อย</option>');
            
            if (response.success && response.data.length > 0) {
                response.data.forEach(item => {
                    $select.append(`<option value="${item.id}">${item.name}</option>`);
                });
            }
        },
        error: function() {
            $select.empty().append('<option value="">เกิดข้อผิดพลาด</option>');
        }
    });
}

/**
 * Initialize file uploads
 */
function initializeFileUploads() {
    // Drag and drop file upload
    $('.file-upload-area').each(function() {
        const $area = $(this);
        const $input = $area.find('input[type="file"]');
        const $preview = $area.find('.file-preview');
        const multiple = $input.prop('multiple');
        const accept = $input.attr('accept');
        
        // Drag and drop events
        $area.on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('border-blue-500 bg-blue-50');
        });
        
        $area.on('dragleave', function(e) {
            e.preventDefault();
            $(this).removeClass('border-blue-500 bg-blue-50');
        });
        
        $area.on('drop', function(e) {
            e.preventDefault();
            $(this).removeClass('border-blue-500 bg-blue-50');
            
            const files = e.originalEvent.dataTransfer.files;
            handleFileSelection(files, $input, $preview, multiple, accept);
        });
        
        // File input change
        $input.on('change', function() {
            const files = this.files;
            handleFileSelection(files, $input, $preview, multiple, accept);
        });
        
        // Click to select files
        $area.on('click', function(e) {
            if (e.target === this || $(e.target).hasClass('upload-text')) {
                $input.click();
            }
        });
    });
    
    // Remove file button
    $(document).on('click', '.remove-file', function() {
        const $fileItem = $(this).closest('.file-item');
        const $input = $fileItem.closest('.file-upload-area').find('input[type="file"]');
        
        $fileItem.remove();
        
        // Reset input if no files left
        if ($fileItem.siblings('.file-item').length === 0) {
            $input.val('');
        }
    });
}

/**
 * Handle file selection
 */
function handleFileSelection(files, $input, $preview, multiple, accept) {
    const maxSize = $input.data('max-size') || 10 * 1024 * 1024; // 10MB default
    const allowedTypes = accept ? accept.split(',').map(type => type.trim()) : [];
    
    if (!multiple) {
        $preview.empty();
    }
    
    Array.from(files).forEach(file => {
        // Validate file size
        if (file.size > maxSize) {
            showAlert(`ไฟล์ "${file.name}" มีขนาดใหญ่เกินไป (สูงสุด ${formatFileSize(maxSize)})`, 'error');
            return;
        }
        
        // Validate file type
        if (allowedTypes.length > 0) {
            const isValidType = allowedTypes.some(type => {
                if (type.startsWith('.')) {
                    return file.name.toLowerCase().endsWith(type.toLowerCase());
                } else {
                    return file.type.match(type);
                }
            });
            
            if (!isValidType) {
                showAlert(`ไฟล์ "${file.name}" ไม่ใช่ประเภทที่รองรับ`, 'error');
                return;
            }
        }
        
        // Create file preview
        createFilePreview(file, $preview);
    });
    
    // Update input files (for single file upload)
    if (!multiple && files.length > 0) {
        const dt = new DataTransfer();
        dt.items.add(files[0]);
        $input[0].files = dt.files;
    }
}

/**
 * Create file preview
 */
function createFilePreview(file, $preview) {
    const isImage = file.type.startsWith('image/');
    const fileSize = formatFileSize(file.size);
    
    const $fileItem = $(`
        <div class="file-item flex items-center p-3 border border-gray-200 rounded-lg mb-2">
            <div class="file-icon mr-3">
                ${isImage ? 
                    '<i class="fas fa-image text-blue-500 text-xl"></i>' : 
                    '<i class="fas fa-file text-gray-500 text-xl"></i>'
                }
            </div>
            <div class="file-info flex-1">
                <div class="file-name text-sm font-medium text-gray-900">${file.name}</div>
                <div class="file-size text-xs text-gray-500">${fileSize}</div>
            </div>
            <button type="button" class="remove-file ml-3 text-red-500 hover:text-red-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `);
    
    // Add image preview
    if (isImage) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $fileItem.find('.file-icon').html(`
                <img src="${e.target.result}" alt="${file.name}" class="w-10 h-10 object-cover rounded">
            `);
        };
        reader.readAsDataURL(file);
    }
    
    $preview.append($fileItem);
}

/**
 * Initialize rich text editors
 */
function initializeRichTextEditors() {
    $('.rich-editor').each(function() {
        const $textarea = $(this);
        const toolbar = $textarea.data('toolbar') || 'basic';
        
        // Initialize TinyMCE or similar editor
        // This is a placeholder - you would integrate your preferred editor here
        
        // Simple toolbar for basic editing
        if (toolbar === 'basic') {
            $textarea.addClass('h-32 p-3 border border-gray-300 rounded-md');
        }
    });
}

/**
 * Initialize form wizards
 */
function initializeFormWizards() {
    $('.form-wizard').each(function() {
        const $wizard = $(this);
        const $steps = $wizard.find('.wizard-step');
        const $nextBtn = $wizard.find('.wizard-next');
        const $prevBtn = $wizard.find('.wizard-prev');
        const $submitBtn = $wizard.find('.wizard-submit');
        let currentStep = 0;
        
        // Initialize wizard
        showStep(currentStep);
        
        // Next button
        $nextBtn.on('click', function() {
            if (validateStep(currentStep)) {
                currentStep++;
                showStep(currentStep);
            }
        });
        
        // Previous button
        $prevBtn.on('click', function() {
            currentStep--;
            showStep(currentStep);
        });
        
        // Step navigation
        $wizard.find('.wizard-nav .step').on('click', function() {
            const step = $(this).data('step');
            if (step <= currentStep || validateAllPreviousSteps(step)) {
                currentStep = step;
                showStep(currentStep);
            }
        });
        
        function showStep(step) {
            $steps.addClass('hidden');
            $steps.eq(step).removeClass('hidden');
            
            // Update navigation
            $wizard.find('.wizard-nav .step').removeClass('active completed');
            $wizard.find('.wizard-nav .step').each(function(index) {
                if (index < step) {
                    $(this).addClass('completed');
                } else if (index === step) {
                    $(this).addClass('active');
                }
            });
            
            // Update buttons
            $prevBtn.toggle(step > 0);
            $nextBtn.toggle(step < $steps.length - 1);
            $submitBtn.toggle(step === $steps.length - 1);
        }
        
        function validateStep(step) {
            const $step = $steps.eq(step);
            const $requiredFields = $step.find('[required]');
            let isValid = true;
            
            $requiredFields.each(function() {
                if (!this.checkValidity()) {
                    isValid = false;
                    $(this).addClass('border-red-500');
                } else {
                    $(this).removeClass('border-red-500');
                }
            });
            
            if (!isValid) {
                showAlert('กรุณากรอกข้อมูลให้ครบถ้วน', 'error');
            }
            
            return isValid;
        }
        
        function validateAllPreviousSteps(targetStep) {
            for (let i = 0; i < targetStep; i++) {
                if (!validateStep(i)) {
                    return false;
                }
            }
            return true;
        }
    });
}

/**
 * Reinitialize plugins for new elements
 */
function reinitializePlugins($element) {
    // Reinitialize Select2
    $element.find('.select2').select2({
        width: '100%'
    });
    
    // Reinitialize date pickers
    $element.find('.datepicker').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true
    });
    
    // Reinitialize other plugins as needed
}

/**
 * Utility functions
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function showAlert(message, type) {
    if (window.AdminJS && window.AdminJS.showAlert) {
        window.AdminJS.showAlert(message, type);
    }
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}