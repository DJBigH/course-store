@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="admin-page-actions">
                <div>
                    <h5 class="mb-1">{{ ($mode ?? 'contact') === 'support' ? 'Danh sách góp ý / báo cáo' : 'Danh sách liên hệ' }}</h5>
                    <p class="text-muted mb-0">
                        {{ ($mode ?? 'contact') === 'support'
                            ? 'Theo dõi góp ý phát triển và báo cáo sự cố gửi đến admin.'
                            : 'Theo dõi các yêu cầu liên hệ và tư vấn từ người dùng.' }}
                    </p>
                </div>
                @if (auth()->user()?->canAnyPermission(['contacts.soft_delete', 'contacts.delete', 'contacts.force_delete']))
                    <a href="{{ route('contacts.trash') }}" class="btn btn-light border">
                        <i class="fa-solid fa-trash-can me-2"></i>
                        Thùng rác
                    </a>
                @endif
            </div>

            @if (session('msg'))
                <div class="alert alert-success border-0 rounded-4">{{ session('msg') }}</div>
            @endif
            @if (session('msg_danger'))
                <div class="alert alert-danger border-0 rounded-4">{{ session('msg_danger') }}</div>
            @endif
            @if ($errors->has('bulk_action'))
                <div class="alert alert-danger border-0 rounded-4">{{ $errors->first('bulk_action') }}</div>
            @endif

            <form id="contact-filter-form" class="admin-filter-panel mb-4">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Từ khóa</label>
                        <input type="text" class="form-control" name="q" id="filter-q" placeholder="Tên, email, tiêu đề, nội dung...">
                    </div>
                    @if (($mode ?? 'contact') === 'support')
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">Loại gửi</label>
                            <select class="form-select" name="submission_type" id="filter-type">
                                <option value="">Tất cả</option>
                                <option value="feedback">Góp ý</option>
                                <option value="report">Báo cáo</option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">Danh mục</label>
                            <select class="form-select" name="category" id="filter-category">
                                <option value="">Tất cả</option>
                                <option value="feature_request">Tính năng mới</option>
                                <option value="ui_ux">UI/UX</option>
                                <option value="teacher_portal">Teacher portal</option>
                                <option value="student_portal">Student portal</option>
                                <option value="payment_package">Thanh toán / gói</option>
                                <option value="system_bug">Lỗi hệ thống</option>
                                <option value="course_lesson">Khóa học / bài học</option>
                                <option value="comment_rating">Bình luận / đánh giá</option>
                                <option value="content_violation">Nội dung vi phạm</option>
                                <option value="account">Tài khoản</option>
                                <option value="other">Khác</option>
                            </select>
                        </div>
                    @endif
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">Trạng thái</label>
                        <select class="form-select" name="workflow_status" id="filter-status">
                            <option value="">Tất cả</option>
                            <option value="new">Mới gửi</option>
                            <option value="in_progress">Đang xử lý</option>
                            <option value="need_info">Cần thêm thông tin</option>
                            <option value="resolved">Đã giải quyết</option>
                            <option value="rejected">Đã từ chối</option>
                        </select>
                    </div>
                    <div class="col-lg-1 col-md-6">
                        <label class="form-label">Từ ngày</label>
                        <input type="date" class="form-control" name="from_date" id="filter-from-date">
                    </div>
                    <div class="col-lg-1 col-md-6">
                        <label class="form-label">Đến ngày</label>
                        <input type="date" class="form-control" name="to_date" id="filter-to-date">
                    </div>
                    <div class="col-lg-1 col-md-12 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Lọc</button>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="button" class="btn btn-light border" id="reset-filters">Xóa lọc</button>
                </div>
            </form>

            @if (auth()->user()?->canAnyPermission(['contacts.update', 'contacts.soft_delete', 'contacts.delete']))
                <form id="bulk-action-form" action="{{ route('contacts.bulk') }}" method="POST" class="mb-4">
                    @csrf
                    <input type="hidden" name="selected_ids" id="selected-ids">
                    <input type="hidden" name="bulk_action" id="bulk-action-input">

                    <div class="bulk-toolbar">
                        <div class="bulk-toolbar__summary">
                            <span id="selected-count">0</span> yêu cầu được chọn
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @if (auth()->user()?->hasPermission('contacts.update'))
                                <button type="button" class="btn btn-success bulk-action-trigger" data-action="accept">Đánh dấu đang xử lý</button>
                            @endif
                            @if (auth()->user()?->canAnyPermission(['contacts.soft_delete', 'contacts.delete']))
                                <button type="button" class="btn btn-outline-danger bulk-action-trigger" data-action="delete">Xóa</button>
                            @endif
                        </div>
                    </div>
                </form>
            @endif

            <div class="table-responsive">
                <table id="datatable" class="table align-middle w-100">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 48px;">
                                <input type="checkbox" id="select-all-records" class="form-check-input">
                            </th>
                            <th>Người gửi</th>
                            @if (($mode ?? 'contact') === 'support')
                                <th>Loại</th>
                                <th>Danh mục</th>
                            @endif
                            <th>Số điện thoại</th>
                            <th>Email</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th>Lịch sử</th>
                            <th>Xem</th>
                            <th>Xóa</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    @include('part.backend.delete')
