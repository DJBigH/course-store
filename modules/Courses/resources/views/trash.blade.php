@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="admin-page-actions">
                <div>
                    <h5 class="mb-1">Thùng rác khóa học</h5>
                    <p class="text-muted mb-0">Theo dõi các khóa học đã xóa mềm, khôi phục khi cần hoặc xóa vĩnh viễn.</p>
                </div>
                <a href="{{ route('courses.index') }}" class="btn btn-light border">
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

            <form id="bulk-trash-action-form" action="{{ route('courses.trash.bulk') }}" method="POST" class="mb-4">
                @csrf
                <input type="hidden" name="selected_ids" id="selected-trash-course-ids">
                <input type="hidden" name="bulk_action" id="bulk-trash-action-input">

                <div class="bulk-toolbar">
                    <div class="bulk-toolbar__summary">
                        <span id="selected-trash-count">0</span> khóa học được chọn
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-success bulk-trash-action-trigger" data-action="restore">
                            Khôi phục hàng loạt
                        </button>
                        <button type="button" class="btn btn-outline-danger bulk-trash-action-trigger" data-action="force_delete">
                            Xóa vĩnh viễn
                        </button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table id="trash-datatable" class="table align-middle admin-data-table w-100">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 48px;">
                                <input type="checkbox" id="select-all-trashed-courses" class="form-check-input">
                            </th>
                            <th>Khóa học</th>
                            <th>Giá bán</th>
                            <th>Học tập</th>
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
        .course-cell__title {
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.35rem;
        }

        .course-cell__meta,
        .course-learning {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem 0.9rem;
            color: #64748b;
            font-size: 0.9rem;
        }

        .course-cell__meta span,
        .course-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
        }

        .course-pill {
            padding: 0.5rem 0.75rem;
            border-radius: 999px;
            background: #f8fafc;
            color: #334155;
            border: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        .course-price {
            display: flex;
            flex-direction: column;
            gap: 0.1rem;
        }

        .course-price strong {
            color: #0f172a;
        }

        .course-price span {
            color: #94a3b8;
            text-decoration: line-through;
            font-size: 0.88rem;
        }

        .admin-data-table td {
            padding-top: 1rem;
            padding-bottom: 1rem;
            vertical-align: middle;
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
            const selectedTrashCourseIds = new Set();

            $('#trash-datatable').DataTable({
                autoWidth: false,
                processing: true,
                serverSide: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                order: [
                    [4, 'desc']
                ],
                ajax: "{{ route('courses.trash.data') }}",
                columns: [{
                        data: 'select',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'overview',
                        name: 'name'
                    },
                    {
                        data: 'price',
                        name: 'price'
                    },
                    {
                        data: 'learning',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'deleted_at',
                        name: 'deleted_at'
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
                $('.trashed-course-row-checkbox').each(function() {
                    $(this).prop('checked', selectedTrashCourseIds.has($(this).val()));
                });

                $('#selected-trash-count').text(selectedTrashCourseIds.size);

                const visibleCheckboxes = $('.trashed-course-row-checkbox');
                const checkedVisible = visibleCheckboxes.filter(':checked').length;
                $('#select-all-trashed-courses').prop('checked', visibleCheckboxes.length > 0 && visibleCheckboxes.length === checkedVisible);
            }

            $('#trash-datatable').on('change', '.trashed-course-row-checkbox', function() {
                const id = $(this).val();

                if ($(this).is(':checked')) {
                    selectedTrashCourseIds.add(id);
                } else {
                    selectedTrashCourseIds.delete(id);
                }

                syncCheckboxState();
            });

            $('#select-all-trashed-courses').on('change', function() {
                $('.trashed-course-row-checkbox').each(function() {
                    const id = $(this).val();

                    if ($('#select-all-trashed-courses').is(':checked')) {
                        selectedTrashCourseIds.add(id);
                    } else {
                        selectedTrashCourseIds.delete(id);
                    }
                });

                syncCheckboxState();
            });

            $('.bulk-trash-action-trigger').on('click', function() {
                if (selectedTrashCourseIds.size === 0) {
                    alert('Vui lòng chọn ít nhất một khóa học trong thùng rác.');
                    return;
                }

                if ($(this).data('action') === 'force_delete' && !confirm('Xóa vĩnh viễn các khóa học đã chọn?')) {
                    return;
                }

                $('#selected-trash-course-ids').val(Array.from(selectedTrashCourseIds).join(','));
                $('#bulk-trash-action-input').val($(this).data('action'));
                $('#bulk-trash-action-form').trigger('submit');
            });
        });
    </script>
@endsection
