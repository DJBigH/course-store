@extends('layouts.backend')

@section('content')
    <div class="card shadow-sm">

        <div class="card-header d-flex justify-content-between">
            <h5>
                📊 Lịch sử sử dụng mã:
                <span class="text-primary">{{ $coupon->code }}</span>
            </h5>
            <a href="{{ route('coupons.index') }}" class="btn btn-sm btn-secondary">
                ← Quay lại
            </a>
        </div>

        <div class="card-body">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Học viên</th>
                        <th>Email</th>
                        <th>Đơn hàng</th>
                        <th>Mã giảm giá</th>
                        <th>Thời gian</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($histories as $item)
                        <tr>
                            <td>{{ $item->students->name }}</td>
                            <td>{{ $item->students->email }}</td>
                            <td>#{{ $item->order->code ?? 'N/A' }}</td>
                            <td>{{ $item->coupon->code ?? 'N/A' }}</td>
                            <td>{{ $item->created_at }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                Chưa có học viên nào sử dụng mã
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
@endsection
