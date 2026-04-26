@extends('layouts.backend')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="admin-page-actions mb-4">
                <div>
                    <h5 class="mb-1">Quản lý đánh giá</h5>
                    <p class="text-muted mb-0">Theo dõi và kiểm soát đánh giá từ học viên dành cho khóa học và giảng viên.</p>
                </div>
            </div>

            <ul class="nav nav-tabs nav-tabs-custom mb-4" id="ratingTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="course-ratings-tab" data-bs-toggle="tab" data-bs-target="#course-ratings" type="button" role="tab">
                        <i class="fa-solid fa-book me-2"></i> Đánh giá khóa học
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="teacher-ratings-tab" data-bs-toggle="tab" data-bs-target="#teacher-ratings" type="button" role="tab">
                        <i class="fa-solid fa-chalkboard-user me-2"></i> Đánh giá giảng viên
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="ratingTabsContent">
                <!-- Course Ratings -->
                <div class="tab-pane fade show active" id="course-ratings" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle custom-datatable w-100" id="course-ratings-table">
                            <thead>
                                <tr>
                                    <th>Khóa học</th>
                                    <th>Học viên</th>
                                    <th>Số sao</th>
                                    <th>Ngày đánh giá</th>
                                    <th width="100">Hành động</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>

                <!-- Teacher Ratings -->
                <div class="tab-pane fade" id="teacher-ratings" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle custom-datatable w-100" id="teacher-ratings-table">
                            <thead>
                                <tr>
                                    <th>Giảng viên</th>
                                    <th>Học viên</th>
                                    <th>Số sao</th>
                                    <th>Ngày đánh giá</th>
                                    <th width="100">Hành động</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('stylesheets')
    <style>
        .nav-tabs-custom {
            border-bottom: 2px solid #f1f3f5;
        }
        .nav-tabs-custom .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 500;
            padding: 1rem 1.5rem;
            position: relative;
        }
        .nav-tabs-custom .nav-link.active {
            color: var(--bs-primary);
            background: transparent;
        }
        .nav-tabs-custom .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--bs-primary);
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(function() {
            // Course Ratings Table
            var courseTable = $('#course-ratings-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('courses.ratings.course-data') }}",
                columns: [
                    { data: 'course_name', name: 'course_name' },
                    { data: 'student_name', name: 'student_name' },
                    { data: 'rating', name: 'rating' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Vietnamese.json"
                },
                order: [[3, 'desc']]
            });

            // Teacher Ratings Table
            var teacherTable = $('#teacher-ratings-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('courses.ratings.teacher-data') }}",
                columns: [
                    { data: 'teacher_name', name: 'teacher_name' },
                    { data: 'student_name', name: 'student_name' },
                    { data: 'rating', name: 'rating' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Vietnamese.json"
                },
                order: [[3, 'desc']]
            });

            // Delete Rating
            $(document).on('click', '.delete-rating', function() {
                var id = $(this).data('id');
                var type = $(this).data('type');
                
                if (confirm('Bạn có chắc chắn muốn xóa đánh giá này?')) {
                    $.ajax({
                        url: "{{ route('courses.ratings.delete') }}",
                        type: 'DELETE',
                        data: {
                            id: id,
                            type: type,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                if (type === 'course') {
                                    courseTable.ajax.reload();
                                } else {
                                    teacherTable.ajax.reload();
                                }
                            }
                        }
                    });
                }
            });

            // Toggle Visibility
            $(document).on('click', '.toggle-visibility', function() {
                var id = $(this).data('id');
                var type = $(this).data('type');
                
                $.ajax({
                    url: "{{ route('courses.ratings.toggle-visibility') }}",
                    type: 'POST',
                    data: {
                        id: id,
                        type: type,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            if (type === 'course') {
                                courseTable.ajax.reload(null, false);
                            } else {
                                teacherTable.ajax.reload(null, false);
                            }
                        }
                    }
                });
            });

            // Fix for DataTables in tabs
            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
            });
        });
    </script>
@endsection
