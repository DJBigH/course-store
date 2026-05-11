@extends('layouts.backend')

@section('content')
    @if (session('msg'))
        <div class="alert alert-success">{{ session('msg') }}</div>
    @endif
    @if (session('msg_danger'))
        <div class="alert alert-danger">{{ session('msg_danger') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            Vui lòng kiểm tra lại dữ liệu đã nhập.
        </div>
    @endif

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">
                <i class="bi bi-receipt me-1"></i> Chi tiết đơn hàng
            </h4>

            <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header fw-semibold">
                        Thông tin đơn hàng
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered mb-0">
                            <tbody>
                                <tr>
                                    <th width="30%">Mã đơn hàng</th>
                                    <td><strong>#{{ $order->code }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Ngày tạo</th>
                                    <td>{{ format_date_dmy($order->created_at) }}</td>
                                </tr>
                                <tr>
                                    <th>Trạng thái</th>
                                    <td>
                                        <span class="badge bg-{{ $order->status->color }}">
                                            {{ $order->status->name_locale }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Thanh toán</th>
                                    <td>
                                        @if ((int) $order->status_id === 2)
                                            <span class="badge bg-success">Đã thanh toán</span>
                                            @if ($order->payment_complete_date || $order->payment_date)
                                                <div class="small text-muted">
                                                    {{ format_date_dmy($order->payment_complete_date ?: $order->payment_date) }}
                                                </div>
                                            @endif
                                        @elseif ((int) $order->status_id === 4)
                                            <span class="badge bg-danger">Hủy thanh toán</span>
                                        @elseif ((int) $order->status_id === 3)
                                            <span class="badge bg-danger">Thanh toán thất bại</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Chưa thanh toán</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Phương thức thanh toán</th>
                                    <td>
                                        <span class="badge rounded-pill" style="{{ $order->payment_method_badge_style }}">
                                            {{ $order->payment_method_label }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($order->type === 'telegram_package')
                    <div class="card shadow-sm">
                        <div class="card-header fw-semibold">
                            Thông tin gói Telegram
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tên gói</th>
                                        <th>Thời hạn</th>
                                        <th class="text-end">Giá</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="fw-semibold">
                                            {{ $order->orderable->package->name_locale ?? $order->orderable->package->name ?? '-' }}
                                        </td>
                                        <td>
                                            {{ $order->orderable->package->duration_value }} {{ $order->orderable->package->duration_unit }}
                                        </td>
                                        <td class="text-end fw-bold text-primary">
                                            {{ money($order->total) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @elseif ($order->type === 'teacher_upgrade')
                    <div class="card shadow-sm">
                        <div class="card-header fw-semibold">
                            Thông tin gói giảng viên
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tên gói</th>
                                        <th class="text-end">Giá</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="fw-semibold">
                                            {{ $order->orderable->package->name ?? '-' }}
                                        </td>
                                        <td class="text-end fw-bold text-primary">
                                            {{ money($order->total) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="card shadow-sm">
                        <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                            <span>Danh sách khóa học</span>
                            @if($order->bundle_id && $order->bundle)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3">
                                    <i class="bi bi-layers-half me-1"></i> Combo: {{ $order->bundle->name }}
                                </span>
                            @endif
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Tên khóa học</th>
                                        <th class="text-end">Giá</th>
                                        <th class="text-end">Giá sale</th>
                                        <th>Giảng viên</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($order->detail as $key => $item)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td class="fw-semibold">
                                                {{ $item->courses->name ?? '-' }}
                                            </td>
                                            <td class="text-end">
                                                {{ money($item->courses->price ?? 0) }}
                                            </td>
                                            <td class="text-end text-danger fw-semibold">
                                                {{ money($item->courses->sale_price ?? $item->courses->price) }}
                                            </td>
                                            <td>
                                                {{ $item->courses->teacher->name ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header fw-semibold">
                        Thông tin khách hàng
                    </div>
                    <div class="card-body">
                        <p class="mb-1">
                            <strong>Họ tên:</strong> {{ $order->customer_name_display }}
                        </p>
                        <p class="mb-1">
                            <strong>Email:</strong> {{ $order->customer_email_display }}
                        </p>
                        <p class="mb-1">
                            <strong>SĐT:</strong> {{ $order->customer_phone_display }}
                        </p>
                        <p class="mb-0">
                            <strong>Địa chỉ:</strong> {{ $order->customer_address_display }}
                        </p>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header fw-semibold">
                        Tổng thanh toán
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered mb-0 order-summary-table">
                            <tbody>
                                <tr>
                                    <th>Tạm tính</th>
                                    <td class="text-end">
                                        {{ money($order->total) }}
                                    </td>
                                </tr>

                                @if ($order->discount > 0)
                                    <tr>
                                        <th class="text-danger">Áp mã giảm giá</th>
                                        <td class="text-end text-danger">
                                            -{{ money($order->discount) }}
                                        </td>
                                    </tr>
                                @endif

                                <tr class="table-success">
                                    <th class="fw-bold">Tổng thanh toán</th>
                                    <td class="fw-bold text-end text-success">
                                        {{ money($order->total - $order->discount, $order->currency ?: 'đ') }}
                                        @if($order->currency && $order->currency !== 'VND' && $order->base_total > 0)
                                            <div class="small text-muted fw-normal" style="font-size: 11px;">
                                                (~ {{ money($order->base_total, 'đ') }})
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('stylesheets')
    <style>
        html[data-theme='dark'] .order-summary-table .table-success,
        html[data-theme='dark'] .order-summary-table .table-success > th,
        html[data-theme='dark'] .order-summary-table .table-success > td {
            background: rgba(34, 197, 94, 0.18) !important;
            color: #dcfce7 !important;
        }

        html[data-theme='dark'] .order-summary-table .table-success th,
        html[data-theme='dark'] .order-summary-table .table-success td {
            border-color: rgba(74, 222, 128, 0.18) !important;
        }

        html[data-theme='dark'] .order-summary-table .table-success .fw-bold,
        html[data-theme='dark'] .order-summary-table .table-success th.fw-bold,
        html[data-theme='dark'] .order-summary-table .table-success td.fw-bold {
            color: #f0fdf4 !important;
            text-shadow: 0 0 0 rgba(0, 0, 0, 0);
        }

        html[data-theme='dark'] .order-summary-table .text-success {
            color: #86efac !important;
            font-weight: 800;
        }

        html[data-theme='dark'] .order-summary-table .text-danger {
            color: #f87171 !important;
        }

        html[data-theme='dark'] .card .table-light,
        html[data-theme='dark'] .card .table-light > th,
        html[data-theme='dark'] .card .table-light > td {
            background: rgba(30, 41, 59, 0.92) !important;
            color: #cbd5e1 !important;
            border-color: rgba(148, 163, 184, 0.18) !important;
        }

        html[data-theme='dark'] .card .table-bordered > :not(caption) > * > * {
            border-color: rgba(148, 163, 184, 0.18);
        }

        html[data-theme='dark'] .card .table-hover > tbody > tr:hover > * {
            background: rgba(30, 41, 59, 0.52);
            color: #f8fafc;
        }

        html[data-theme='dark'] .card .small.text-muted {
            color: #94a3b8 !important;
        }

        html[data-theme='dark'] .btn-outline-secondary {
            border-color: rgba(148, 163, 184, 0.24);
            color: #e2e8f0;
            background: rgba(15, 23, 42, 0.8);
        }

        html[data-theme='dark'] .btn-outline-secondary:hover {
            background: rgba(30, 41, 59, 0.96);
            color: #f8fafc;
        }
    </style>
@endsection
