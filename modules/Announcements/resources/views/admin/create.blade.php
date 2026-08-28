@extends('layouts.backend')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="mb-1">Soạn thông báo hệ thống</h5>
                <p class="text-muted mb-0">Gửi thông báo và email đến học viên/giảng viên.</p>
            </div>
            <a href="{{ route('admin.announcements.index') }}" class="btn btn-light border">Quay lại</a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">Vui lòng kiểm tra lại dữ liệu đã nhập.</div>
        @endif

        <form action="{{ route('admin.announcements.store') }}" method="POST">
            @csrf
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="mb-3">
                        <label class="form-label">Tiêu đề thông báo</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="Ví dụ: Cập nhật điều khoản sử dụng mới" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tóm tắt ngắn (Hiển thị ở thông báo nhanh)</label>
                        <textarea name="message" class="form-control" rows="3" required placeholder="Nội dung ngắn gọn để gửi qua Notify...">{{ old('message') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nội dung chi tiết (Trang đọc thông báo)</label>
                        <textarea name="content" class="form-control ckeditor" rows="10">{{ old('content') }}</textarea>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border bg-light mb-4">
                        <div class="card-body">
                            <h6 class="mb-3">Đối tượng nhận tin</h6>
                            
                            <div class="mb-3">
                                <label class="form-label">Chọn nhóm đối tượng</label>
                                <select name="target_type" id="target_type" class="form-select">
                                    <option value="all" @selected(old('target_type') == 'all')>Tất cả (Học viên & Giảng viên)</option>
                                    <option value="all_students" @selected(old('target_type') == 'all_students')>Chỉ học viên</option>
                                    <option value="all_teachers" @selected(old('target_type') == 'all_teachers')>Chỉ giảng viên</option>
                                    <option value="selected" @selected(old('target_type') == 'selected')>Cá nhân cụ thể</option>
                                </select>
                            </div>

                            <div id="user_selector_wrapper" class="mb-3" style="display: none;">
                                <label class="form-label">Chọn người nhận</label>
                                <select name="selected_user_ids[]" id="user_search" class="form-control" multiple></select>
                                <small class="text-muted">Tìm kiếm theo tên hoặc email.</small>
                            </div>

                            <hr>

                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="send_email" id="send_email" value="1" @checked(old('send_email'))>
                                <label class="form-check-label" for="send_email">Đồng thời gửi Email</label>
                            </div>
                        </div>
                    </div>

                    <div class="card border bg-light mb-4">
                        <div class="card-body">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="enable_action_link" @checked(old('action_url'))>
                                <label class="form-check-label fw-bold" for="enable_action_link">Thêm liên kết hành động</label>
                            </div>
                            
                            <div id="action_link_wrapper" style="{{ old('action_url') ? '' : 'display: none;' }}">
                                <div class="mb-3">
                                    <label class="form-label">Đường dẫn (URL)</label>
                                    <input type="text" name="action_url" class="form-control" value="{{ old('action_url') }}" placeholder="https://...">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nhãn nút bấm</label>
                                    <input type="text" name="action_label" class="form-control" value="{{ old('action_label') }}" placeholder="Xem ngay">
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="fas fa-paper-plane me-2"></i> Gửi thông báo ngay
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('stylesheets')
<style>
    .select2-container--default .select2-selection--multiple {
        border: 1px solid var(--admin-border, #dee2e6) !important;
        border-radius: 0.375rem !important;
        min-height: 42px !important;
        background-color: var(--admin-input-bg, #fff) !important;
    }
    .select2-container {
        width: 100% !important;
        display: block;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: var(--admin-surface-3, #e9ecef) !important;
        border: 1px solid var(--admin-border, #dee2e6) !important;
        color: var(--admin-text, #333) !important;
        padding: 2px 8px !important;
        border-radius: 4px !important;
        margin-top: 6px !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: var(--admin-text, #333) !important;
        margin-right: 5px !important;
        border-right: 1px solid var(--admin-border, #dee2e6) !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        background-color: transparent !important;
        color: var(--admin-primary, #007bff) !important;
    }
    .select2-container--default .select2-search--inline .select2-search__field {
        color: var(--admin-text, #333) !important;
        background: transparent !important;
    }
    .select2-dropdown {
        background-color: var(--admin-surface, #fff) !important;
        border: 1px solid var(--admin-border, #dee2e6) !important;
        color: var(--admin-text, #333) !important;
        z-index: 9999 !important;
    }
    .select2-results__option {
        color: var(--admin-text, #333) !important;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: var(--admin-primary, #007bff) !important;
        color: #fff !important;
    }
    .select2-container--default .select2-results__option[aria-selected="true"] {
        background-color: var(--admin-surface-2, #f8f9fa) !important;
    }
</style>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        function toggleUserSelector() {
            if ($('#target_type').val() === 'selected') {
                $('#user_selector_wrapper').show();
                // Force Select2 to recalculate width
                $('#user_search').select2({
                    placeholder: 'Tìm kiếm người nhận...',
                    minimumInputLength: 2,
                    width: '100%',
                    ajax: {
                        url: '{{ route("admin.announcements.search-users") }}',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: data.items
                            };
                        },
                        cache: true
                    }
                });
            } else {
                $('#user_selector_wrapper').hide();
            }
        }

        $('#target_type').on('change', toggleUserSelector);
        toggleUserSelector();

        $('#enable_action_link').on('change', function() {
            if ($(this).is(':checked')) {
                $('#action_link_wrapper').slideDown();
            } else {
                $('#action_link_wrapper').slideUp();
                $('#action_link_wrapper input').val('');
            }
        });
    });
</script>
@endsection
