@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    <section class="account-page py-4">
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
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4 order-detail" id="order-print">

                            {{-- Header --}}
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div>
                                    <h2 class="fw-bold mb-1">Chi tiết đơn hàng</h2>
                                    <span class="text-muted">Mã đơn: <strong>#{{ $order->code }}</strong></span>
                                </div>

                                <span
                                    class="badge bg-{{ $order->status->color }}-subtle 
                                    text-{{ $order->status->color }} px-3 py-2 fs-6">
                                    {{ $order->status->name }}
                                </span>
                            </div>

                            {{-- Thông tin đơn hàng --}}
                            <div class="mb-4">
                                <h5 class="fw-semibold mb-3">
                                    <i class="bi bi-receipt me-1 text-primary"></i> Thông tin đơn hàng
                                </h5>

                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle mb-0">
                                        <tbody>
                                            <tr>
                                                <th width="30%" class="bg-light">Mã đơn hàng</th>
                                                <td>#{{ $order->code }}</td>
                                            </tr>

                                            <tr>
                                                <th class="bg-light">Tạm tính</th>
                                                <td>
                                                    <span class="fw-semibold">
                                                        {{ money($order->total) }}
                                                    </span>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th class="bg-light">
                                                    Giảm giá mã khuyến mãi
                                                </th>
                                                <td class="text-danger fw-semibold">
                                                    -{{ money($order->discount ?? 0) }}
                                                </td>
                                            </tr>

                                            <tr class="table-success">
                                                <th class="fw-bold">Tổng thanh toán</th>
                                                <td class="fw-bold fs-5 text-success">
                                                    {{ money($order->total - ($order->discount ?? 0)) }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <th class="bg-light">Thời gian đặt</th>
                                                <td>{{ format_date_dmy($order->created_at) }}</td>
                                            </tr>

                                            <tr>
                                                <th class="bg-light">Trạng thái</th>
                                                <td>
                                                    <span class="badge bg-{{ $order->status->color }}">
                                                        {{ $order->status->name }}
                                                    </span>

                                                    @if ($order->status->is_success == 0)
                                                        @if ($order->expired && !$order->payment_date)
                                                            <span class="badge bg-danger ms-1">Hết hạn thanh toán</span>
                                                        @else
                                                            <a href="{{ route('students.account.checkout', $order->id) }}"
                                                                class="btn btn-success btn-sm ms-2">
                                                                Thanh toán
                                                            </a>
                                                        @endif
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    @if ($order->discount)
                                        <div class="alert alert-success d-flex align-items-center mt-3">
                                            <i class="bi bi-ticket-perforated me-2 fs-5"></i>
                                            <div>
                                                <strong>Đã áp dụng mã khuyến mãi</strong><br>
                                                Giảm {{ money($order->discount) }} cho đơn hàng
                                            </div>
                                        </div>
                                    @endif

                                </div>
                            </div>

                            {{-- Chi tiết khóa học --}}
                            <div class="mb-4">
                                <h5 class="fw-semibold mb-3">
                                    <i class="bi bi-journal-text me-1 text-success"></i> Thông tin khóa học
                                </h5>

                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="5%" class="text-center">#</th>
                                                <th>Tên khóa học</th>
                                                <th class="text-end">Giá</th>
                                                <th>Giảng viên</th>
                                                <th class="text-center">Trạng thái</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @forelse ($order->detail as $key => $item)
                                                <tr>
                                                    <td class="text-center text-muted">{{ $key + 1 }}</td>
                                                    <td class="fw-semibold">
                                                        {{ $item?->courses?->name }}
                                                    </td>
                                                    <td class="text-end">
                                                        @if ($item->courses?->sale_price)
                                                            <div class="text-muted text-decoration-line-through small">
                                                                {{ money($item->courses->price) }}
                                                            </div>
                                                            <div class="fw-bold text-danger">
                                                                {{ money($item->courses->sale_price) }}
                                                            </div>
                                                        @else
                                                            <div class="fw-bold">
                                                                {{ money($item->courses->price) }}
                                                            </div>
                                                        @endif
                                                    </td>

                                                    <td>
                                                        {{ $item?->courses?->teacher?->name }}
                                                    </td>
                                                    <td class="text-center">
                                                        <span
                                                            class="badge bg-{{ $item?->courses?->status ? 'success' : 'danger' }}-subtle 
                                                            text-{{ $item?->courses?->status ? 'success' : 'danger' }}">
                                                            {{ $item?->courses?->status ? 'Đang hoạt động' : 'Dừng hoạt động' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">
                                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                                        Không có dữ liệu chi tiết
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Action --}}
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-1"></i> Quay lại
                                </a>
                                <button class="btn btn-primary download-btn">
                                    <i class="bi bi-download me-1"></i> Tải hóa đơn
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
    </style>
@endsection
