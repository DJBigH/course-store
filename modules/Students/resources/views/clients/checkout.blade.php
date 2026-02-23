@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    <section class="account-page py-5 bg-light checkout-page">
        <div class="container">
            {{-- Header --}}
            <div class="mb-4">
                <h2 class="fw-bold">
                    {{ __('students::clients/checkout.checkout.page_title') }}
                    <span class="text-primary"><a
                            href="{{ route('students.account.order-detail', ['locale' => app()->getLocale(), 'id' => $order->id]) }}">#{{ $order->code }}</a></span>
                    @if (config('checkout.checkout_countdown') > 0)
                        <span class="countdown"><span class="cd-minute">00</span>:<span class="cd-second">00</span>
                    @endif
                </h2>
                <p class="text-muted mb-0">
                    {{ __('students::clients/checkout.checkout.notice_complete_payment') }}
                </p>
            </div>

            <div class="row g-4">

                {{-- LEFT: ORDER INFO --}}
                <div class="col-lg-7">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body p-4">

                            <h5 class="fw-bold mb-3">
                                <i class="bi bi-receipt me-1 text-primary"></i>
                                {{ __('students::clients/checkout.checkout.order_info') }}
                            </h5>

                            <table class="table table-bordered align-middle mb-4">
                                <tbody>
                                    <tr>
                                        <th width="30%" class="bg-light">
                                            {{ __('students::clients/checkout.checkout.order_code') }}</th>
                                        <td>#{{ $order->code }}</td>
                                    </tr>

                                    <tr>
                                        <th class="bg-light">{{ __('students::clients/checkout.checkout.subtotal') }}</th>
                                        <td class="fw-semibold">
                                            {{ moneyLocale($order->total) }}
                                        </td>
                                    </tr>

                                    <tr>
                                        @if (!empty($order->discount))
                                            <th class="bg-light text-success">
                                                {{ __('students::clients/checkout.checkout.discount') }}
                                            </th>
                                            <td class="text-success fw-medium discount-value">
                                                - {{ moneyLocale($order->discount) }}
                                            </td>
                                        @else
                                            <th class="bg-light text-success">
                                                {{ __('students::clients/checkout.checkout.discount') }}
                                            </th>
                                            <td class="text-success fw-medium discount-value">
                                                @if ($locale = app()->getLocale())
                                                    @if ($locale === 'en')
                                                        -0 $
                                                    @else
                                                        -0 đ
                                                    @endif
                                                @endif
                                            </td>
                                        @endif
                                    </tr>

                                    <tr>
                                        <th class="bg-light">{{ __('students::clients/checkout.checkout.order_time') }}
                                        </th>
                                        <td>{{ format_date_dmy($order->created_at) }}</td>
                                    </tr>

                                    <tr>
                                        <th class="bg-light">{{ __('students::clients/checkout.checkout.status') }}</th>
                                        <td>
                                            <span class="badge bg-{{ $order->status->color }} px-3 py-2">
                                                {{ $order->status->name }}
                                            </span>
                                        </td>
                                    </tr>

                                    {{-- Divider --}}
                                    <tr class="table-secondary">
                                        <th class="fw-bold">{{ __('students::clients/checkout.checkout.total_payment') }}
                                        </th>
                                        <td class="fw-bold text-danger fs-4 total_value">
                                            {{ moneyLocale($order->total - $order->discount) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>


                            <h5 class="fw-bold mb-3">
                                <i class="bi bi-journal-text me-1 text-success"></i>
                                {{ __('students::clients/checkout.checkout.course_details') }}
                            </h5>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('students::clients/checkout.checkout.course') }}</th>
                                            <th class="text-end">{{ __('students::clients/checkout.checkout.price') }}</th>
                                            <th>{{ __('students::clients/checkout.checkout.instructor') }}</th>
                                            <th class="text-center">
                                                {{ __('students::clients/checkout.checkout.course_status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($order->detail as $key => $item)
                                            <tr>
                                                <td class="text-muted">{{ $key + 1 }}</td>
                                                <td class="fw-semibold">
                                                    {{ $item?->courses?->name_locale }}
                                                </td>
                                                @if ($item?->courses?->sale_price)
                                                    <td class="text-end text-danger fw-semibold">
                                                        {{ moneyLocale($item?->courses?->sale_price) }}
                                                    </td>
                                                @else
                                                    <td class="text-end text-danger fw-semibold">
                                                        {{ moneyLocale($item?->courses?->price) }}
                                                    </td>
                                                @endif
                                                <td>
                                                    {{ $item?->courses?->teacher?->name }}
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        class="badge bg-{{ $item?->courses?->status ? 'success' : 'danger' }}-subtle 
                                                    text-{{ $item?->courses?->status ? 'success' : 'danger' }}">
                                                        {{ $item?->courses?->status ? __('students::clients/checkout.checkout.active') : __('students::clients/checkout.checkout.inactive') }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    <a href="{{ route('home', ['locale' => app()->getLocale()]) }}"
                                        class="btn btn-outline-primary btn-sm px-3 d-flex align-items-center">
                                        <i class="bi bi-arrow-left me-1"></i>
                                        {{ __('students::clients/checkout.checkout.back_home') }}
                                    </a>

                                    <a href="{{ route('courses.home', ['locale' => app()->getLocale()]) }}"
                                        class="btn btn-success btn-sm px-3 d-flex align-items-center">
                                        <i class="bi bi-plus-circle me-1"></i>
                                        {{ __('students::clients/checkout.checkout.buy_another_course') }}
                                    </a>
                                </div>
                            </div>

                            <hr>
                            <div class="mb-4">
                                <label
                                    class="form-label fw-semibold">{{ __('students::clients/checkout.checkout.choose_payment_method') }}</label>

                                <div class="form-check mb-2">
                                    <input class="form-check-input payment-method" type="radio" name="payment_method"
                                        value="bank" checked>
                                    <label class="form-check-label">
                                        {{ __('students::clients/checkout.checkout.qr_transfer') }}
                                    </label>
                                </div>

                                <div class="form-check mb-2">
                                    <input class="form-check-input payment-method" type="radio" name="payment_method"
                                        value="vnpay">
                                    <img src="{{ asset('clients/assets/vnpay.png') }}" alt="" style="width: 40px;">
                                    <label class="form-check-label">
                                        VNPay <strong
                                            style="color: red">({{ __('students::clients/checkout.checkout.maintenance') }})</strong>
                                    </label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input payment-method" type="radio" name="payment_method"
                                        value="momo">
                                    <img src="{{ asset('clients/assets/momo.png') }}" alt="" style="width: 30px;">
                                    <label class="form-check-label">
                                        MoMo <strong
                                            style="color: red">({{ __('students::clients/checkout.checkout.maintenance') }})</strong>
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
                                    {{ __('students::clients/checkout.checkout.bank_transfer') }}
                                </h5>
                                @include('students::clients.partials.coupons')
                                <ul class="list-unstyled small mb-3">
                                    <li>🏦 <strong>{{ __('students::clients/checkout.checkout.bank_name') }}:</strong>
                                        Techcombank</li>
                                    <li>
                                        🔢 <strong>{{ __('students::clients/checkout.checkout.bank_account') }}:</strong>
                                        <span class="copy-text" data-copy="61043040524">61043040524</span>
                                        <i class="bank-copy fa-regular fa-copy"></i>
                                    </li>
                                    <li>👤
                                        <strong>{{ __('students::clients/checkout.checkout.bank_account_name') }}:</strong>
                                        {{ __('students::clients/checkout.checkout.bank_account_name_bank') }}
                                    </li>
                                    <li>💰 <strong>{{ __('students::clients/checkout.checkout.amount') }}:</strong>
                                        <span
                                            class="text-danger fw-bold total_value">{{ moneyLocale($order->total - $order->discount) }}</span>
                                    </li>
                                    <li>
                                        📝
                                        <strong>{{ __('students::clients/checkout.checkout.transfer_content') }}:</strong>
                                        <span class="copy-text"
                                            data-copy="{{ __('students::clients/checkout.checkout.transfer_note_qr') }} {{ $order->code }}">
                                            {{ __('students::clients/checkout.checkout.transfer_note_qr') }}
                                            {{ $order->code }}
                                        </span>
                                        <i class="bank-copy fa-regular fa-copy"></i>
                                    </li>
                                </ul>

                                {{-- QR --}}
                                {{-- QR --}}
                                <div class="text-center my-4">
                                    <div class="border rounded-3 p-3 bg-light d-inline-block">
                                        <img id="vietqr-img"
                                            src="https://img.vietqr.io/image/techcombank-61043040524-compact2.jpg?amount={{ $order->total - $order->discount }}&addInfo={{ rawurlencode(__('students::clients/checkout.checkout.transfer_note_qr') . ' ' . $order->code) }}"
                                            class="img-fluid mb-2 qr-image" style="max-width: 220px" alt="VietQR">
                                        <div>
                                            <button type="button" class="btn btn-outline-primary btn-sm download-qr">
                                                <i class="bi bi-download me-1"></i>
                                                {{ __('students::clients/checkout.checkout.download_qr') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- NOTE --}}
                                <div class="alert alert-warning small">
                                    <i class="bi bi-info-circle me-1"></i>
                                    {{ __('students::clients/checkout.checkout.after_transfer_notice') }}
                                    <strong>{{ __('students::clients/checkout.checkout.confirm_paid') }}</strong>
                                    {{ __('students::clients/checkout.checkout.complete_order_notice') }}
                                </div>

                                {{-- BUTTON --}}
                                <form method="POST"
                                    action="{{ route('students.account.checkout-payment', ['locale' => app()->getLocale(), 'id' => $order->id]) }}">
                                    @csrf
                                    <button class="btn btn-success w-100 py-2 fw-semibold">
                                        <i class="bi bi-check-circle me-1"></i>
                                        {{ __('students::clients/checkout.checkout.i_have_paid') }}
                                    </button>
                                </form>

                                {{-- NÚT HỦY ĐƠN --}}
                                @if (!$order->status->is_success)
                                    <form method="POST" class="mt-2 js-cancel-order"
                                        data-confirm="{{ __('students::clients/checkout.checkout.cancel_confirm') }}"
                                        action="{{ route('students.account.checkout-cancel', [
                                            'locale' => app()->getLocale(),
                                            'id' => $order->id,
                                        ]) }}">
                                        @csrf

                                        <button type="submit" class="btn btn-outline-danger w-100 py-2 fw-semibold">
                                            <i class="bi bi-x-circle me-1"></i>
                                            {{ __('students::clients/checkout.checkout.cancel_order') }}
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <div id="payment-vnpay" class="d-none">
                                @include('students::clients.partials.coupons')
                                <p class="text-muted small">
                                    {{ __('students::clients/checkout.checkout.vnpay_notice') }}
                                </p>

                                <form method="POST" action="#">
                                    @csrf
                                    <button class="btn btn-primary w-100">
                                        {{ __('students::clients/checkout.checkout.pay_with_vnpay') }}
                                        ({{ __('students::clients/checkout.checkout.maintenance') }})
                                    </button>
                                </form>
                            </div>

                            <div id="payment-momo" class="d-none">
                                @include('students::clients.partials.coupons')
                                <p class="text-muted small">
                                    {{ __('students::clients/checkout.checkout.momo_notice') }}
                                </p>

                                <form method="POST" action="#">
                                    @csrf
                                    <button class="btn btn-danger w-100">
                                        {{ __('students::clients/checkout.checkout.pay_with_momo') }}
                                        ({{ __('students::clients/checkout.checkout.maintenance') }})
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
