@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="admin-page-actions">
                <div>
                    <h5 class="mb-1">Danh sách học viên</h5>
                    <p class="text-muted mb-0">Theo dõi trạng thái tài khoản, 2FA, khóa học đã mua và lịch sử mã giảm giá.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @if (auth()->user()?->canAnyPermission(['students.soft_delete', 'students.delete', 'students.force_delete']))
                        <a href="{{ route('students.trash') }}" class="btn btn-light border">
                            <i class="fa-solid fa-trash-can me-2"></i>
                            Thùng rác
                        </a>
                    @endif
                    @if (auth()->user()?->hasPermission('students.create'))
                        <a href="{{ route('students.add') }}" class="btn btn-primary">
                            <i class="fa-solid fa-plus me-2"></i>
                            Thêm học viên
                        </a>
                    @endif
                </div>
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

            @if (auth()->user()?->canAnyPermission(['students.edit', 'students.soft_delete', 'students.delete']))
                <form id="bulk-action-form" action="{{ route('students.bulk') }}" method="POST" class="mb-4">
                    @csrf
                    <input type="hidden" name="selected_ids" id="selected-ids">
                    <input type="hidden" name="bulk_action" id="bulk-action-input">

                    <div class="bulk-toolbar">
                        <div class="bulk-toolbar__summary">
                            <span id="selected-count">0</span> Học viên được chọn
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @if (auth()->user()?->hasPermission('students.edit'))
                                <button type="button" class="btn btn-success bulk-action-trigger" data-action="activate">Kích hoạt</button>
                                <button type="button" class="btn btn-light border bulk-action-trigger" data-action="deactivate">Tạm khóa</button>
                            @endif
                            @if (auth()->user()?->canAnyPermission(['students.soft_delete', 'students.delete']))
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
                            <th>Tên</th>
                            <th>Email</th>
                            <th>Vai trò</th>
                            <th>2FA</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th>Khóa học</th>
                            <th>Lịch sử mã</th>
                            <th>Lịch sử</th>
                            <th>Đăng nhập</th>
                            <th>Sửa</th>
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

        html[data-theme="dark"] .bulk-toolbar {
            background: #162033;
            border-color: #2b3b53;
        }

        html[data-theme="dark"] .bulk-toolbar__summary {
            color: #cbd5e1;
        }

        html[data-theme="dark"] .bulk-toolbar .btn-light.border {
            background: #0f172a;
            color: #f8fafc;
            border-color: #334155 !important;
        }

        html[data-theme="dark"] #datatable tbody td,
        html[data-theme="dark"] #datatable tbody a {
            color: #e2e8f0;
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            const selectedIds = new Set();

            $('#datatable').DataTable({
                autoWidth: false,
                processing: true,
                serverSide: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                ajax: "{{ route('students.data') }}",
                columns: [{
                        data: 'select',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'email'
                    },
                    {
                        data: 'roles',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'two_factor',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'courses'
                    },
                    {
                        data: 'link'
                    },
                    {
                        data: 'logs'
                    },
                    {
                        data: 'impersonate',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'edit'
                    },
                    {
                        data: 'delete'
                    }
                ],
                language: {
                    processing: 'Đang xử lý...',
                    search: 'Tìm kiếm:',
                    lengthMenu: 'Hiển thị _MENU_ bản ghi',
                    info: 'Hiển thị từ _START_ đến _END_ của _TOTAL_ bản ghi',
                    infoEmpty: 'Hiển thị 0 đến 0 của 0 bản ghi',
                    infoFiltered: '(lọc từ _MAX_ bản ghi)',
                    loadingRecords: 'Đang tải...',
                    zeroRecords: 'Không tìm thấy bản ghi nào',
                    emptyTable: 'Không có dữ liệu trong bảng',
                    paginate: {
                        previous: 'Trước',
                        next: 'Tiếp'
                    },
                    aria: {
                        sortAscending: ': sắp xếp tăng dần',
                        sortDescending: ': sắp xếp giảm dần'
                    }
                },
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

            $('#datatable').on('change', '.bulk-row-checkbox', function() {
                const id = $(this).val();

                if ($(this).is(':checked')) {
                    selectedIds.add(id);
                } else {
                    selectedIds.delete(id);
                }

                syncCheckboxState();
            });

            $('#select-all-records').on('change', function() {
                $('.bulk-row-checkbox').each(function() {
                    const id = $(this).val();

                    if ($('#select-all-records').is(':checked')) {
                        selectedIds.add(id);
                    } else {
                        selectedIds.delete(id);
                    }
                });

                syncCheckboxState();
            });

            $('.bulk-action-trigger').on('click', function() {
                if (selectedIds.size === 0) {
                    alert('Vui lòng chọn ít nhất một học viên.');
                    return;
                }

                $('#selected-ids').val(Array.from(selectedIds).join(','));
                $('#bulk-action-input').val($(this).data('action'));
                $('#bulk-action-form').trigger('submit');
            });
        });
    </script>
@endsection
