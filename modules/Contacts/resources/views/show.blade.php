@extends('layouts.backend')

@section('content')
    @php
        $typeLabels = [
            'contact' => 'Liên hệ',
            'feedback' => 'Góp ý',
            'report' => 'Báo cáo',
        ];

        $statusLabels = [
            'new' => 'Mới gửi',
            'in_progress' => 'Đang xử lý',
            'need_info' => 'Cần thêm thông tin',
            'resolved' => 'Đã giải quyết',
            'rejected' => 'Đã từ chối',
        ];

        $categoryLabels = [
            'general_contact' => 'Liên hệ chung',
            'feature_request' => 'Tính năng mới',
            'ui_ux' => 'UI/UX',
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
    @endphp

    @php
        $isSupport = ($mode ?? 'contact') === 'support';
        $backRoute = $isSupport ? route('contacts.support-index') : route('contacts.index');
    @endphp

    <div class="admin-form">
        <div class="admin-form__header">
            <div>
                <h5 class="mb-1">{{ $isSupport ? 'Chi tiết góp ý / báo cáo' : 'Chi tiết liên hệ' }}</h5>
                <p class="text-muted mb-0">
                    {{ $isSupport
                        ? 'Xem nhanh nội dung góp ý hoặc báo cáo và cập nhật trạng thái xử lý.'
                        : 'Xem nhanh nội dung liên hệ và cập nhật trạng thái xử lý.' }}
                </p>
            </div>
            <a href="{{ $backRoute }}" class="btn btn-light border">Quay lại danh sách</a>
        </div>

        @if (session('msg'))
            <div class="alert alert-success border-0 rounded-4">{{ session('msg') }}</div>
        @endif

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <table class="table align-middle mb-0">
                            <tbody>
                                <tr>
                                    <th style="width: 220px;">Người gửi</th>
                                    <td>{{ $contact->name }}</td>
                                </tr>
                                <tr>
                                    <th>Loại gửi</th>
                                    <td>{{ $typeLabels[$contact->submission_type] ?? 'Khác' }}</td>
                                </tr>
                                <tr>
                                    <th>Danh mục</th>
                                    <td>{{ $categoryLabels[$contact->category] ?? ($contact->category ?: '-') }}</td>
                                </tr>
                                <tr>
                                    <th>Số điện thoại</th>
                                    <td><a href="tel:{{ $contact->phone }}">{{ $contact->phone }}</a></td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>
                                        @if ($contact->email)
                                            <a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Tiêu đề</th>
                                    <td>{{ $contact->subject ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Nguồn gửi</th>
                                    <td>{{ $contact->source }}</td>
                                </tr>
                                <tr>
                                    <th>URL trang</th>
                                    <td class="text-break">{{ $contact->page_url ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Học viên</th>
                                    <td>{{ $contact->student?->name ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Giảng viên</th>
                                    <td>{{ $contact->teacher?->name_locale ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Thời gian gửi</th>
                                    <td>{{ $contact->created_at?->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Nội dung</th>
                                    <td>
                                        <div class="rounded-4 border bg-light p-3">
                                            {!! nl2br(e($contact->message)) !!}
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h6 class="mb-3">Cập nhật xử lý</h6>
                        <form method="POST" action="{{ route('contacts.update-status', $contact->id) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Trạng thái</label>
                                <select name="workflow_status" class="form-select">
                                    @foreach ($statusLabels as $value => $label)
                                        <option value="{{ $value }}" @selected(old('workflow_status', $contact->workflow_status) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ghi chú nội bộ</label>
                                <textarea name="admin_note" rows="6" class="form-control">{{ old('admin_note', $contact->admin_note) }}</textarea>
                            </div>
                            <button class="btn btn-primary w-100">Lưu trạng thái</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('stylesheets')
    <style>
        html[data-theme="dark"] .bg-light {
            background: #162033 !important;
            border-color: #2b3b53 !important;
            color: #e2e8f0;
        }

        html[data-theme="dark"] .table tbody th,
        html[data-theme="dark"] .table tbody td,
        html[data-theme="dark"] .table tbody a {
            color: #e2e8f0;
        }
    </style>
@endsection
