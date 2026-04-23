
http://127.0.0.1:8000/teacher/ho-so@extends('layouts.teacher')

@section('style')
<style>
    /* SIMPLE & FRIENDLY UI FOR TEACHERS */
    .support-container {
        max-width: 1000px;
        margin: 0 auto;
        padding-bottom: 3rem;
    }

    .support-card {
        background: var(--admin-surface-2);
        border: 1px solid var(--admin-sidebar-border);
        border-radius: 1.25rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    /* Clear Tabs */
    .support-nav-pills {
        display: flex;
        background: var(--admin-surface-3);
        padding: 0.5rem;
        border-bottom: 1px solid var(--admin-sidebar-border);
    }
    .support-nav-pills .nav-item {
        flex: 1;
    }
    .support-nav-pills .nav-link {
        width: 100%;
        text-align: center;
        padding: 1rem;
        border-radius: 0.75rem;
        font-weight: 700;
        font-size: 1rem;
        color: var(--admin-muted);
        border: none !important;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
    }
    .support-nav-pills .nav-link:hover {
        color: var(--admin-primary);
    }
    .support-nav-pills .nav-link.active {
        background: var(--admin-primary) !important;
        color: white !important;
    }

    /* Friendly Form - Fixing the "clumping" issue */
    .form-group-custom {
        margin-bottom: 2rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .form-label-friendly {
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--admin-text) !important;
        margin: 0 !important;
        display: block !important;
    }
    .form-hint {
        font-size: 0.9rem;
        color: var(--admin-muted) !important;
        margin: 0 !important;
        display: block !important;
        font-weight: 500;
    }

    /* High Contrast Inputs */
    .input-friendly {
        background: var(--admin-surface-1) !important;
        border: 1.5px solid var(--admin-sidebar-border) !important;
        border-radius: 10px !important;
        padding: 0.85rem 1rem !important;
        color: var(--admin-text) !important;
        font-size: 1rem !important;
        width: 100% !important;
    }

    /* CKEditor Custom Chrome - Hard Overrides */
    .cke_chrome {
        border: 1.5px solid var(--admin-sidebar-border) !important;
        border-radius: 12px !important;
        background: var(--admin-surface-1) !important;
        box-shadow: none !important;
    }
    
    /* Dark Mode specific overrides for CKEditor UI */
    html[data-theme="dark"] .cke_inner,
    html[data-theme="dark"] .cke_top, 
    html[data-theme="dark"] .cke_bottom {
        background: #1f2937 !important;
        border-color: #374151 !important;
    }
    html[data-theme="dark"] .cke_button_icon {
        filter: invert(1) brightness(2) !important;
    }
    html[data-theme="dark"] .cke_combo_button {
        background: #374151 !important;
        border-color: #4b5563 !important;
        box-shadow: none !important;
    }
    html[data-theme="dark"] .cke_combo_text {
        color: #f9fafb !important;
    }
    html[data-theme="dark"] .cke_path_item, 
    html[data-theme="dark"] .cke_path_empty {
        color: #9ca3af !important;
    }
    /* Fixed: Source view text color in dark mode */
    html[data-theme="dark"] .cke_source {
        background-color: #111827 !important;
        color: #f9fafb !important;
    }

    /* Loader */
    .loading-overlay {
        position: relative;
        pointer-events: none;
    }
    .loading-overlay::after {
        content: "";
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(var(--admin-surface-2-rgb), 0.7);
        z-index: 999;
        backdrop-filter: blur(2px);
    }
</style>
@endsection

@section('content')
<div class="support-container">
    <div class="d-flex align-items-center mb-4">
        <div class="bg-primary-subtle p-3 rounded-4 me-3">
            <i class="fas fa-life-ring fa-2x text-primary"></i>
        </div>
        <div>
            <h2 class="fw-bold mb-0">{{ __('contacts::teacher.support.page_title') }}</h2>
            <p class="text-muted mb-0">{{ __('contacts::teacher.support.description') }}</p>
        </div>
    </div>

    <div class="support-card">
        <div class="nav nav-pills support-nav-pills" id="supportTabs" role="tablist">
            <button class="nav-link active" id="new-request-tab" data-bs-toggle="tab" data-bs-target="#new-request" type="button" role="tab">
                <i class="fas fa-edit"></i> {{ __('contacts::teacher.support.form.title') }}
            </button>
            <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab">
                <i class="fas fa-list-ul"></i> {{ __('contacts::teacher.support.history.title') }}
            </button>
        </div>

        <div class="tab-content" id="supportTabsContent">
            <!-- Form Tab -->
            <div class="tab-pane fade show active p-4 p-lg-5" id="new-request" role="tabpanel">
                <form id="supportForm" method="POST" action="{{ route('teacher.dashboard.support.store') }}">
                    @csrf
                    <input type="hidden" name="page_url" value="{{ url()->current() }}">

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="form-group-custom">
                                <label class="form-label-friendly">{{ __('contacts::teacher.support.form.type_label') }}</label>
                                <span class="form-hint">{{ __('contacts::teacher.support.form.type_hint') }}</span>
                                <select name="submission_type" class="form-select input-friendly">
                                    <option value="feedback" @selected(old('submission_type', 'feedback') === 'feedback')>{{ __('contacts::teacher.support.types.feedback') }}</option>
                                    <option value="report" @selected(old('submission_type') === 'report')>{{ __('contacts::teacher.support.types.report') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="form-group-custom">
                                <label class="form-label-friendly">{{ __('contacts::teacher.support.form.category_label') }}</label>
                                <span class="form-hint">{{ __('contacts::teacher.support.form.category_hint') }}</span>
                                <select name="category" class="form-select input-friendly">
                                    <option value="">{{ __('contacts::teacher.support.form.category_select_default') }}</option>
                                    @foreach (__('contacts::teacher.support.categories') as $value => $label)
                                        <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback d-block fw-bold mt-1" id="error-category"></div>
                            </div>
                        </div>

                        <div class="col-12 mb-4">
                            <div class="form-group-custom">
                                <label class="form-label-friendly">{{ __('contacts::teacher.support.form.subject_label') }}</label>
                                <span class="form-hint">{{ __('contacts::teacher.support.form.subject_hint') }}</span>
                                <div class="ckeditor-wrapper">
                                    <textarea name="subject" id="support-subject">{{ old('subject') }}</textarea>
                                </div>
                                <div class="invalid-feedback d-block fw-bold mt-2" id="error-subject"></div>
                            </div>
                        </div>

                        <div class="col-12 mb-4">
                            <div class="form-group-custom">
                                <label class="form-label-friendly">{{ __('contacts::teacher.support.form.message_label') }}</label>
                                <span class="form-hint">{{ __('contacts::teacher.support.form.message_hint') }}</span>
                                <div class="ckeditor-wrapper">
                                    <textarea name="message" id="support-message">{{ old('message') }}</textarea>
                                </div>
                                <div class="invalid-feedback d-block fw-bold mt-2" id="error-message"></div>
                            </div>
                        </div>

                        <div class="col-12 mt-2">
                            <button type="submit" id="btnSubmit" class="btn-send-support btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i> {{ __('contacts::teacher.support.form.submit') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- History Tab -->
            <div class="tab-pane fade p-4" id="history" role="tabpanel">
                <div id="history-container">
                    @include('contacts::teacher.partials.support_history', ['items' => $items])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('backend/plugins/ckeditor/ckeditor.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    /**
     * CKEDITOR DARK MODE SYNC - IMPROVED
     */
    function syncCKEditorTheme(editor) {
        if (!editor || !editor.document) return;
        
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const doc = editor.document;
        const body = doc.getBody();
        
        if (body) {
            if (isDark) {
                body.setStyle('background-color', '#111827');
                body.setStyle('color', '#f1f5f9');
            } else {
                body.setStyle('background-color', '#ffffff');
                body.setStyle('color', '#111827');
            }
            body.setStyle('font-family', '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif');
            body.setStyle('font-size', '16px');
        }
    }

    function applyCKEditorDarkMode(editor) {
        if (!editor) return;

        editor.on('instanceReady', function() {
            syncCKEditorTheme(editor);
            
            // Watch for theme changes
            const observer = new MutationObserver(() => syncCKEditorTheme(editor));
            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof CKEDITOR === 'undefined') return;

        // Disable automatic instantiation
        CKEDITOR.disableAutoInline = true;

        const commonConfig = {
            removePlugins: 'elementspath,exportpdf,uploadimage,uploadfile,easyimage,ckeditorcloudservices',
            resize_enabled: false,
            enterMode: CKEDITOR.ENTER_P,
            shiftEnterMode: CKEDITOR.ENTER_BR
        };

        // Initialize Support Subject
        if (CKEDITOR.instances['support-subject']) {
            CKEDITOR.instances['support-subject'].destroy(true);
        }
        const subjectEditor = CKEDITOR.replace('support-subject', {
            ...commonConfig,
            height: 80,
            toolbar: [
                { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline' ] },
                { name: 'colors', items: [ 'TextColor' ] }
            ]
        });
        applyCKEditorDarkMode(subjectEditor);

        // Initialize Support Message
        if (CKEDITOR.instances['support-message']) {
            CKEDITOR.instances['support-message'].destroy(true);
        }
        const messageEditor = CKEDITOR.replace('support-message', {
            ...commonConfig,
            height: 300,
            toolbar: [
                { name: 'document', items: [ 'Source', '-', 'Maximize' ] },
                { name: 'clipboard', items: [ 'Undo', 'Redo' ] },
                { name: 'styles', items: [ 'Format', 'Font', 'FontSize' ] },
                { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
                { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
                { name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
                { name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
                { name: 'insert', items: [ 'Image', 'Table', 'HorizontalRule', 'SpecialChar' ] }
            ]
        });
        applyCKEditorDarkMode(messageEditor);

        // AJAX SUBMISSION
        const form = document.querySelector('#supportForm');
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            for (let instance in CKEDITOR.instances) CKEDITOR.instances[instance].updateElement();

            // FRONT-END VALIDATION
            let hasError = false;
            const subjectVal = CKEDITOR.instances['support-subject'].getData().replace(/<[^>]*>/g, '').trim();
            const messageVal = CKEDITOR.instances['support-message'].getData().replace(/<[^>]*>/g, '').trim();
            const categoryVal = form.querySelector('select[name="category"]').value;

            // Reset errors
            document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
            document.querySelectorAll('.cke_chrome').forEach(el => el.style.borderColor = 'var(--admin-sidebar-border)');
            document.querySelectorAll('.input-friendly').forEach(el => el.style.borderColor = 'var(--admin-sidebar-border)');

            if (!categoryVal) {
                document.querySelector('#error-category').textContent = 'Vui lòng chọn danh mục hỗ trợ.';
                form.querySelector('select[name="category"]').style.borderColor = '#ef4444';
                hasError = true;
            }

            if (!subjectVal) {
                document.querySelector('#error-subject').textContent = 'Tiêu đề không được để trống.';
                document.querySelector('#cke_support-subject').style.borderColor = '#ef4444';
                hasError = true;
            }

            if (!messageVal) {
                document.querySelector('#error-message').textContent = 'Nội dung chi tiết không được để trống.';
                document.querySelector('#cke_support-message').style.borderColor = '#ef4444';
                hasError = true;
            }

            if (hasError) {
                Swal.fire({ icon: 'warning', title: 'Thông tin chưa đầy đủ', text: 'Vui lòng nhập đầy đủ yêu cầu trước khi gửi.' });
                return;
            }

            const formData = new FormData(form);
            const btnSubmit = document.querySelector('#btnSubmit');
            const card = document.querySelector('.support-card');
            
            // CSRF Token Safety
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.content : '';

            // Reset errors
            document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
            document.querySelectorAll('.cke_chrome').forEach(el => el.style.borderColor = 'var(--admin-sidebar-border)');
            
            card.classList.add('loading-overlay');
            btnSubmit.disabled = true;

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                card.classList.remove('loading-overlay');
                btnSubmit.disabled = false;

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Đã gửi thành công!',
                        text: data.message,
                        confirmButtonText: 'Đóng',
                        confirmButtonColor: 'var(--admin-primary)'
                    });
                    
                    form.reset();
                    for (let instance in CKEDITOR.instances) CKEDITOR.instances[instance].setData('');
                    
                    new bootstrap.Tab(document.querySelector('#history-tab')).show();
                    refreshHistory();
                } else if (data.errors) {
                    Object.keys(data.errors).forEach(key => {
                        const errorEl = document.querySelector(`#error-${key}`);
                        if (errorEl) {
                            errorEl.textContent = data.errors[key][0];
                            errorEl.style.color = '#ef4444';
                            errorEl.style.fontSize = '0.9rem';
                        }
                        
                        // Highlight CKEditor border if it's subject or message
                        if (key === 'subject' || key === 'message') {
                            const ckInstance = CKEDITOR.instances[`support-${key}`];
                            if (ckInstance) {
                                document.querySelector(`#cke_support-${key}`).style.borderColor = '#ef4444';
                            }
                        }
                    });
                    
                    Swal.fire({
                        icon: 'warning',
                        title: 'Thông tin chưa đầy đủ',
                        text: 'Vui lòng kiểm tra lại các trường thông tin bị lỗi màu đỏ.',
                        confirmButtonColor: '#f59e0b'
                    });
                }
            })
            .catch((err) => {
                card.classList.remove('loading-overlay');
                btnSubmit.disabled = false;
                console.error(err);
                Swal.fire({ icon: 'error', title: 'Lỗi hệ thống', text: 'Không thể kết nối đến máy chủ. Vui lòng thử lại.' });
            });
        });

        function refreshHistory(url = "{{ route('teacher.dashboard.support') }}") {
            const container = document.querySelector('#history-container');
            container.classList.add('loading-overlay');
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => response.text())
            .then(html => {
                container.innerHTML = html;
                container.classList.remove('loading-overlay');
                bindPagination();
            });
        }

        function bindPagination() {
            document.querySelectorAll('.ajax-pagination a').forEach(link => {
                link.addEventListener('click', function(e) { e.preventDefault(); refreshHistory(this.href); });
            });
        }
        bindPagination();
    });
</script>
@endsection
