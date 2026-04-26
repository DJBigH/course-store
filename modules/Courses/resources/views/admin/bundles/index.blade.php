@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="admin-page-actions mb-4 d-flex justify-content-between align-items-start">
                <div>
                    <h5 class="mb-1">Quản lý Combo khóa học</h5>
                    <p class="text-muted mb-0">Quản lý các gói combo khóa học từ giảng viên, kiểm soát hiển thị và giá bán.</p>
                </div>
                @if (auth()->user()?->hasPermission('courses.create'))
                    <a href="{{ route('courses.bundles.add') }}" class="btn btn-primary shadow-sm rounded-pill px-4">
                        <i class="fa-solid fa-plus me-2"></i> Thêm Combo mới
                    </a>
                @endif
            </div>

            @if (session('msg'))
                <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4">
                    <i class="fa-solid fa-circle-check me-2"></i> {{ session('msg') }}
                </div>
            @endif
            @if (session('msg_danger'))
                <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4">
                    <i class="fa-solid fa-circle-exclamation me-2"></i> {{ session('msg_danger') }}
                </div>
            @endif

            <div class="row g-3 mb-4">
                <div class="col-12 col-md-4">
                    <label class="form-label fw-bold">Trạng thái</label>
                    <select id="status-filter" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="1">Đang hiển thị</option>
                        <option value="0">Đang ẩn</option>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label fw-bold">Giảng viên</label>
                    <select id="teacher-filter" class="form-select">
                        <option value="">Tất cả giảng viên</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mb-4">
                <button type="button" id="apply-filters" class="btn btn-primary">
                    <i class="fa-solid fa-filter me-2"></i> Lọc dữ liệu
                </button>
                <button type="button" id="reset-filters" class="btn btn-light border">
                    <i class="fa-solid fa-rotate-left me-2"></i> Xóa bộ lọc
                </button>
            </div>

            @if (auth()->user()?->hasPermission('courses.edit'))
                <form id="bulk-action-form" action="{{ route('courses.bundles.bulk') }}" method="POST" class="mb-4">
                    @csrf
                    <input type="hidden" name="selected_ids" id="selected-bundle-ids">
                    <input type="hidden" name="bulk_action" id="bulk-action-input">

                    <div class="bulk-toolbar shadow-sm">
                        <div class="bulk-toolbar__summary">
                            <span id="selected-count" class="badge bg-primary me-1">0</span> combo được chọn
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-success bulk-action-trigger" data-action="show">Hiện hàng loạt</button>
                            <button type="button" class="btn btn-light border bulk-action-trigger" data-action="hide">Ẩn hàng loạt</button>
                            <button type="button" class="btn btn-danger bulk-action-trigger" data-action="hot">Đánh dấu HOT</button>
                            <button type="button" class="btn btn-outline-secondary bulk-action-trigger" data-action="unhot">Bỏ HOT</button>
                            <button type="button" class="btn btn-outline-danger bulk-action-trigger" data-action="delete">Xóa hàng loạt</button>
                        </div>
                    </div>
                </form>
            @endif

            <div class="table-responsive">
                <table id="datatable" class="table align-middle admin-data-table w-100">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 48px;">
                                <input type="checkbox" id="select-all-bundles" class="form-check-input">
                            </th>
                            <th>Combo</th>
                            <th>Giá bán</th>
                            <th>Trạng thái</th>
                            <th class="text-center">Vị trí</th>
                            <th>Ngày tạo</th>
                            <th class="text-center" style="width: 60px;">HOT</th>
                            <th class="text-center" style="width: 80px;">Trạng thái</th>
                            <th class="text-center" style="width: 100px;">Hành động</th>
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
        .course-cell__title {
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.35rem;
        }
        .course-cell__meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem 0.9rem;
            color: #64748b;
            font-size: 0.85rem;
        }
        .course-cell__meta span {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }
        .bulk-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.25rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            margin-bottom: 1.5rem;
        }
        .bulk-toolbar__summary {
            font-weight: 600;
            color: #334155;
        }
        .update-position:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
        }
        html[data-theme="dark"] .bulk-toolbar {
            background: #162033;
            border-color: #2b3b53;
        }
        html[data-theme="dark"] .bulk-toolbar__summary {
            color: #f1f5f9;
        }
        html[data-theme="dark"] .course-cell__title {
            color: #f1f5f9;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            const selectedBundleIds = new Set();

            const table = $('#datatable').DataTable({
                autoWidth: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('courses.bundles.data') }}",
                    data: function(d) {
                        d.status_filter = $('#status-filter').val();
                        d.teacher_filter = $('#teacher-filter').val();
                    }
                },
                columns: [
                    { data: 'select', orderable: false, searchable: false },
                    { data: 'overview', name: 'name' },
                    { data: 'price', name: 'price' },
                    { data: 'status', name: 'status' },
                    { data: 'position', name: 'position', className: 'text-center' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'toggle_hot', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'toggle_status', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[4, 'asc'], [5, 'desc']],
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Vietnamese.json"
                },
                drawCallback: function() {
                    syncCheckboxState();
                }
            });

            // Quick Update Position
            $('#datatable').on('change', '.update-position', function() {
                const id = $(this).data('id');
                const position = $(this).val();
                
                $.ajax({
                    url: "{{ route('courses.bundles.update-position') }}",
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id,
                        position: position
                    },
                    success: function() {
                        // Optional: Show a small toast
                    }
                });
            });

            function syncCheckboxState() {
                $('.bundle-row-checkbox').each(function() {
                    $(this).prop('checked', selectedBundleIds.has($(this).val()));
                });
                $('#selected-count').text(selectedBundleIds.size);
                
                const visibleCheckboxes = $('.bundle-row-checkbox');
                const checkedVisible = visibleCheckboxes.filter(':checked').length;
                $('#select-all-bundles').prop('checked', visibleCheckboxes.length > 0 && visibleCheckboxes.length === checkedVisible);
            }

            $('#datatable').on('change', '.bundle-row-checkbox', function() {
                const id = $(this).val();
                if ($(this).is(':checked')) {
                    selectedBundleIds.add(id);
                } else {
                    selectedBundleIds.delete(id);
                }
                syncCheckboxState();
            });

            $('#select-all-bundles').on('change', function() {
                $('.bundle-row-checkbox').each(function() {
                    const id = $(this).val();
                    if ($('#select-all-bundles').is(':checked')) {
                        selectedBundleIds.add(id);
                    } else {
                        selectedBundleIds.delete(id);
                    }
                });
                syncCheckboxState();
            });

            $('#apply-filters').on('click', () => table.ajax.reload());
            $('#reset-filters').on('click', function() {
                $('#status-filter, #teacher-filter').val('');
                table.ajax.reload();
            });

            $('.bulk-action-trigger').on('click', function() {
                if (selectedBundleIds.size === 0) {
                    alert('Vui lòng chọn ít nhất một combo.');
                    return;
                }
                if ($(this).data('action') === 'delete' && !confirm('Xác nhận xóa các combo đã chọn?')) {
                    return;
                }
                $('#selected-bundle-ids').val(Array.from(selectedBundleIds).join(','));
                $('#bulk-action-input').val($(this).data('action'));
                $('#bulk-action-form').submit();
            });
        });
    </script>
@endsection
