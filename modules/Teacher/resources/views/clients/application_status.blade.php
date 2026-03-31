@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    @php
        $statusClass = match ($application->status) {
            'approved' => 'success',
            'rejected' => 'danger',
            'pending_payment' => 'warning',
            default => 'info',
        };
    @endphp

    <section class="teacher-application-status py-5">
        <div class="container">
            <div class="teacher-status-shell">
                <div class="teacher-status-header">
                    <div>
                        <span class="teacher-status-kicker">Application Tracking</span>
                        <h2>Đơn đăng ký giảng viên</h2>
                        <p class="mb-0">Bạn đang đứng ở đúng màn theo dõi hồ sơ rồi. Chưa được duyệt thì chưa vào `/teacher`, nên flow sẽ đỡ rối hơn nhiều.</p>
                    </div>
                    @if (in_array($application->status, ['rejected', 'pending_payment', 'draft'], true))
                        <a href="{{ route('teacher.account.edit', ['locale' => app()->getLocale()]) }}" class="btn btn-outline-primary">
                            Chỉnh sửa đơn
                        </a>
                    @endif
                </div>

                @if (session('msg_success'))
                    <div class="alert alert-success">{{ session('msg_success') }}</div>
                @endif
                @if (session('msg_danger'))
                    <div class="alert alert-danger">{{ session('msg_danger') }}</div>
                @endif

                <div class="alert alert-{{ $statusClass }} teacher-status-alert">
                    <strong>Trạng thái hiện tại: {{ $application->display_status }}</strong>
                    @if ($application->status === 'pending_payment')
                        <span>Bạn đã chọn gói trả phí. Hoàn tất thanh toán rồi bấm xác nhận để đơn đi tiếp vào hàng chờ duyệt.</span>
                    @elseif ($application->status === 'pending_review')
                        <span>Admin đang xem hồ sơ của bạn. Giờ là lúc bình tĩnh chờ mail, đừng tự dọa mình bằng cách refresh 40 lần.</span>
                    @elseif ($application->status === 'approved')
                        <span>Chúc mừng, đơn đã được duyệt. Nếu tài khoản được tạo mới từ email đăng ký, thông tin đăng nhập đã được gửi qua mail.</span>
                    @elseif ($application->status === 'rejected')
                        <span>Đơn chưa được duyệt. Bạn xem ghi chú bên dưới, sửa lại cho gọn và gửi lại là ổn.</span>
                    @endif
                </div>

                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="teacher-status-card">
                            <h3 class="h5 fw-bold mb-3">Thông tin hồ sơ</h3>
                            <dl class="row mb-0">
                                <dt class="col-sm-5">Họ tên</dt>
                                <dd class="col-sm-7">{{ $application->full_name }}</dd>
                                <dt class="col-sm-5">Tên hiển thị</dt>
                                <dd class="col-sm-7">{{ $application->display_name ?: '-' }}</dd>
                                <dt class="col-sm-5">Email</dt>
                                <dd class="col-sm-7">{{ $application->email }}</dd>
                                <dt class="col-sm-5">Loại người nộp</dt>
                                <dd class="col-sm-7">{{ $application->applicant_type === 'guest' ? 'Khách ngoài hệ thống' : 'Học viên hiện tại' }}</dd>
                                <dt class="col-sm-5">Gửi lúc</dt>
                                <dd class="col-sm-7">{{ optional($application->submitted_at)->format('d/m/Y H:i') ?: '-' }}</dd>
                            </dl>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="teacher-status-card">
                            <h3 class="h5 fw-bold mb-3">Gói và thanh toán</h3>
                            <p class="mb-2 fw-semibold">{{ $application->package?->name ?: 'Chưa chọn gói' }}</p>
                            <p class="text-muted mb-2">{{ $application->package?->description }}</p>
                            <div class="teacher-status-meta">
                                <span>{{ $application->package ? money($application->package->price) : '-' }}</span>
                                @if ($application->coupon_code)
                                    <span>Mã {{ $application->coupon_code }} giảm {{ money($application->discount_amount ?? 0) }}</span>
                                @endif
                                @if ($application->package)
                                    <span>Thanh toán {{ money($application->payable_amount) }}</span>
                                @endif
                                <span>{{ $application->payment_method_label }}</span>
                                <span>Commission {{ $application->package?->commission_rate ?? 0 }}%</span>
                            </div>

                            @if ($application->status === 'pending_payment')
                                <div class="teacher-status-payment mt-3">
                                    <strong>Hướng dẫn nhanh</strong>
                                    <p class="mb-0">
                                        @if ($application->payment_method === 'vnpay')
                                            Đơn đang chờ thanh toán qua VNPay. Sau khi hoàn tất, quay lại đây và bấm xác nhận.
                                        @elseif ($application->payment_method === 'momo')
                                            Đơn đang chờ thanh toán qua MoMo. Sau khi hoàn tất, quay lại đây và bấm xác nhận.
                                        @else
                                            Đơn đang chờ chuyển khoản ngân hàng. Sau khi chuyển khoản xong, quay lại đây và bấm xác nhận.
                                        @endif
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                @if (!empty($application->bio))
                    <div class="teacher-status-card mt-4">
                        <h3 class="h5 fw-bold mb-3">Giới thiệu</h3>
                        <p class="mb-0">{{ $application->bio }}</p>
                    </div>
                @endif

                @if (!empty($application->admin_note))
                    <div class="teacher-status-card mt-4 teacher-status-card--danger">
                        <h3 class="h5 fw-bold mb-3 text-danger">Ghi chú từ admin</h3>
                        <p class="mb-0">{{ $application->admin_note }}</p>
                    </div>
                @endif

                <div class="d-flex flex-wrap gap-2 mt-4">
                    @if ($application->status === 'approved' && $application->teacher?->status === 'active')
                        <a href="{{ route('teacher.dashboard.index') }}" class="btn btn-primary">
                            Vào kênh giảng viên
                        </a>
                    @endif

                    @if ($application->status === 'pending_payment')
                        <form method="POST" action="{{ route('teacher.account.mark-paid', ['locale' => app()->getLocale()]) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                Tôi đã thanh toán
                            </button>
                        </form>
                    @endif

                    @if (in_array($application->status, ['rejected', 'pending_payment', 'draft'], true))
                        <a href="{{ route('teacher.account.edit', ['locale' => app()->getLocale()]) }}" class="btn btn-outline-primary">
                            Cập nhật hồ sơ
                        </a>
                    @endif

                    <a href="{{ route('teacher.portal.index', ['locale' => app()->getLocale()]) }}" class="btn btn-outline-secondary">
                        Quay lại landing page
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('stylesheets')
    <style>
        .teacher-status-shell {
            max-width: 1100px;
            margin: 0 auto;
        }

        .teacher-status-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .teacher-status-kicker {
            display: inline-flex;
            padding: 0.45rem 0.9rem;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.1);
            color: #2563eb;
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .teacher-status-header h2 {
            margin-top: 1rem;
            font-size: clamp(2rem, 3vw, 2.8rem);
            font-weight: 800;
        }

        .teacher-status-alert {
            display: grid;
            gap: 0.45rem;
        }

        .teacher-status-card {
            padding: 1.35rem;
            border-radius: 26px;
            background: var(--card-bg-color, #fff);
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.08);
        }

        .teacher-status-card--danger {
            border: 1px solid rgba(220, 38, 38, 0.18);
        }

        .teacher-status-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            color: #2563eb;
            font-weight: 700;
        }

        .teacher-status-payment {
            padding: 1rem 1.1rem;
            border-radius: 18px;
            background: rgba(245, 158, 11, 0.12);
        }
    </style>
@endsection