@endsection

@section('stylesheets')
    <style>
        .admin-filter-panel {
            padding: 1.1rem;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .bulk-toolbar {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.1rem;
            border-radius: 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .bulk-toolbar__summary {
            font-weight: 600;
            color: #334155;
        }

        html[data-theme="dark"] .admin-filter-panel {
            background: linear-gradient(180deg, #162033 0%, #111827 100%);
            border-color: #2b3b53;
        }

        html[data-theme="dark"] .bulk-toolbar {
            background: #162033;
            border-color: #2b3b53;
        }

        html[data-theme="dark"] .bulk-toolbar__summary,
        html[data-theme="dark"] #datatable tbody td,
        html[data-theme="dark"] #datatable tbody a {
            color: #cbd5e1;
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            const selectedIds = new Set();

            const table = $('#datatable').DataTable({
                autoWidth: false,
                processing: true,
                serverSide: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                ajax: {
                    url: "{{ route('contacts.data') }}",
                    data: function(d) {
                        d.q = $('#filter-q').val();
                        d.submission_type = $('#filter-type').val();
                        d.category = $('#filter-category').val();
                        d.workflow_status = $('#filter-status').val();
                        d.from_date = $('#filter-from-date').val();
                        d.to_date = $('#filter-to-date').val();
                    }
                },
                columns: [
                    { data: 'select', orderable: false, searchable: false },
                    { data: 'name' },
                    @if (($mode ?? 'contact') === 'support')
                    { data: 'submission_type' },
                    { data: 'category' },
                    @endif
                    { data: 'phone' },
                    { data: 'email' },
                    { data: 'status' },
                    { data: 'created_at' },
                    { data: 'logs' },
                    { data: 'view' },
                    { data: 'delete' }
                ],
                drawCallback: function() {
                    syncCheckboxState();
                }
            });

            function syncCheckboxState() {
                $('.bulk-row-checkbox').each(function() {
                    $(this).prop('checked', selectedIds.has($(this).val()));
                });

                $('#selected-count').text(selectedIds.size);

                const visibleCheckboxes = $('.bulk-row-checkbox');
                const checkedVisible = visibleCheckboxes.filter(':checked').length;
                $('#select-all-records').prop('checked', visibleCheckboxes.length > 0 && visibleCheckboxes.length === checkedVisible);
            }

            $('#contact-filter-form').on('submit', function(event) {
                event.preventDefault();
                table.ajax.reload();
            });

            $('#reset-filters').on('click', function() {
                $('#contact-filter-form')[0].reset();
                table.ajax.reload();
            });

            $('#datatable').on('change', '.bulk-row-checkbox', function() {
                const id = $(this).val();
                $(this).is(':checked') ? selectedIds.add(id) : selectedIds.delete(id);
                syncCheckboxState();
            });

            $('#select-all-records').on('change', function() {
                $('.bulk-row-checkbox').each(function() {
                    const id = $(this).val();
                    $('#select-all-records').is(':checked') ? selectedIds.add(id) : selectedIds.delete(id);
                });
                syncCheckboxState();
            });

            $('.bulk-action-trigger').on('click', function() {
                if (selectedIds.size === 0) {
                    alert('Vui lòng chọn ít nhất một yêu cầu.');
                    return;
                }

                if ($(this).data('action') === 'delete' && !confirm('Xóa các yêu cầu đã chọn?')) {
                    return;
                }

                $('#selected-ids').val(Array.from(selectedIds).join(','));
                $('#bulk-action-input').val($(this).data('action'));
                $('#bulk-action-form').trigger('submit');
            });
        });
    </script>
@endsection
