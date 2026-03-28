@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="admin-page-actions">
                <div>
                    <h5 class="mb-1">Thùng rác học viên</h5>
                    <p class="text-muted mb-0">Khôi phục học viên đã xóa mềm hoặc xóa vĩnh viễn từ thùng rác.</p>
                </div>
                <a href="{{ route('students.index') }}" class="btn btn-light border">
                    <i class="fa-solid fa-arrow-left me-2"></i>
                    Quay lại danh sách
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

            @if (auth()->user()
                    ?->canAnyPermission(['students.soft_delete', 'students.delete', 'students.force_delete']))
                <form id="bulk-trash-action-form" action="{{ route('students.trash.bulk') }}" method="POST" class="mb-4">
                    @csrf
                    <input type="hidden" name="selected_ids" id="selected-trash-ids">
                    <input type="hidden" name="bulk_action" id="bulk-trash-action-input">

                    <div class="bulk-toolbar">
                        <div class="bulk-toolbar__summary">
                            <span id="selected-trash-count">0</span> Học viên được chọn
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @if (auth()->user()
                                    ?->canAnyPermission(['students.soft_delete', 'students.delete']))
                                <button type="button" class="btn btn-success bulk-trash-action-trigger"
                                    data-action="restore">Khôi phục</button>
                            @endif
                            @if (auth()->user()?->hasPermission('students.force_delete'))
                                <button type="button" class="btn btn-outline-danger bulk-trash-action-trigger"
                                    data-action="force_delete">Xóa vĩnh viễn</button>
                            @endif
                        </div>
                    </div>
                </form>
            @endif

            <div class="table-responsive">
                <table id="trash-datatable" class="table align-middle w-100">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 48px;">
                                <input type="checkbox" id="select-all-trashed-records" class="form-check-input">
                            </th>
                            <th>Tên</th>
                            <th>Email</th>
                            <th>2FA</th>
                            <th>Thời gian xóa</th>
                            <th>Khôi phục</th>
                            <th>Xóa vĩnh viễn</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
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
    </style>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            const selectedIds = new Set();

            $('#trash-datatable').DataTable({
                autoWidth: false,
                processing: true,
                serverSide: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                order: [
                    [4, 'desc']
                ],
                ajax: "{{ route('students.trash.data') }}",
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
                        data: 'two_factor',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'deleted_at'
                    },
                    {
                        data: 'restore',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'force_delete',
                        orderable: false,
                        searchable: false
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

                $('#selected-trash-count').text(selectedIds.size);

                const visibleCheckboxes = $('.bulk-row-checkbox');
                const checkedVisible = visibleCheckboxes.filter(':checked').length;
                $('#select-all-trashed-records').prop('checked', visibleCheckboxes.length > 0 && visibleCheckboxes
                    .length === checkedVisible);
            }

            $('#trash-datatable').on('change', '.bulk-row-checkbox', function() {
                const id = $(this).val();

                if ($(this).is(':checked')) {
                    selectedIds.add(id);
                } else {
                    selectedIds.delete(id);
                }

                syncCheckboxState();
            });

            $('#select-all-trashed-records').on('change', function() {
                $('.bulk-row-checkbox').each(function() {
                    const id = $(this).val();

                    if ($('#select-all-trashed-records').is(':checked')) {
                        selectedIds.add(id);
                    } else {
                        selectedIds.delete(id);
                    }
                });

                syncCheckboxState();
            });

            $('.bulk-trash-action-trigger').on('click', function() {
                if (selectedIds.size === 0) {
                    alert('Vui lòng chọn ít nhất một học viên trong thùng rác.');
                    return;
                }

                if ($(this).data('action') === 'force_delete' && !confirm(
                        'Xóa vĩnh viễn các học viên đã chọn?')) {
                    return;
                }

                $('#selected-trash-ids').val(Array.from(selectedIds).join(','));
                $('#bulk-trash-action-input').val($(this).data('action'));
                $('#bulk-trash-action-form').trigger('submit');
            });
        });
    </script>
@endsection
