@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="admin-page-actions">
                <div>
                    <h5 class="mb-1">Danh sách giảng viên</h5>
                    <p class="text-muted mb-0">Quản lý hồ sơ giảng viên, kinh nghiệm và ảnh đại diện theo layout admin mới.</p>
                </div>
                <a href="{{ route('teacher.add') }}" class="btn btn-primary">
                    <i class="fa-solid fa-plus me-2"></i>
                    Thêm giảng viên
                </a>
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

            <form id="teacher-filter-form" class="admin-filter-panel mb-4">
                <div class="row g-3">
                    <div class="col-lg-4 col-md-6">
                        <label class="form-label">Từ khóa</label>
                        <input type="text" class="form-control" name="q" id="filter-q"
                            placeholder="Tên, slug, kinh nghiệm...">
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Trạng thái hồ sơ</label>
                        <select class="form-select" name="profile_status" id="filter-profile-status">
                            <option value="">Tất cả</option>
                            <option value="has_image">Đã có ảnh đại diện</option>
                            <option value="missing_image">Chưa có ảnh đại diện</option>
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

            <form id="bulk-action-form" action="{{ route('teacher.bulk') }}" method="POST" class="mb-4">
                @csrf
                <input type="hidden" name="selected_ids" id="selected-ids">
                <input type="hidden" name="bulk_action" id="bulk-action-input">

                <div class="bulk-toolbar">
                    <div class="bulk-toolbar__summary">
                        <span id="selected-count">0</span> giảng viên được chọn
                    </div>
                    <button type="button" class="btn btn-outline-danger bulk-action-trigger" data-action="delete">Xóa</button>
                </div>
            </form>

            <div class="table-responsive">
                <table id="datatable" class="table align-middle w-100">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 48px;">
                                <input type="checkbox" id="select-all-records" class="form-check-input">
                            </th>
                            <th>Ảnh</th>
                            <th>Tên</th>
                            <th>Kinh nghiệm</th>
                            <th>Ngày tạo</th>
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
                    url: "{{ route('teacher.data') }}",
                    data: function(d) {
                        d.q = $('#filter-q').val();
                        d.profile_status = $('#filter-profile-status').val();
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
                        data: 'image'
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'exp'
                    },
                    {
                        data: 'created_at'
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

            $('#teacher-filter-form').on('submit', function(event) {
                event.preventDefault();
                table.ajax.reload();
            });

            $('#reset-filters').on('click', function() {
                $('#teacher-filter-form')[0].reset();
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
                    alert('Vui lòng chọn ít nhất một giảng viên.');
                    return;
                }

                if ($(this).data('action') === 'delete' && !confirm('Xóa các giảng viên đã chọn?')) {
                    return;
                }

                $('#selected-ids').val(Array.from(selectedIds).join(','));
                $('#bulk-action-input').val($(this).data('action'));
                $('#bulk-action-form').trigger('submit');
            });
        });
    </script>
@endsection
