@extends('layouts.backend')

@section('content')
    <div class="card shadow-sm">

        <div class="card-header d-flex justify-content-between">
            <h5>
                🎓 Mã khuyến mãi của học viên:
                <span class="text-primary">{{ $student->name }}</span>

                <span class="badge bg-info ms-2">
                        {{ $student->coupons->count() }} mã
                    </span>
            </h5>

            <a href="{{ route('students.index') }}" class="btn btn-sm btn-secondary">
                ← Quay lại
            </a>
        </div>

        <div class="card-body">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Mã</th>
                        <th>Loại</th>
                        <th>Giá trị</th>
                        <th>Lượt còn</th>
                        <th>Trạng thái</th>
                        <th>Ngày cấp</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($student->coupons as $item)
                        @php
                            $used = $item->usages_count ?? 0;
                            $limited = !empty($item->count);
                            $remaining = $limited ? max($item->count - $used, 0) : null;
                        @endphp

                        <tr>
                            <td>{{ $item->code }}</td>
                            <td>{{ $item->discount_type === 'percent' ? '%' : 'Tiền' }}</td>

                            <td>
                                @if ($item->discount_type === 'percent')
                                    {{ $item->discount_value }}%
                                @else
                                    {{ money($item->discount_value) }}
                                @endif
                            </td>

                            <td>
                                @if ($limited)
                                    {{ $remaining }}
                                    <small class="text-muted">
                                        ({{ $used }}/{{ $item->count }})
                                    </small>
                                @else
                                    Không giới hạn
                                @endif
                            </td>

                            <td>
                                @if ($limited && $remaining <= 0)
                                    <span class="badge bg-danger">Hết lượt</span>
                                @else
                                    <span class="badge bg-success">Còn dùng</span>
                                @endif
                            </td>

                            <td>
                                {{ $item->pivot->created_at->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @empty

                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                Học viên chưa được cấp mã nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
@endsection
