@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    <section class="account-page py-5 bg-light checkout-page">
        <div class="container">
            {{-- Header --}}
            <div class="mb-4">
                <h2 class="fw-bold">
                    Thanh toán đơn hàng
                    <span class="text-primary"><a
                            href="{{ route('students.account.order-detail', $order->id) }}">#{{ $order->code }}</a></span>
                    @if (config('checkout.checkout_countdown') > 0)
                        <span class="countdown"><span class="cd-minute">00</span>:<span class="cd-second">00</span>
                    @endif
                </h2>
                <p class="text-muted mb-0">
                    Vui lòng hoàn tất thanh toán để kích hoạt khóa học
                </p>
            </div>

            <div class="row g-4">

                {{-- LEFT: ORDER INFO --}}
                <div class="col-lg-7">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body p-4">

                            <h5 class="fw-bold mb-3">
                                <i class="bi bi-receipt me-1 text-primary"></i>
                                Thông tin đơn hàng
                            </h5>

                            <table class="table table-bordered align-middle mb-4">
                                <tbody>
                                    <tr>
                                        <th width="30%" class="bg-light">Mã đơn hàng</th>
                                        <td>#{{ $order->code }}</td>
                                    </tr>

                                    <tr>
                                        <th class="bg-light">Tổng đơn hàng</th>
                                        <td class="fw-semibold">
                                            {{ money($order->total) }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th class="bg-light text-success">
                                            Giảm giá
                                        </th>
                                        <td class="text-success fw-medium discount-value">
                                            - {{ money($order->discount, freeText: '0 đ') }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th class="bg-light">Thời gian đặt</th>
                                        <td>{{ format_date_dmy($order->created_at) }}</td>
                                    </tr>

                                    <tr>
                                        <th class="bg-light">Trạng thái</th>
                                        <td>
                                            <span class="badge bg-{{ $order->status->color }} px-3 py-2">
                                                {{ $order->status->name }}
                                            </span>
                                        </td>
                                    </tr>

                                    {{-- Divider --}}
                                    <tr class="table-secondary">
                                        <th class="fw-bold">Tổng thanh toán</th>
                                        <td class="fw-bold text-danger fs-4 total_value">
                                            {{ money($order->total - $order->discount) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>


                            <h5 class="fw-bold mb-3">
                                <i class="bi bi-journal-text me-1 text-success"></i>
                                Chi tiết khóa học
                            </h5>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Khóa học</th>
                                            <th class="text-end">Giá</th>
                                            <th>Giảng viên</th>
                                            <th class="text-center">Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($order->detail as $key => $item)
                                            <tr>
                                                <td class="text-muted">{{ $key + 1 }}</td>
                                                <td class="fw-semibold">
                                                    {{ $item?->courses?->name }}
                                                </td>
                                                <td class="text-end text-danger fw-semibold">
                                                    {{ money($item?->courses?->price) }}
                                                </td>
                                                <td>
                                                    {{ $item?->courses?->teacher?->name }}
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        class="badge bg-{{ $item?->courses?->status ? 'success' : 'danger' }}-subtle 
                                                    text-{{ $item?->courses?->status ? 'success' : 'danger' }}">
                                                        {{ $item?->courses?->status ? 'Đang hoạt động' : 'Dừng' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    <a href="{{ route('home') }}"
                                        class="btn btn-outline-primary btn-sm px-3 d-flex align-items-center">
                                        <i class="bi bi-arrow-left me-1"></i>
                                        Quay lại trang chủ
                                    </a>

                                    <a href="{{ route('courses.home') }}"
                                        class="btn btn-success btn-sm px-3 d-flex align-items-center">
                                        <i class="bi bi-plus-circle me-1"></i>
                                        Mua khóa học khác
                                    </a>
                                </div>
                            </div>

                            <hr>
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Chọn hình thức thanh toán</label>

                                <div class="form-check mb-2">
                                    <input class="form-check-input payment-method" type="radio" name="payment_method"
                                        value="bank" checked>
                                    <label class="form-check-label">
                                        Chuyển khoản QR
                                    </label>
                                </div>

                                <div class="form-check mb-2">
                                    <input class="form-check-input payment-method" type="radio" name="payment_method"
                                        value="vnpay">
                                    <img src="{{ asset('clients/assets/vnpay.png') }}" alt="" style="width: 40px;">
                                    <label class="form-check-label">
                                        VNPay <strong style="color: red">(Bảo trì)</strong>
                                    </label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input payment-method" type="radio" name="payment_method"
                                        value="momo">
                                    <img src="{{ asset('clients/assets/momo.png') }}" alt="" style="width: 30px;">
                                    <label class="form-check-label">
                                        MoMo <strong style="color: red">(Bảo trì)</strong>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT: PAYMENT --}}
                <div class="col-lg-5">
                    <div class="card shadow border-0 sticky-top" style="top:90px">
                        <div class="card-body p-4">


                            <div id="payment-bank">
                                <h5 class="fw-bold mb-3">
                                    <i class="bi bi-credit-card me-1 text-success"></i>
                                    Thanh toán chuyển khoản
                                </h5>
                                @include('students::clients.partials.coupons')
                                <ul class="list-unstyled small mb-3">
                                    <li>🏦 <strong>Ngân hàng:</strong> Techcombank</li>
                                    <li>
                                        🔢 <strong>STK:</strong>
                                        <span class="copy-text" data-copy="61043040524">61043040524</span>
                                        <i class="bank-copy fa-regular fa-copy"></i>
                                    </li>
                                    <li>👤 <strong>Chủ TK:</strong> Nguyễn Duy Khánh</li>
                                    <li>💰 <strong>Số tiền:</strong>
                                        <span
                                            class="text-danger fw-bold total_value">{{ money($order->total - $order->discount) }}</span>
                                    </li>
                                    <li>
                                        📝 <strong>Nội dung:</strong>
                                        <span class="copy-text" data-copy="Thanh toan don {{ $order->code }}">
                                            Thanh toan don {{ $order->code }}
                                        </span>
                                        <i class="bank-copy fa-regular fa-copy"></i>
                                    </li>
                                </ul>

                                {{-- QR --}}
                                {{-- QR --}}
                                <div class="text-center my-4">
                                    <div class="border rounded-3 p-3 bg-light d-inline-block">
                                        <img id="vietqr-img"
                                            src="https://img.vietqr.io/image/techcombank-61043040524-compact2.jpg?amount={{ $order->total - $order->discount }}&addInfo={{ rawurlencode('Thanh toan don ' . $order->code) }}"
                                            class="img-fluid mb-2 qr-image" style="max-width: 220px" alt="VietQR">
                                        <div>
                                            <button type="button" class="btn btn-outline-primary btn-sm download-qr">
                                                <i class="bi bi-download me-1"></i>
                                                Tải QR
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- NOTE --}}
                                <div class="alert alert-warning small">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Sau khi chuyển khoản thành công, vui lòng nhấn
                                    <strong>“Tôi đã thanh toán”</strong> để hoàn tất đơn hàng.
                                </div>

                                {{-- BUTTON --}}
                                <form method="POST" action="">
                                    @csrf
                                    <button class="btn btn-success w-100 py-2 fw-semibold">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Tôi đã thanh toán
                                    </button>
                                </form>
                            </div>

                            <div id="payment-vnpay" class="d-none">
                                @include('students::clients.partials.coupons')
                                <p class="text-muted small">
                                    Bạn sẽ được chuyển đến cổng thanh toán VNPay để hoàn tất giao dịch.
                                </p>

                                <form method="POST" action="#">
                                    @csrf
                                    <button class="btn btn-primary w-100">
                                        Thanh toán bằng VNPay (Bảo trì)
                                    </button>
                                </form>
                            </div>

                            <div id="payment-momo" class="d-none">
                                @include('students::clients.partials.coupons')
                                <p class="text-muted small">
                                    Bạn sẽ được chuyển đến cổng thanh toán MoMo để hoàn tất giao dịch.
                                </p>

                                <form method="POST" action="#">
                                    @csrf
                                    <button class="btn btn-danger w-100">
                                        Thanh toán bằng MoMo (Bảo trì)
                                    </button>
                                </form>
                            </div>


                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection

@section('stylesheets')
    <style>
        .list-unstyled i {
            cursor: pointer;
        }

        .countdown {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 700;
            font-size: 1.4rem;
        }

        .countdown .cd-minute,
        .countdown .cd-second {
            min-width: 44px;
            padding: 6px 10px;
            text-align: center;
            border-radius: 8px;
            background: linear-gradient(135deg, #dc3545, #ff6b6b);
            color: #fff;
            box-shadow: 0 4px 8px rgba(25, 135, 84, 0.25);
            transition: all 0.3s ease;
        }

        /* dấu : */
        .countdown {
            color: #198754;
        }

        /* gần hết giờ → đổi màu cảnh báo */
        .countdown.warning .cd-minute,
        .countdown.warning .cd-second {
            background: linear-gradient(135deg, #ffc107, #ffdd57);
            color: #000;
        }

        /* hết giờ */
        .countdown.expired .cd-minute,
        .countdown.expired .cd-second {
            background: linear-gradient(135deg, #dc3545, #ff6b6b);
        }
    </style>
@endsection

@section('scripts')
    <script>
        document.querySelectorAll('.payment-method').forEach(el => {
            el.addEventListener('change', function() {
                document.getElementById('payment-bank').classList.add('d-none');
                document.getElementById('payment-vnpay').classList.add('d-none');
                document.getElementById('payment-momo').classList.add('d-none');

                if (this.value === 'bank') {
                    document.getElementById('payment-bank').classList.remove('d-none');
                }
                if (this.value === 'vnpay') {
                    document.getElementById('payment-vnpay').classList.remove('d-none');
                }
                if (this.value === 'momo') {
                    document.getElementById('payment-momo').classList.remove('d-none');
                }
            });
        });
    </script>
@endsection
