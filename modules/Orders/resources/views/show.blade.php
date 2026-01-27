@extends('layouts.backend')

@section('content')
    {{-- Alert --}}
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

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">
                <i class="bi bi-receipt me-1"></i> Chi tiết đơn hàng
            </h4>

            <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>

        <div class="row">

            {{-- LEFT --}}
            <div class="col-lg-8">

                {{-- Order Info --}}
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
                                            {{ $order->status->name }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Thanh toán</th>
                                    <td>
                                        @if ($order->payment_date)
                                            <span class="badge bg-success">Đã thanh toán</span>
                                            <div class="small text-muted">
                                                {{ format_date_dmy($order->payment_complete_date) ?? '' }}
                                            </div>
                                        @else
                                            <span class="badge bg-warning">Chưa thanh toán</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Course Detail --}}
                <div class="card shadow-sm">
                    <div class="card-header fw-semibold">
                        Danh sách khóa học
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

            </div>

            {{-- RIGHT --}}
            <div class="col-lg-4">

                {{-- Customer --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header fw-semibold">
                        Thông tin khách hàng
                    </div>
                    <div class="card-body">
                        <p class="mb-1">
                            <strong>Họ tên:</strong> {{ $order->students->name ?? '-' }}
                        </p>
                        <p class="mb-1">
                            <strong>Email:</strong> {{ $order->students->email ?? '-' }}
                        </p>
                        <p class="mb-1">
                            <strong>SĐT:</strong> {{ $order->students->phone ?? '-' }}
                        </p>
                        <p class="mb-0">
                            <strong>Địa chỉ:</strong> {{ $order->students->address ?? '-' }}
                        </p>
                    </div>
                </div>

                {{-- Payment Summary --}}
                <div class="card shadow-sm">
                    <div class="card-header fw-semibold">
                        Tổng thanh toán
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered mb-0">
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
                                        {{ money($order->total - $order->discount) }}
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
