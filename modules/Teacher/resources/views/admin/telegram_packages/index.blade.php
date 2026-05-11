@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="admin-page-actions">
                <div>
                    <h5 class="mb-1">{{ __('teacher::admin.titles.telegram_packages') }}</h5>
                    <p class="text-muted mb-0">{{ __('teacher::admin.titles.telegram_packages_desc') ?? 'Tạo và quản lý các gói đăng ký tính năng Telegram cho giảng viên.' }}</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('teacher.telegram-packages.create') }}" class="btn btn-primary">
                        <i class="fa-solid fa-plus me-2"></i> {{ __('teacher::admin.titles.create_telegram_package') }}
                    </a>
                </div>
            </div>

            @if (session('msg'))
                <div class="alert alert-success border-0 rounded-4">{{ session('msg') }}</div>
            @endif

            <ul class="nav nav-tabs nav-tabs-custom mb-4" id="telegramTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="packages-tab" data-bs-toggle="tab" data-bs-target="#packages" type="button" role="tab" aria-controls="packages" aria-selected="true">
                        <i class="fa-solid fa-box me-2"></i>Danh sách gói
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="subscribers-tab" data-bs-toggle="tab" data-bs-target="#subscribers" type="button" role="tab" aria-controls="subscribers" aria-selected="false">
                        <i class="fa-solid fa-users me-2"></i>Người dùng đăng ký
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="telegramTabsContent">
                <!-- Tab Gói Telegram -->
                <div class="tab-pane fade show active" id="packages" role="tabpanel" aria-labelledby="packages-tab">
                    <div class="table-responsive">
                        <table id="datatable" class="table align-middle w-100">
                            <thead>
                                <tr>
                                    <th>{{ __('teacher::admin.table.telegram_package_name') }}</th>
                                    <th>{{ __('teacher::admin.table.telegram_package_price') }}</th>
                                    <th>{{ __('teacher::admin.table.telegram_package_duration') }}</th>
                                    <th>{{ __('teacher::admin.table.telegram_package_sort') }}</th>
                                    <th>{{ __('teacher::admin.table.telegram_package_status') }}</th>
                                    <th style="width: 80px;">{{ __('teacher::admin.table.actions') }}</th>
                                    <th style="width: 80px;">{{ __('teacher::admin.actions.delete') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>

                <!-- Tab Người dùng đăng ký -->
                <div class="tab-pane fade" id="subscribers" role="tabpanel" aria-labelledby="subscribers-tab">
                    <div class="table-responsive">
                        <table id="subscribers-table" class="table align-middle w-100">
                            <thead>
                                <tr>
                                    <th>Giảng viên</th>
                                    <th>Gói hiện tại</th>
                                    <th>Ngày hết hạn</th>
                                    <th>Trạng thái</th>
                                    <th style="width: 100px;">Thao tác</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Lịch sử mua gói -->
    <div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold">Lịch sử đăng ký: <span id="historyTeacherName" class="text-primary"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="table-responsive">
                        <table id="history-table" class="table align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>Gói đăng ký</th>
                                    <th>Số tiền</th>
                                    <th>Phương thức</th>
                                    <th>Ngày mua</th>
                                    <th>Hạn dùng</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tặng gói -->
    <div class="modal fade" id="giftModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tặng tính năng Telegram</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="giftForm">
                    @csrf
                    <input type="hidden" name="teacher_id" id="giftTeacherId">
                    <div class="modal-body p-4">
                        <p class="mb-4 text-muted">Bạn đang tặng gói cho giáo viên: <strong id="giftTeacherName" class="text-dark"></strong></p>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Chọn gói quà tặng</label>
                            <select name="package_id" class="form-select" required>
                                <option value="">-- Chọn gói --</option>
                                @foreach($packages as $package)
                                    <option value="{{ $package->id }}">{{ $package->name_locale }} ({{ $package->formatted_duration }})</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="alert alert-info border-0 shadow-sm mb-0">
                            <i class="fa-solid fa-circle-info me-2"></i>
                            Gói tặng sẽ có giá 0 VNĐ và giáo viên sẽ nhận được thông báo ngay lập tức.
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-success px-4" id="btnSubmitGift">
                            <i class="fa-solid fa-gift me-1"></i> Xác nhận tặng
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('part.backend.delete')
@endsection

@section('stylesheets')
    <style>
        .admin-page-actions {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #f1f5f9;
        }
        
        /* Modern Tabs Design */
        .nav-tabs-custom {
            border-bottom: 1px solid #e2e8f0;
            gap: 2rem;
            padding: 0 0.5rem;
        }
        .nav-tabs-custom .nav-item {
            margin-bottom: -1px;
        }
        .nav-tabs-custom .nav-link {
            border: none;
            padding: 1rem 0.5rem;
            color: #64748b;
            font-weight: 500;
            font-size: 0.95rem;
            position: relative;
            background: transparent;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            opacity: 0.7;
        }
        .nav-tabs-custom .nav-link i {
            font-size: 1.1rem;
            transition: transform 0.2s ease;
        }
        .nav-tabs-custom .nav-link:hover {
            color: #3b82f6;
            opacity: 1;
        }
        .nav-tabs-custom .nav-link:hover i {
            transform: translateY(-1px);
        }
        .nav-tabs-custom .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2.5px;
            background: #3b82f6;
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 999px;
        }
        .nav-tabs-custom .nav-link.active {
            color: #3b82f6;
            opacity: 1;
            font-weight: 600;
        }
        .nav-tabs-custom .nav-link.active::after {
            width: 100%;
        }
        .nav-tabs-custom .nav-link.active i {
            color: #3b82f6;
        }

        /* Modal styling */
        #historyModal .modal-content {
            border-radius: 20px;
        }
        #historyModal .table thead th {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            border: none;
        }

        /* Dark Mode Adjustments */
        html[data-theme="dark"] .card, 
        html[data-theme="dark"] .modal-content { background-color: #1e293b; color: #f1f5f9; }
        html[data-theme="dark"] .modal-header .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }
        html[data-theme="dark"] .admin-page-actions { border-bottom-color: #334155; }
        html[data-theme="dark"] .table { color: #f1f5f9; }
        html[data-theme="dark"] .table-light { background-color: #334155 !important; border-color: #475569; }
        html[data-theme="dark"] .table-light th { color: #cbd5e1; }
        html[data-theme="dark"] .nav-tabs-custom { border-bottom-color: #334155; }
        html[data-theme="dark"] .nav-tabs-custom .nav-link { color: #94a3b8; }
        html[data-theme="dark"] .nav-tabs-custom .nav-link.active { color: #60a5fa; }
        html[data-theme="dark"] .nav-tabs-custom .nav-link.active::after { background: #60a5fa; }
        html[data-theme="dark"] .nav-tabs-custom .nav-link:hover { color: #60a5fa; }

        .cancel-subscriber, .view-history, .gift-package {
            cursor: pointer !important;
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Table Gói
            $('#datatable').DataTable({
                autoWidth: false,
                processing: true,
                serverSide: true,
                ajax: "{{ route('teacher.telegram-packages.data') }}",
                columns: [
                    { data: 'name' },
                    { data: 'price', orderable: false, searchable: false },
                    { data: 'duration', orderable: false, searchable: false },
                    { data: 'sort_order' },
                    { data: 'is_active', searchable: false },
                    { data: 'edit', orderable: false, searchable: false },
                    { data: 'delete', orderable: false, searchable: false }
                ],
                language: {
                    processing: 'Đang xử lý...',
                    search: 'Tìm kiếm:',
                    lengthMenu: 'Hiển thị _MENU_ bản ghi',
                    info: 'Hiển thị từ _START_ đến _END_ của _TOTAL_ bản ghi',
                    emptyTable: 'Không có dữ liệu',
                    zeroRecords: 'Không tìm thấy dữ liệu phù hợp',
                    paginate: { previous: 'Trước', next: 'Tiếp' }
                }
            });

            // Table Người dùng (Theo giáo viên)
            var subTable = $('#subscribers-table').DataTable({
                autoWidth: false,
                processing: true,
                serverSide: true,
                ajax: "{{ route('teacher.telegram-subscribers.data') }}",
                columns: [
                    { data: 'teacher_name' },
                    { data: 'package_name', orderable: false, searchable: false },
                    { data: 'expires_at', orderable: false, searchable: false },
                    { data: 'status', orderable: false, searchable: false },
                    { data: 'actions', orderable: false, searchable: false }
                ],
                language: {
                    processing: 'Đang xử lý...',
                    search: 'Tìm kiếm:',
                    lengthMenu: 'Hiển thị _MENU_ bản ghi',
                    info: 'Hiển thị từ _START_ đến _END_ của _TOTAL_ bản ghi',
                    emptyTable: 'Không có dữ liệu',
                    zeroRecords: 'Không tìm thấy dữ liệu phù hợp',
                    paginate: { previous: 'Trước', next: 'Tiếp' }
                }
            });

            // Table Lịch sử (In Modal)
            var historyTable;

            $(document).on('click', '.view-history', function() {
                var teacherId = $(this).data('id');
                var teacherName = $(this).data('name');
                
                $('#historyTeacherName').text(teacherName);
                
                if ($.fn.DataTable.isDataTable('#history-table')) {
                    $('#history-table').DataTable().destroy();
                }

                historyTable = $('#history-table').DataTable({
                    autoWidth: false,
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('teacher.telegram-subscribers.history', '') }}/" + teacherId,
                    columns: [
                        { data: 'package_name' },
                        { data: 'amount' },
                        { data: 'payment_method' },
                        { data: 'created_at' },
                        { data: 'expires_at' },
                        { data: 'status' }
                    ],
                    order: [[2, 'desc']],
                    language: {
                        processing: 'Đang tải...',
                        emptyTable: 'Chưa có lịch sử mua gói',
                        info: 'Hiển thị _START_ đến _END_ của _TOTAL_ bản ghi',
                        paginate: { previous: 'Trước', next: 'Tiếp' }
                    }
                });

                $('#historyModal').modal('show');
            });

            $(document).on('click', '.gift-package', function() {
                var teacherId = $(this).data('id');
                var teacherName = $(this).data('name');
                
                $('#giftTeacherId').val(teacherId);
                $('#giftTeacherName').text(teacherName);
                $('#giftModal').modal('show');
            });

            $('#giftForm').on('submit', function(e) {
                e.preventDefault();
                var btn = $('#btnSubmitGift');
                btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Đang xử lý...');

                $.ajax({
                    url: "{{ route('teacher.telegram-subscribers.gift') }}",
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            $('#giftModal').modal('hide');
                            subTable.ajax.reload();
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        alert('Đã có lỗi xảy ra, vui lòng thử lại.');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="fa-solid fa-gift me-1"></i> Xác nhận tặng');
                    }
                });
            });

            // Reload table when switching tabs to fix header width issues
            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
            });

            $(document).on('click', '.cancel-subscriber', function(e) {
                e.preventDefault();
                var id = $(this).attr('data-id');
                if (confirm('Bạn có chắc chắn muốn hủy gói Telegram của người dùng này? Gói sẽ hết hạn ngay lập tức.')) {
                    var url = "{{ route('teacher.telegram-subscribers.cancel', ['id' => ':id']) }}".replace(':id', id);
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                alert(response.message);
                                subTable.ajax.reload(null, false); // Keep pagination
                                if (historyTable) historyTable.ajax.reload(null, false);
                            } else {
                                alert(response.message);
                            }
                        },
                        error: function(xhr) {
                            alert('Đã có lỗi xảy ra: ' + (xhr.responseJSON?.message || 'Lỗi kết nối'));
                        }
                    });
                }
            });
        });
    </script>
@endsection
