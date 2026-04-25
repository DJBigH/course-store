@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="admin-page-actions">
                <div>
                    <h5 class="mb-1">Danh sách mã giảm giá</h5>
                    <p class="text-muted mb-0">Quản lý ưu đãi, điều kiện áp dụng, số lượng còn lại và lịch sử gắn mã.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @if (auth()->user()?->canAnyPermission(['coupons.soft_delete', 'coupons.delete', 'coupons.force_delete']))
                        <a href="{{ route('coupons.trash') }}" class="btn btn-light border">
                            <i class="fa-solid fa-trash-can me-2"></i>
                            Thùng rác
                        </a>
                    @endif
                    @if (auth()->user()?->hasPermission('coupons.create'))
                        <a href="{{ route('coupons.add') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>
                            Thêm mã giảm giá
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

            @if (auth()->user()?->canAnyPermission(['coupons.edit', 'coupons.soft_delete', 'coupons.delete']))
                <form id="bulk-action-form" action="{{ route('coupons.bulk') }}" method="POST" class="mb-4">
                    @csrf
                    <input type="hidden" name="selected_ids" id="selected-ids">
                    <input type="hidden" name="bulk_action" id="bulk-action-input">

                    <div class="bulk-toolbar">
                        <div class="bulk-toolbar__summary">
                            <span id="selected-count">0</span> mã giảm giá được chọn
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @if (auth()->user()?->hasPermission('coupons.edit'))
                                <button type="button" class="btn btn-outline-secondary bulk-action-trigger"
                                    data-action="duplicate">Nhân bản</button>
                            @endif
                            @if (auth()->user()?->canAnyPermission(['coupons.soft_delete', 'coupons.delete']))
                                <button type="button" class="btn btn-outline-danger bulk-action-trigger"
                                    data-action="delete">Xóa</button>
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
                            <th>Mã</th>
                            <th>Người tạo</th>
                            <th>Loại giảm</th>
                            <th>Giá trị</th>
                            <th>Cách dùng</th>
                            <th>Số lượng</th>
                            <th>Thời gian</th>
                            <th>Tối thiểu</th>
                            <th>Cấp mã</th>
                            <th>Lịch sử</th>
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

        html[data-theme='dark'] .bulk-toolbar {
            background: rgba(15, 23, 42, 0.88);
            border-color: rgba(148, 163, 184, 0.18);
        }

        html[data-theme='dark'] .bulk-toolbar__summary {
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
                ajax: "{{ route('coupons.data') }}",
                columns: [{
                        data: 'select',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'code'
                    },
                    {
                        data: 'creator'
                    },
                    {
                        data: 'discount_type'
                    },
                    {
                        data: 'discount_value'
                    },
                    {
                        data: 'usage_mode'
                    },
                    {
                        data: 'count'
                    },
                    {
                        data: 'time'
                    },
                    {
                        data: 'total_condition'
                    },
                    {
                        data: 'bindings'
                    },
                    {
                        data: 'logs'
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
                    zeroRecords: 'Không tìm thấy mã giảm giá',
                    emptyTable: 'Chưa có mã giảm giá nào',
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
                $('#select-all-records').prop('checked', visibleCheckboxes.length > 0 && visibleCheckboxes
                    .length === checkedVisible);
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
                    alert('Vui lòng chọn ít nhất một mã giảm giá.');
                    return;
                }

                $('#selected-ids').val(Array.from(selectedIds).join(','));
                $('#bulk-action-input').val($(this).data('action'));
                $('#bulk-action-form').trigger('submit');
            });
        });
    </script>
@endsection
