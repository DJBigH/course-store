@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    <section class="account-page account-orders-page py-4">
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
                    <div class="account-content card shadow-sm border-0 account-orders-content">
                        <div class="card-body p-4">

                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h2 class="fw-semibold mb-0">
                                    {{ __('students::clients/account.order.title') }}
                                </h2>
                            </div>

                            <div class="table-responsive" style="overflow-x: unset;" data-pagination-scroll
                                data-filter-block="account-my-orders">
                                <form method="GET"
                                    action="{{ route('students.account.my-order', ['locale' => app()->getLocale()]) }}"
                                    class="card shadow-sm border-0 mb-4 account-orders-filter js-smooth-filter"
                                    data-filter-block-target="account-my-orders">
                                    <div class="card-body">
                                        <div class="row g-3 align-items-end">

                                            <!-- Trạng thái -->
                                            <div class="col-lg-3 col-md-6">
                                                <label class="form-label fw-semibold">
                                                    <i class="bi bi-flag me-1 text-primary"></i>
                                                    {{ __('students::clients/account.order.status') }}
                                                </label>
                                                <select name="status_id" class="form-select js-select2">
                                                    <option value="">
                                                        {{ __('students::clients/account.order.all_status') }}</option>
                                                    @if (!empty($ordersStatus))
                                                        @foreach ($ordersStatus as $item)
                                                            <option value="{{ $item->id }}"
                                                                {{ request()->status_id == $item->id ? 'selected' : '' }}>
                                                                {{ $item->name_locale }}
                                                            </option>
                                                        @endforeach
                                                    @else
                                                        <option value="">
                                                            {{ __('students::clients/account.order.no_data') }}</option>
                                                    @endif
                                                </select>
                                            </div>

                                            <!-- Mã đơn hàng -->
                                            <div class="col-lg-3 col-md-6">
                                                <label class="form-label fw-semibold">
                                                    <i class="bi bi-upc-scan me-1 text-info"></i>
                                                    {{ __('students::clients/account.order.order_code') }}
                                                </label>
                                                <input type="text" name="code" class="form-control"
                                                    placeholder="{{ __('students::clients/account.order.placeholder_order_code') }}"
                                                    value="{{ request()->code }}">
                                            </div>


                                            <!-- Thời gian bắt đầu -->
                                            <div class="col-lg-3 col-md-6">
                                                <label class="form-label fw-semibold">
                                                    <i class="bi bi-calendar-event me-1 text-success"></i>
                                                    {{ __('students::clients/account.order.from_date') }}
                                                </label>
                                                <input type="date" name="start_date" class="form-control"
                                                    value="{{ request()->start_date }}">
                                            </div>

                                            <!-- Thời gian kết thúc -->
                                            <div class="col-lg-3 col-md-6">
                                                <label class="form-label fw-semibold">
                                                    <i class="bi bi-calendar-check me-1 text-danger"></i>
                                                    {{ __('students::clients/account.order.to_date') }}
                                                </label>
                                                <input type="date" name="end_date" class="form-control"
                                                    value="{{ request()->end_date }}">
                                            </div>

                                            <!-- Tổng tiền -->
                                            <div class="col-lg-3 col-md-6">
                                                <label class="form-label fw-semibold">
                                                    <i class="bi bi-cash-coin me-1 text-warning"></i>
                                                    {{ __('students::clients/account.order.total') }}
                                                </label>

                                                <!-- input hiển thị -->
                                                <input type="text" id="total_display" class="form-control"
                                                    placeholder="{{ __('students::clients/account.order.placeholder_total') }}"
                                                    value="{{ number_format(request()->total) }}">

                                                <!-- input gửi về backend -->
                                                <input type="hidden" name="total" id="total"
                                                    value="{{ request()->total }}">
                                            </div>

                                            <!-- Nút lọc -->
                                            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                                <a href="{{ url()->current() }}" class="btn btn-outline-secondary">
                                                    <i class="bi bi-arrow-counterclockwise me-1"></i>
                                                    {{ __('students::clients/account.order.reset') }}
                                                </a>
                                                <button type="submit" class="btn btn-primary px-4">
                                                    <i class="bi bi-funnel me-1"></i>
                                                    {{ __('students::clients/account.order.filter') }}
                                                </button>
                                            </div>

                                        </div>
                                    </div>
                                </form>


                                <div data-pagination-container="account-my-orders">
                                <table class="table table-hover align-middle mb-0 account-orders-table">
                                    <thead class="table-light text-uppercase small">
                                        <tr>
                                            <th class="text-center" style="width: 50px;">#</th>
                                            <th>{{ __('students::clients/account.order.table_order_code') }}</th>
                                            <th class="text-end">{{ __('students::clients/account.order.table_total') }}
                                            </th>
                                            <th class="text-center">
                                                {{ __('students::clients/account.order.table_status') }}</th>
                                            <th class="text-center">{{ __('students::clients/account.order.table_time') }}
                                            </th>
                                            <th class="text-center">
                                                {{ __('students::clients/account.order.table_action') }}</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse ($orders as $item)
                                            <tr>
                                                <td class="text-center text-muted" data-label="#">
                                                    {{ $loop->iteration }}
                                                </td>

                                                <td class="fw-semibold text-primary"
                                                    data-label="{{ __('students::clients/account.order.table_order_code') }}">
                                                    #{{ $item->code }}
                                                </td>
                                                @if ($item->discount)
                                                    <td class="text-end fw-semibold text-danger"
                                                        data-label="{{ __('students::clients/account.order.table_total') }}">
                                                        {{ moneyLocale($item->total - $item->discount) }}
                                                    </td>
                                                @else
                                                    <td class="text-end fw-semibold text-success"
                                                        data-label="{{ __('students::clients/account.order.table_total') }}">
                                                        {{ moneyLocale($item->total) }}
                                                    </td>
                                                @endif
                                                <td class="text-center"
                                                    data-label="{{ __('students::clients/account.order.table_status') }}">
                                                    <span
                                                        class="badge bg-{{ $item->status->color }}-subtle text-{{ $item->status->color }} px-3">
                                                        {{ $item->status->name_locale }}
                                                    </span>
                                                </td>

                                                <td class="text-center text-muted small"
                                                    data-label="{{ __('students::clients/account.order.table_time') }}">
                                                    {{ format_date_dmy($item->created_at) }}
                                                </td>

                                                <td class="text-center"
                                                    data-label="{{ __('students::clients/account.order.table_action') }}">
                                                    <a href="{{ route('students.account.order-detail', ['locale' => app()->getLocale(), 'id' => $item->id]) }}"
                                                        class="btn btn-outline-primary btn-sm px-3 account-orders-action-btn">
                                                        <i class="bi bi-eye"></i>
                                                        <span>{{ __('students::clients/account.order.table_action') }}</span>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr class="account-orders-empty-row">
                                                <td colspan="7" class="text-center py-5 text-muted account-orders-empty">
                                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                                    {{ __('students::clients/account.order.empty') }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>

                                </table>
                                    <div class="mt-2">
                                        {{ $orders->links('students::clients.pagination.boostrap') }}
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
    </section>
@endsection

@section('stylesheets')
    <style data-account-page-style>
        @media (max-width: 767.98px) {
            .account-orders-content .card-body {
                padding: 1.25rem !important;
            }

            .account-orders-content .table-responsive {
                overflow: visible !important;
            }

            .account-orders-filter .col-12,
            .account-orders-filter .col-lg-3,
            .account-orders-filter .btn {
                width: 100%;
            }

            .account-orders-table {
                min-width: 0;
                border-spacing: 0;
            }

            .account-orders-table thead {
                display: none;
            }

            .account-orders-table,
            .account-orders-table tbody,
            .account-orders-table tr,
            .account-orders-table td {
                display: block;
                width: 100%;
            }

            .account-orders-table tbody {
                display: grid;
                gap: 14px;
            }

            .account-orders-table tbody tr {
                background: #ffffff;
                border: 1px solid rgba(148, 163, 184, 0.14);
                border-radius: 18px;
                overflow: hidden;
                box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
            }

            .account-orders-table tbody td {
                padding: 12px 14px;
                border: 0;
                background: transparent;
                text-align: left !important;
            }

            .account-orders-table tbody td + td {
                border-top: 1px solid rgba(148, 163, 184, 0.14);
            }

            .account-orders-table tbody td::before {
                content: attr(data-label);
                display: block;
                margin-bottom: 6px;
                color: #94a7c0;
                font-size: 0.78rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.04em;
            }

            .account-orders-table tbody td[data-label="#"]::before {
                display: none;
            }

            .account-orders-empty-row td::before {
                display: none;
            }

            .account-orders-empty {
                display: grid;
                place-items: center;
                min-height: 120px;
            }

            .account-orders-table .badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 34px;
                border-radius: 999px;
                white-space: normal;
                text-align: center;
            }

            .account-orders-action-btn {
                width: 100%;
                min-height: 42px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                border-radius: 999px;
            }

            html[data-theme="dark"] .account-orders-table tbody tr {
                background: rgba(15, 23, 42, 0.92);
                box-shadow: 0 12px 30px rgba(2, 6, 23, 0.26);
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        const displayInputSafe = document.getElementById('total_display');
        const hiddenInputSafe = document.getElementById('total');

        if (displayInputSafe && hiddenInputSafe) {
            displayInputSafe.addEventListener('input', function() {
                let rawValue = this.value.replace(/[^\d]/g, '');
                hiddenInputSafe.value = rawValue;
                this.value = rawValue ? Number(rawValue).toLocaleString('en-US') : '';
            });
        }
    </script>
@endsection

@section('scripts')
    <script>
        const displayInput = document.getElementById('total_display');
        const hiddenInput = document.getElementById('total');

        displayInput.addEventListener('input', function() {
            // Lấy số sạch (chỉ giữ số)
            let rawValue = this.value.replace(/[^\d]/g, '');

            // Gán vào hidden input để submit
            hiddenInput.value = rawValue;

            // Format hiển thị
            if (rawValue) {
                this.value = Number(rawValue).toLocaleString('en-US');
            } else {
                this.value = '';
            }
        });
    </script>
@endsection
