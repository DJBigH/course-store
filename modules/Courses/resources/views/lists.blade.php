@extends('layouts.backend')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="course-stat__label">Tổng khóa học</div>
                    <div class="course-stat__value">{{ number_format($stats['total'] ?? 0) }}</div>
                    <div class="course-stat__meta">Tất cả khóa học trong hệ thống</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="course-stat__label">Đã xuất bản</div>
                    <div class="course-stat__value">{{ number_format($stats['published'] ?? 0) }}</div>
                    <div class="course-stat__meta">Sẵn sàng cho học viên mua và học</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="course-stat__label">Bản nháp</div>
                    <div class="course-stat__value">{{ number_format($stats['draft'] ?? 0) }}</div>
                    <div class="course-stat__meta">Đang chuẩn bị nội dung hoặc kiểm tra</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="course-stat__label">Miễn phí</div>
                    <div class="course-stat__value">{{ number_format($stats['free'] ?? 0) }}</div>
                    <div class="course-stat__meta">Khóa học đang mở học thử miễn phí</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="admin-page-actions">
                <div>
                    <h5 class="mb-1">Danh sách khóa học</h5>
                    <p class="text-muted mb-0">Quản lý nội dung, giá bán, trạng thái và truy cập nhanh đến bài giảng.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @if (auth()->user()?->hasPermission('courses.view'))
                        <a href="{{ route('courses.trash') }}" class="btn btn-light border">
                            <i class="fa-solid fa-trash-can-arrow-up me-2"></i>
                            Thùng rác
                        </a>
                    @endif
                    @if (auth()->user()?->hasPermission('comments.moderate'))
                        <a href="{{ route('courses.comments.admin') }}" class="btn btn-light border">
                            <i class="fa-solid fa-comments me-2"></i>
                            Bình luận
                        </a>
                    @endif
                    @if (auth()->user()?->hasPermission('courses.create'))
                        <a href="{{ route('courses.add') }}" class="btn btn-primary">
                            <i class="fa-solid fa-plus me-2"></i>
                            Thêm khóa học
                        </a>
                    @endif
                </div>
            </div>

            @if (session('msg'))
                <div class="alert alert-success border-0 rounded-4">{{ session('msg') }}</div>
            @endif
            @if ($errors->has('bulk_action'))
                <div class="alert alert-danger border-0 rounded-4">{{ $errors->first('bulk_action') }}</div>
            @endif

            <div class="row g-3 mb-4">
                <div class="col-12 col-md-4">
                    <label class="form-label">Trạng thái</label>
                    <select id="status-filter" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="1">Đã xuất bản</option>
                        <option value="0">Bản nháp</option>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Giảng viên</label>
                    <select id="teacher-filter" class="form-select">
                        <option value="">Tất cả giảng viên</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Danh mục</label>
                    <select id="category-filter" class="form-select">
                        <option value="">Tất cả danh mục</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Mức giá</label>
                    <select id="price-filter" class="form-select">
                        <option value="">Tất cả mức giá</option>
                        <option value="free">Miễn phí</option>
                        <option value="discounted">Đang giảm giá</option>
                        <option value="under_500k">Dưới 500.000 đ</option>
                        <option value="500k_1m">500.000 đ đến 1.000.000 đ</option>
                        <option value="above_1m">Trên 1.000.000 đ</option>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Bài học trial</label>
                    <select id="trial-filter" class="form-select">
                        <option value="">Tất cả khóa học</option>
                        <option value="with_trial">Có bài học trial</option>
                        <option value="without_trial">Không có bài học trial</option>
                    </select>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mb-3">
                <button type="button" id="apply-filters" class="btn btn-primary">
                    <i class="fa-solid fa-filter me-2"></i>
                    Lọc khóa học
                </button>
                <button type="button" id="reset-filters" class="btn btn-light border">
                    <i class="fa-solid fa-rotate-left me-2"></i>
                    Xóa bộ lọc
                </button>
            </div>

            @if (auth()->user()?->hasPermission('courses.publish'))
                <form id="bulk-action-form" action="{{ route('courses.bulk') }}" method="POST" class="mb-4">
                    @csrf
                    <input type="hidden" name="selected_ids" id="selected-course-ids">
                    <input type="hidden" name="bulk_action" id="bulk-action-input">

                    <div class="bulk-toolbar">
                        <div class="bulk-toolbar__summary">
                            <span id="selected-count">0</span> khóa học được chọn
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-success bulk-action-trigger" data-action="publish">Xuất bản hàng loạt</button>
                            <button type="button" class="btn btn-light border bulk-action-trigger" data-action="draft">Chuyển về nháp</button>
                            <button type="button" class="btn btn-outline-secondary bulk-action-trigger" data-action="duplicate">Nhân bản hàng loạt</button>
                            <button type="button" class="btn btn-outline-danger bulk-action-trigger" data-action="soft_delete">Xóa mềm</button>
                        </div>
                    </div>
                </form>
            @endif

            <div class="table-responsive">
                <table id="datatable" class="table align-middle admin-data-table w-100">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 48px;">
                                <input type="checkbox" id="select-all-courses" class="form-check-input">
                            </th>
                            <th>Khóa học</th>
                            <th>Giá bán</th>
                            <th>Trạng thái</th>
                            <th>Học tập</th>
                            <th>Ngày tạo</th>
                            <th>Publish nhanh</th>
                            <th>Nhân bản</th>
                            <th>Lịch sử</th>
                            <th>Bài giảng</th>
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
        .course-stat__label {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 0.35rem;
        }

        .course-stat__value {
            font-size: 2rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.1;
            margin-bottom: 0.35rem;
        }

        .course-stat__meta {
            color: #64748b;
            font-size: 0.92rem;
        }

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
            const selectedCourseIds = new Set();

            const table = $('#datatable').DataTable({
                autoWidth: false,
                processing: true,
                serverSide: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                order: [
                    [5, 'desc']
                ],
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
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'learning',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'publish',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'duplicate',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'logs',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'lessions',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'edit',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'delete',
                        orderable: false,
                        searchable: false
                    }
                ],
                ajax: {
                    url: "{{ route('courses.data') }}",
                    data: function(d) {
                        d.status_filter = $('#status-filter').val();
                        d.teacher_filter = $('#teacher-filter').val();
                        d.category_filter = $('#category-filter').val();
                        d.price_filter = $('#price-filter').val();
                        d.trial_filter = $('#trial-filter').val();
                    }
                },
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
                $('.course-row-checkbox').each(function() {
                    $(this).prop('checked', selectedCourseIds.has($(this).val()));
                });

                $('#selected-count').text(selectedCourseIds.size);

                const visibleCheckboxes = $('.course-row-checkbox');
                const checkedVisible = visibleCheckboxes.filter(':checked').length;
                $('#select-all-courses').prop('checked', visibleCheckboxes.length > 0 && visibleCheckboxes.length === checkedVisible);
            }

            $('#datatable').on('change', '.course-row-checkbox', function() {
                const id = $(this).val();

                if ($(this).is(':checked')) {
                    selectedCourseIds.add(id);
                } else {
                    selectedCourseIds.delete(id);
                }

                syncCheckboxState();
            });

            $('#select-all-courses').on('change', function() {
                $('.course-row-checkbox').each(function() {
                    const id = $(this).val();

                    if ($('#select-all-courses').is(':checked')) {
                        selectedCourseIds.add(id);
                    } else {
                        selectedCourseIds.delete(id);
                    }
                });

                syncCheckboxState();
            });

            $('#apply-filters').on('click', function() {
                table.ajax.reload();
            });

            $('#status-filter, #teacher-filter, #category-filter, #price-filter, #trial-filter').on('change', function() {
                table.ajax.reload();
            });

            $('#reset-filters').on('click', function() {
                $('#status-filter').val('');
                $('#teacher-filter').val('');
                $('#category-filter').val('');
                $('#price-filter').val('');
                $('#trial-filter').val('');
                table.ajax.reload();
            });

            $('.bulk-action-trigger').on('click', function() {
                if (selectedCourseIds.size === 0) {
                    alert('Vui lòng chọn ít nhất một khóa học.');
                    return;
                }

                $('#selected-course-ids').val(Array.from(selectedCourseIds).join(','));
                $('#bulk-action-input').val($(this).data('action'));
                $('#bulk-action-form').trigger('submit');
            });
        });
    </script>
@endsection
