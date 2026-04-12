@extends('layouts.teacher')

@section('content')
    @php
        $typeLabels = [
            'feedback' => 'Góp ý',
            'report' => 'Báo cáo',
        ];

        $categoryLabels = [
            'feature_request' => 'Tính năng mới',
            'ui_ux' => 'Giao diện / UX',
            'teacher_portal' => 'Teacher portal',
            'student_portal' => 'Student portal',
            'payment_package' => 'Thanh toán / gói',
            'system_bug' => 'Lỗi hệ thống',
            'course_lesson' => 'Khóa học / bài học',
            'comment_rating' => 'Bình luận / đánh giá',
            'content_violation' => 'Nội dung vi phạm',
            'account' => 'Tài khoản',
            'other' => 'Khác',
        ];

        $statusLabels = [
            'new' => 'Mới gửi',
            'in_progress' => 'Đang xử lý',
            'need_info' => 'Cần thêm thông tin',
            'resolved' => 'Đã giải quyết',
            'rejected' => 'Đã từ chối',
        ];
    @endphp

    <div class="teacher-panel">
        <div class="teacher-section-title mb-4">
            <div>
                <h3 class="fw-bold mb-2">{{ __('teacher::dashboard.pages.support') }}</h3>
                <p class="text-muted mb-0">Gửi đề xuất phát triển sản phẩm hoặc báo cáo sự cố trực tiếp cho admin ngay trong khu giảng viên.</p>
            </div>
        </div>

        @if (session('msg_success'))
            <div class="alert alert-success">{{ session('msg_success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="teacher-panel h-100">
                    <h4 class="h5 fw-bold mb-3">Gửi mới</h4>

                    <form method="POST" action="{{ route('teacher.dashboard.support.store') }}">
                        @csrf
                        <input type="hidden" name="page_url" value="{{ url()->current() }}">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Loại gửi</label>
                            <select name="submission_type" class="form-select">
                                <option value="feedback" @selected(old('submission_type', 'feedback') === 'feedback')>Góp ý</option>
                                <option value="report" @selected(old('submission_type') === 'report')>Báo cáo</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Danh mục</label>
                            <select name="category" class="form-select">
                                @foreach ($categoryLabels as $value => $label)
                                    <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tiêu đề ngắn</label>
                            <input
                                type="text"
                                name="subject"
                                class="form-control"
                                value="{{ old('subject') }}"
                                placeholder="Ví dụ: Cần thêm export doanh thu theo tháng"
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mô tả chi tiết</label>
                            <textarea
                                name="message"
                                rows="7"
                                class="form-control"
                                placeholder="Mô tả rõ ý tưởng hoặc vấn đề bạn đang gặp..."
                            >{{ old('message') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Gửi cho admin</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="teacher-panel h-100">
                    <h4 class="h5 fw-bold mb-3">Lịch sử đã gửi</h4>

                    @if ($items->isEmpty())
                        <div class="border rounded-3 p-4 text-muted">Bạn chưa gửi góp ý hoặc báo cáo nào.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Loại</th>
                                        <th>Danh mục</th>
                                        <th>Tiêu đề</th>
                                        <th>Trạng thái</th>
                                        <th>Gửi lúc</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $item)
                                        <tr>
                                            <td>{{ $typeLabels[$item->submission_type] ?? $item->submission_type }}</td>
                                            <td>{{ $categoryLabels[$item->category] ?? $item->category }}</td>
                                            <td class="fw-semibold">{{ $item->subject }}</td>
                                            <td>
                                                <span class="badge bg-secondary-subtle text-secondary-emphasis">
                                                    {{ $statusLabels[$item->workflow_status] ?? $item->workflow_status }}
                                                </span>
                                            </td>
                                            <td>{{ $item->created_at?->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $items->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
