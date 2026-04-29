@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="admin-page-actions">
                <div>
                    <h5 class="mb-1">Danh sách giảng viên</h5>
                    <p class="text-muted mb-0">Quản lý hồ sơ giảng viên, kinh nghiệm và ảnh đại diện theo layout admin mới.
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @if (auth()->user()
                            ?->canAnyPermission(['teachers.soft_delete', 'teachers.delete', 'teachers.force_delete']))
                        <a href="{{ route('teacher.trash') }}" class="btn btn-light border">
                            <i class="fa-solid fa-trash-can me-2"></i>
                            Thùng rác
                        </a>
                    @endif
                    @if (auth()->user()?->hasPermission('teachers.create'))
                        <a href="{{ route('teacher.add') }}" class="btn btn-primary">
                            <i class="fa-solid fa-plus me-2"></i>
                            Thêm giảng viên
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
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Hoạt động gần nhất</label>
                        <select class="form-select" name="activity_status" id="filter-activity-status">
                            <option value="">Tất cả</option>
                            <option value="active_30">Có hoạt động trong 30 ngày</option>
                            <option value="inactive_30">Không hoạt động từ 30 ngày</option>
                            <option value="inactive_60">Không hoạt động từ 60 ngày</option>
                            <option value="never_active">Chưa có hoạt động nào</option>
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

            @if (auth()->user()
                    ?->canAnyPermission(['teachers.soft_delete', 'teachers.delete']))
                <form id="bulk-action-form" action="{{ route('teacher.bulk') }}" method="POST" class="mb-4">
                    @csrf
                    <input type="hidden" name="selected_ids" id="selected-ids">
                    <input type="hidden" name="bulk_action" id="bulk-action-input">

                    <div class="bulk-toolbar">
                        <div class="bulk-toolbar__summary">
                            <span id="selected-count">0</span> giảng viên được chọn
                        </div>
                        <button type="button" class="btn btn-outline-danger bulk-action-trigger"
                            data-action="delete">Xóa</button>
                    </div>
                </form>
            @endif

            <div class="table-responsive">
                <table id="datatable" class="table align-middle w-100 table-hover">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 48px;">
                                <input type="checkbox" id="select-all-records" class="form-check-input">
                            </th>
                            <th>Giảng viên</th>
                            <th>Kinh nghiệm & Đánh giá</th>
                            <th>Trạng thái</th>
                            <th>Hoạt động</th>
                            <th class="text-end" style="width: 80px;">Hành động</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    @include('part.backend.delete')

    {{-- Modal Khóa tài khoản --}}
    <div class="modal fade" id="lockTeacherModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="lockTeacherForm" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="fa-solid fa-user-lock me-1"></i> Khóa quyền giáo viên</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Bạn đang thực hiện khóa quyền giáo viên đối với: <strong id="lock-teacher-name"></strong></p>
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="lock_reason">Lý do khóa <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="lock_reason" id="lock_reason" rows="4" required 
                                      placeholder="Nhập lý do cụ thể để giáo viên biết..."></textarea>
                        </div>
                        <div class="alert alert-warning border-0 small">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i> 
                            Hành động này sẽ ngăn giáo viên truy cập vào Dashboard nhưng <strong>không khóa</strong> quyền học viên của họ.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-danger">Xác nhận khóa</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
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

        .admin-filter-panel {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
        }

        .bulk-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.25rem;
            background: #fff5f5;
            border: 1px solid #feb2b2;
            border-radius: 12px;
            margin-bottom: 1rem;
        }

        .bulk-toolbar__summary {
            font-weight: 600;
            color: #c53030;
        }

        .teacher-admin-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .teacher-admin-badge--blue {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .teacher-admin-badge--gold {
            background: #fffbeb;
            color: #b45309;
        }

        .teacher-admin-badge--emerald {
            background: #ecfdf5;
            color: #047857;
        }

        .teacher-admin-badge--violet {
            background: #f5f3ff;
            color: #6d28d9;
        }

        .teacher-admin-badge--rose {
            background: #fff1f2;
            color: #be123c;
        }

        .teacher-admin-badge--slate {
            background: #f8fafc;
            color: #475569;
        }

        .activity-age {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .activity-age--fresh {
            background: #dcfce7;
            color: #15803d;
        }

        .teacher-rating-cell .rating-text {
            font-size: 1.1rem;
            margin-bottom: 0.1rem;
        }

        .teacher-rating-cell .text-muted {
            font-size: 0.75rem;
        }

        .activity-age--notice {
            background: #fef9c3;
            color: #a16207;
        }

        .activity-age--warning {
            background: #ffedd5;
            color: #c2410c;
        }

        .activity-age--danger {
            background: #fee2e2;
            color: #b91c1c;
        }

        /* Dark Mode support */
        html[data-theme="dark"] .admin-filter-panel {
            background: #1e293b;
            border-color: #334155;
        }

        html[data-theme="dark"] .bulk-toolbar {
            background: #2d1a1a;
            border-color: #4a1a1a;
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
                order: [
                    [1, 'asc']
                ],
                ajax: {
                    url: "{{ route('teacher.data') }}",
                    data: function(d) {
                        d.q = $('#filter-q').val();
                        d.profile_status = $('#filter-profile-status').val();
                        d.activity_status = $('#filter-activity-status').val();
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
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'exp_rating',
                        name: 'exp',
                        searchable: false
                    },
                    {
                        data: 'teacher_status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'activity_timeline',
                        name: 'created_at',
                        searchable: false
                    },
                    {
                        data: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-end'
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
                $('#select-all-records').prop('checked', visibleCheckboxes.length > 0 && visibleCheckboxes
                    .length === checkedVisible);
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

            // Xử lý nút khóa giáo viên
            $('#datatable').on('click', '.btn-lock-teacher', function() {
                var id = $(this).data('id');
                var name = $(this).data('name');
                var url = $(this).data('url');

                $('#lock-teacher-name').text(name);
                $('#lockTeacherForm').attr('action', url);
                $('#lockTeacherModal').modal('show');
            });
        });
    </script>
@endsection
