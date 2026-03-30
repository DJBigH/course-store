@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="admin-page-actions">
                <div>
                    <h5 class="mb-1">Danh sách liên hệ</h5>
                    <p class="text-muted mb-0">Theo dõi yêu cầu liên hệ, trạng thái tiếp nhận và lịch sử xử lý.</p>
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
                    <div class="col-lg-4 col-md-6">
                        <label class="form-label">Từ khóa</label>
                        <input type="text" class="form-control" name="q" id="filter-q"
                            placeholder="Tên, email, số điện thoại, nội dung...">
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Trạng thái</label>
                        <select class="form-select" name="status_filter" id="filter-status">
                            <option value="">Tất cả</option>
                            <option value="0">Chờ tiếp xử</option>
                            <option value="1">Đã tiếp nhận</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">Từ ngày</label>
                        <input type="date" class="form-control" name="from_date" id="filter-from-date">
                    </div>
                    <div class="col-lg-2 col-md-6">
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
                            <span id="selected-count">0</span> Liên hệ được chọn
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @if (auth()->user()?->hasPermission('contacts.update'))
                                <button type="button" class="btn btn-success bulk-action-trigger" data-action="accept">Tiếp nhận</button>
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
                            <th>Tên</th>
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

        html[data-theme="dark"] .bulk-toolbar__summary {
            color: #cbd5e1;
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
                        d.status_filter = $('#filter-status').val();
                        d.from_date = $('#filter-from-date').val();
                        d.to_date = $('#filter-to-date').val();
                    }
                },
                columns: [{
                        data: 'select',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'phone'
                    },
                    {
                        data: 'email'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'logs'
                    },
                    {
                        data: 'view'
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
                    alert('Vui lòng chọn ít nhất một liên hệ.');
                    return;
                }

                if ($(this).data('action') === 'delete' && !confirm('Xóa các liên hệ đã chọn?')) {
                    return;
                }

                $('#selected-ids').val(Array.from(selectedIds).join(','));
                $('#bulk-action-input').val($(this).data('action'));
                $('#bulk-action-form').trigger('submit');
            });
        });
    </script>
@endsection
