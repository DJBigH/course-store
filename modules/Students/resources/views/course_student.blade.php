@extends('layouts.backend')

@section('content')
    <style>
        #grantCourseModal .modal-content {
            border-radius: 20px;
            border: none;
            overflow: hidden;
            box-shadow: 0 15px 50px rgba(0,0,0,0.15);
        }
        #grantCourseModal .modal-header {
            border-bottom: 1px solid rgba(0,0,0,0.05);
            background-color: var(--admin-surface-2, #f8f9fa);
            color: var(--admin-text, #0f172a);
            padding: 1.5rem;
        }
        #grantCourseModal .modal-footer {
            border-top: 1px solid rgba(0,0,0,0.05);
            padding: 1.2rem;
        }
        #grantCourseModal .btn {
            border-radius: 10px;
            padding: 0.5rem 1.5rem;
        }
        .select2-container--default .select2-selection--multiple {
            border-radius: 10px !important;
            padding: 5px;
            border: 1px solid #dee2e6;
        }
    </style>
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>
                🎓 Khóa học đã mua của:
                <span class="text-primary">{{ $student->name }}</span>

                <span class="badge bg-info ms-2">
                    {{ $courses->total() }} khóa học
                </span>
            </h5>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#grantCourseModal">
                    <i class="fa-solid fa-gift me-1"></i> Tặng khóa học
                </button>
                <a href="{{ route('students.index') }}" class="btn btn-sm btn-secondary">
                    ← Quay lại
                </a>
            </div>
        </div>

        <div class="card-body">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Tên khóa học</th>
                        <th>Trạng thái</th>
                        <th>Ngày mua</th>
                        <th width="100">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($courses as $course)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $course->name }}</td>
                            <td>
                                @if ($course->pivot->status == 1)
                                    <span class="badge bg-success">Hoạt động</span>
                                @else
                                    <span class="badge bg-danger">Dừng hoạt động</span>
                                @endif
                            </td>
                            <td>
                                {{ $course->created_at?->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <button type="button" class="btn btn-outline-danger btn-sm btn-revoke" data-id="{{ $course->id }}" data-name="{{ $course->name }}">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                Học viên chưa mua khóa học nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
            <div class="mt-3">
                {{ $courses->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Tặng khóa học -->
    <div class="modal fade" id="grantCourseModal" tabindex="-1" aria-labelledby="grantCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="grantCourseForm" action="{{ route('students.grant-course', $student->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="grantCourseModalLabel">Tặng khóa học cho học viên</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="course_ids" class="form-label">Chọn khóa học muốn tặng</label>
                            <select class="form-control select2-courses" name="course_ids[]" id="course_ids" multiple="multiple" style="width: 100%">
                                @foreach($allCourses as $courseItem)
                                    <option value="{{ $courseItem->id }}">
                                        {{ $courseItem->name }} 
                                        - GV: {{ $courseItem->teacher->name ?? 'N/A' }} 
                                        {{ $courseItem->code ? '['.$courseItem->code.']' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text text-muted">Bạn có thể chọn nhiều khóa học cùng lúc. Danh sách hiển thị tất cả các khóa học đang hoạt động.</div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="send_email" id="send_email" value="1">
                                <label class="form-check-label fw-bold" for="send_email">
                                    <i class="fa-solid fa-envelope me-1 text-primary"></i> Gửi thông báo qua Email cho học viên
                                </label>
                            </div>
                            <small class="text-muted ms-4 d-block">Nếu tắt, học viên chỉ nhận được thông báo trong chuông thông báo của hệ thống.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmitGrant">Xác nhận tặng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.select2-courses').select2({
                dropdownParent: $('#grantCourseModal'),
                placeholder: 'Chọn hoặc tìm kiếm khóa học...',
                allowClear: true,
                language: {
                    noResults: function() {
                        return "Không tìm thấy khóa học nào";
                    }
                }
            });

            $('#grantCourseForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const btn = $('#btnSubmitGrant');
                const originalText = btn.text();

                btn.prop('disabled', true).text('Đang xử lý...');

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Thành công',
                                text: response.message,
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Lỗi',
                                text: response.message || 'Có lỗi xảy ra.',
                            });
                        }
                    },
                    error: function(xhr) {
                        let msg = 'Có lỗi xảy ra khi gửi yêu cầu.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi',
                            text: msg,
                        });
                    },
                    complete: function() {
                        btn.prop('disabled', false).text(originalText);
                    }
                });
            });

            $('.btn-revoke').on('click', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');

                Swal.fire({
                    title: 'Xác nhận thu hồi?',
                    text: `Bạn có chắc muốn thu hồi khóa học [${name}] từ học viên này không?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Đồng ý thu hồi',
                    cancelButtonText: 'Hủy'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('students.revoke-course', $student->id) }}',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                course_id: id
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('Đã thu hồi!', response.message, 'success').then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire('Lỗi!', response.message, 'error');
                                }
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
