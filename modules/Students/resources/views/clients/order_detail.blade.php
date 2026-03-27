@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    <section class="account-page account-order-detail-page py-4">
        <div class="container">
            <div class="row">

                {{-- Sidebar --}}
                <div class="col-lg-3 mb-4">
                    <div class="account-sidebar">
                        @include('students::clients.menu')
                    </div>
                </div>

                {{-- Content --}}
                <div class="col-lg-9">
                    <div class="card shadow-sm border-0 account-order-detail-content">
                        <div class="card-body p-4 order-detail" id="order-print">

                            {{-- Header --}}
                            <div class="d-flex align-items-center justify-content-between mb-4 order-detail-header">
                                <div>
                                    <h2 class="fw-bold mb-1">{{ __('students::clients/account.order_detail.title') }}</h2>
                                    <span class="text-muted">{{ __('students::clients/account.order_detail.code') }}:
                                        <strong>#{{ $order->code }}</strong></span>
                                </div>

                                <span
                                    class="badge bg-{{ $order->status->color }}-subtle 
                                    text-{{ $order->status->color }} px-3 py-2 fs-6">
                                    {{ $order->status->name_locale }}
                                </span>
                            </div>

                            {{-- Thông tin đơn hàng --}}
                            <div class="mb-4">
                                <h5 class="fw-semibold mb-3">
                                    <i class="bi bi-receipt me-1 text-primary"></i>
                                    {{ __('students::clients/account.order_detail.order_info') }}
                                </h5>

                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle mb-0 order-detail-summary-table">
                                        <tbody>
                                            <tr class="order-detail-summary-row">
                                                <th width="30%">
                                                    {{ __('students::clients/account.order_detail.order_code') }}</th>
                                                <td>#{{ $order->code }}</td>
                                            </tr>

                                            <tr class="order-detail-summary-row">
                                                <th>
                                                    {{ __('students::clients/account.order_detail.subtotal') }}</th>
                                                <td>
                                                    <span class="fw-semibold">
                                                        {{ moneyLocale($order->total) }}
                                                    </span>
                                                </td>
                                            </tr>

                                            <tr class="order-detail-summary-row">
                                                <th>
                                                    {{ __('students::clients/account.order_detail.coupon_discount') }}
                                                </th>
                                                <td class="text-danger fw-semibold">
                                                    -{{ moneyLocale($order->discount ?? 0) }}
                                                </td>
                                            </tr>

                                            <tr class="table-success order-detail-summary-row">
                                                <th class="fw-bold">
                                                    {{ __('students::clients/account.order_detail.total_payment') }}</th>
                                                <td class="fw-bold fs-5 text-success">
                                                    {{ moneyLocale($order->total - ($order->discount ?? 0)) }}
                                                </td>
                                            </tr>

                                            <tr class="order-detail-summary-row">
                                                <th>
                                                    {{ __('students::clients/account.order_detail.ordered_at') }}</th>
                                                <td>{{ format_date_dmy($order->created_at) }}</td>
                                            </tr>

                                            <tr class="order-detail-summary-row">
                                                <th>
                                                    {{ __('students::clients/account.order_detail.status') }}</th>
                                                <td>
                                                    <span class="badge bg-{{ $order->status->color }}">
                                                        {{ $order->status->name_locale }}
                                                    </span>

                                                    @if ($order->status->color == 'warning')
                                                        @if ($order->expired && !$order->payment_date)
                                                            <span
                                                                class="badge bg-danger ms-1">{{ __('students::clients/account.order_detail.payment_expired_at') }}</span>
                                                        @else
                                                            <a href="{{ route('students.account.checkout', ['locale' => app()->getLocale(), 'id' => $order->id]) }}"
                                                                class="btn btn-success btn-sm ms-2">
                                                                {{ __('students::clients/account.order_detail.pay') }}
                                                            </a>
                                                        @endif
                                                    @endif
                                                </td>
                                            </tr>

                                            <tr class="order-detail-summary-row">
                                                <th>Phương thức thanh toán</th>
                                                <td>
                                                    <span class="badge rounded-pill" style="{{ $order->payment_method_badge_style }}">
                                                        {{ $order->payment_method_label }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    @if ($order->discount)
                                        <div class="alert alert-success d-flex align-items-center mt-3">
                                            <i class="bi bi-ticket-perforated me-2 fs-5"></i>
                                            <div>
                                                <strong>{{ __('students::clients/account.order_detail.coupon_applied') }}</strong><br>
                                                {{ __('students::clients/account.order_detail.discount') }}
                                                {{ moneyLocale($order->discount) }}
                                                {{ __('students::clients/account.order_detail.for_order') }}
                                            </div>
                                        </div>
                                    @endif

                                </div>
                            </div>

                            {{-- Chi tiết khóa học --}}
                            <div class="mb-4">
                                <h5 class="fw-semibold mb-3">
                                    <i class="bi bi-journal-text me-1 text-success"></i>
                                    {{ __('students::clients/account.order_detail.course_info') }}
                                </h5>

                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="5%" class="text-center">#</th>
                                                <th>{{ __('students::clients/account.order_detail.course_name') }}</th>
                                                <th class="text-end">
                                                    {{ __('students::clients/account.order_detail.price') }}</th>
                                                <th>{{ __('students::clients/account.order_detail.instructor') }}</th>
                                                <th class="text-center">
                                                    {{ __('students::clients/account.order_detail.course_status') }}</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @forelse ($order->detail as $key => $item)
                                                <tr>
                                                    <td class="text-center text-muted" data-label="#"> {{ $key + 1 }}</td>
                                                    <td class="fw-semibold"
                                                        data-label="{{ __('students::clients/account.order_detail.course_name') }}">
                                                        {{ $item?->courses?->name_locale }}
                                                    </td>
                                                    <td class="text-end"
                                                        data-label="{{ __('students::clients/account.order_detail.price') }}">
                                                        @if ($item->courses?->sale_price)
                                                            <div class="text-muted text-decoration-line-through small">
                                                                {{ moneyLocale($item->courses->price) }}
                                                            </div>
                                                            <div class="fw-bold text-danger">
                                                                {{ moneyLocale($item->courses->sale_price) }}
                                                            </div>
                                                        @else
                                                            <div class="fw-bold">
                                                                {{ moneyLocale($item->courses->price) }}
                                                            </div>
                                                        @endif
                                                    </td>

                                                    <td data-label="{{ __('students::clients/account.order_detail.instructor') }}">
                                                        {{ $item?->courses?->teacher?->name }}
                                                    </td>
                                                    <td class="text-center"
                                                        data-label="{{ __('students::clients/account.order_detail.course_status') }}">
                                                        <span
                                                            class="badge bg-{{ $item?->courses?->status ? 'success' : 'danger' }}-subtle 
                                                            text-{{ $item?->courses?->status ? 'success' : 'danger' }}">
                                                            {{ $item?->courses?->status ? __('students::clients/account.order_detail.active') : __('students::clients/account.order_detail.inactive') }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr class="order-detail-courses-empty-row">
                                                    <td colspan="5" class="text-center text-muted py-4">
                                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                                        {{ __('students::clients/account.order_detail.no_detail') }}
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Action --}}
                            <div class="d-flex justify-content-end gap-2 order-detail-actions">
                                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-1"></i>
                                    {{ __('students::clients/account.order_detail.back') }}
                                </a>
                                <button class="btn btn-primary download-btn">
                                    <i class="bi bi-download me-1"></i>
                                    {{ __('students::clients/account.order_detail.download_invoice') }}
                                </button>
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
        .order-detail {
            font-size: 15px;
        }

        .order-detail h2 {
            letter-spacing: 0.5px;
        }

        .order-detail table th {
            font-weight: 500;
        }

        @media print {
            body {
                background: #fff !important;
            }

            .account-sidebar,
            .download-btn,
            .btn {
                display: none !important;
            }
        }

        .order-detail table th {
            background: #f8f9fa;
        }

        .order-detail .table td,
        .order-detail .table th {
            vertical-align: middle;
        }

        .order-detail .badge {
            font-size: 0.85rem;
        }

        .order-detail .table-success {
            --bs-table-bg: #e9f7ef;
        }

        .order-detail-summary-table th {
            background: #f8f9fa;
            color: #1f2937;
        }

        html[data-theme="dark"] .order-detail-summary-table th {
            background: rgba(37, 99, 235, 0.12);
            color: #dbeafe;
        }

        html[data-theme="dark"] .order-detail-summary-table td {
            color: #f8fafc;
        }

        html[data-theme="dark"] .order-detail-summary-table .table-success th {
            background: rgba(16, 185, 129, 0.2);
            color: #dcfce7;
        }

        .order-detail.pdf-export {
            background: #ffffff !important;
            color: #0f172a !important;
            padding: 0 !important;
        }

        .order-detail.pdf-export h2,
        .order-detail.pdf-export h5,
        .order-detail.pdf-export .fw-bold,
        .order-detail.pdf-export .fw-semibold,
        .order-detail.pdf-export .text-primary,
        .order-detail.pdf-export .text-success,
        .order-detail.pdf-export .text-danger,
        .order-detail.pdf-export .text-muted,
        .order-detail.pdf-export .badge,
        .order-detail.pdf-export td,
        .order-detail.pdf-export th,
        .order-detail.pdf-export span,
        .order-detail.pdf-export div,
        .order-detail.pdf-export a,
        .order-detail.pdf-export p,
        .order-detail.pdf-export i {
            color: inherit !important;
            opacity: 1 !important;
            text-shadow: none !important;
            filter: none !important;
        }

        .order-detail.pdf-export .order-detail-header .badge {
            color: #15803d !important;
            background: rgba(22, 163, 74, 0.16) !important;
            border: 1px solid rgba(22, 163, 74, 0.2);
        }

        .order-detail.pdf-export .table,
        .order-detail.pdf-export .table td,
        .order-detail.pdf-export .table th {
            background: #ffffff !important;
            border-color: #dbe4f0 !important;
        }

        .order-detail.pdf-export .order-detail-summary-table th {
            background: #eef4ff !important;
            color: #1e3a8a !important;
        }

        .order-detail.pdf-export .order-detail-summary-table .table-success {
            background: #e8f8ee !important;
        }

        .order-detail.pdf-export .order-detail-summary-table .table-success th,
        .order-detail.pdf-export .order-detail-summary-table .table-success td {
            color: #166534 !important;
            background: transparent !important;
        }

        .order-detail.pdf-export .table-light th {
            background: #eef4ff !important;
            color: #1e3a8a !important;
        }

        .order-detail.pdf-export .badge[class*="bg-success"],
        .order-detail.pdf-export .badge[class*="text-success"] {
            color: #166534 !important;
        }

        .order-detail.pdf-export .badge[class*="bg-danger"],
        .order-detail.pdf-export .badge[class*="text-danger"] {
            color: #b91c1c !important;
        }

        .order-detail.pdf-export .order-detail-actions,
        .order-detail.pdf-export .download-btn {
            display: none !important;
        }

        @media (max-width: 767.98px) {
            .account-order-detail-content .card-body {
                padding: 1.25rem !important;
            }

            .order-detail-header {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 12px;
            }

            .order-detail-header .badge {
                align-self: flex-start;
            }

            .order-detail .table-responsive {
                overflow: visible !important;
            }

            .order-detail-summary-table,
            .order-detail-summary-table tbody,
            .order-detail-summary-table tr,
            .order-detail-summary-table th,
            .order-detail-summary-table td {
                display: block;
                width: 100%;
            }

            .order-detail-summary-table {
                border: 1px solid rgba(148, 163, 184, 0.14);
                border-radius: 18px;
                overflow: hidden;
            }

            .order-detail-summary-table tr + tr {
                border-top: 1px solid rgba(148, 163, 184, 0.14);
            }

            .order-detail-summary-table tr {
                padding: 12px 14px;
            }

            .order-detail-summary-table th,
            .order-detail-summary-table td {
                border: 0;
                padding: 0;
            }

            .order-detail-summary-table th {
                font-size: 0.82rem;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                color: #94a7c0;
                margin-bottom: 6px;
                background: transparent !important;
                box-shadow: none !important;
            }

            .order-detail-summary-table td {
                font-weight: 600;
                background: transparent !important;
                box-shadow: none !important;
            }

            .order-detail-summary-table .table-success {
                background: rgba(16, 185, 129, 0.16);
            }

            .order-detail-summary-table .table-success th {
                color: #bbf7d0;
            }

            .order-detail-summary-table .table-success th,
            .order-detail-summary-table .table-success td {
                background: transparent !important;
            }

            .order-detail .table-hover thead {
                display: none;
            }

            .order-detail .table-hover,
            .order-detail .table-hover tbody,
            .order-detail .table-hover tr,
            .order-detail .table-hover td {
                display: block;
                width: 100%;
            }

            .order-detail .table-hover tbody {
                display: grid;
                gap: 14px;
            }

            .order-detail .table-hover tbody tr {
                border: 1px solid rgba(148, 163, 184, 0.14);
                border-radius: 18px;
                overflow: hidden;
                background: #ffffff;
                box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
            }

            .order-detail .table-hover tbody td {
                border: 0;
                padding: 12px 14px;
                text-align: left !important;
            }

            .order-detail .table-hover tbody td + td {
                border-top: 1px solid rgba(148, 163, 184, 0.14);
            }

            .order-detail .table-hover tbody td::before {
                content: attr(data-label);
                display: block;
                margin-bottom: 6px;
                color: #94a7c0;
                font-size: 0.78rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.04em;
            }

            .order-detail .table-hover tbody td[data-label="#"]::before,
            .order-detail-courses-empty-row td::before {
                display: none;
            }

            .order-detail-actions {
                flex-direction: column-reverse;
            }

            .order-detail-actions .btn {
                width: 100%;
                justify-content: center;
            }

            .order-detail .alert {
                padding: 0.9rem 1rem;
                align-items: flex-start !important;
            }

            html[data-theme="dark"] .order-detail .table-hover tbody tr {
                background: rgba(15, 23, 42, 0.92);
                box-shadow: 0 12px 30px rgba(2, 6, 23, 0.24);
            }
        }

        @media (max-width: 420px) {
            .order-detail-summary-table tr {
                display: grid;
                grid-template-columns: minmax(110px, 42%) minmax(0, 1fr);
                gap: 12px;
                align-items: start;
            }

            .order-detail-summary-table th,
            .order-detail-summary-table td {
                width: auto;
            }

            .order-detail-summary-table th {
                margin-bottom: 0;
            }

            .order-detail-summary-table td {
                text-align: right;
                word-break: break-word;
            }

            .order-detail-summary-table .table-success {
                display: grid;
                grid-template-columns: minmax(110px, 42%) minmax(0, 1fr);
                gap: 12px;
                align-items: center;
                background: rgba(16, 185, 129, 0.16);
            }

            .order-detail-summary-table .table-success th,
            .order-detail-summary-table .table-success td {
                width: auto;
                margin: 0;
                background: transparent !important;
            }

            .order-detail-summary-table .table-success th {
                color: #bbf7d0;
            }

            .order-detail-summary-table .table-success td {
                text-align: right;
            }

            .order-detail-summary-table .table-success td {
                font-size: 1.1rem !important;
            }
        }
    </style>
@endsection
