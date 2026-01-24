@extends('layouts.backend')

@section('content')
    <div class="card shadow-sm">

        {{-- Thông báo --}}
        @if (session('msg'))
            <div class="alert alert-success">{{ session('msg') }}</div>
        @endif
        @if (session('msg_danger'))
            <div class="alert alert-danger">{{ session('msg_danger') }}</div>
        @endif

        <div class="card-header d-flex justify-content-between">
            <h5>
                📚 Cấp mã cho khóa học
                <span class="text-primary">({{ $coupon->code }})</span>
            </h5>
            <a href="{{ route('coupons.index') }}" class="btn btn-sm btn-secondary">
                ← Quay lại
            </a>
        </div>

        <form method="POST" action="">
            @csrf

            <div class="card-body">
                {{-- Thông tin mã --}}
                <div class="alert alert-info">
                    <strong>Loại giảm:</strong>
                    @if ($coupon->discount_type === 'percent')
                        %
                    @else
                        Giá trị
                    @endif
                    <br>
                    <strong>Giá trị:</strong> {{ money($coupon->discount_value) }} <br>
                    <strong>Số lượng:</strong>
                    @if (!empty($coupon->count))
                        {{ $coupon->count }}
                    @else
                        Không giới hạn
                    @endif
                    <br>
                    <strong>Tối thiểu:</strong>
                    @if (!empty($coupon->total_condition))
                        {{ money($coupon->total_condition) }}
                    @else
                        Chưa thiết lập
                    @endif
                    <br>
                    <strong>Hạn dùng:</strong>
                    @if (!empty($coupon->start_date) && !empty($coupon->end_date))
                        {{ $coupon->start_date }} → {{ $coupon->end_date }}
                    @else
                        Chưa thiết lập
                    @endif
                </div>

                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="checkAll">
                            </th>
                            <th>Tên khóa học</th>
                            <th>Giá</th>
                            <th>Giá khuyễn mãi</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($courses as $course)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" name="courses[]" value="{{ $course->id }}"
                                        {{ in_array($course->id, $assignedCourseIds) ? 'checked' : '' }}>
                                </td>
                                <td>{{ $course->name }}</td>
                                <td>{{ money($course->price) }}</td>
                                <td>{{ money($course->sale_price) }}</td>
                                <td>
                                    @if (in_array($course->id, $assignedCourseIds))
                                        <span class="badge bg-success">Đã áp dụng</span>
                                    @else
                                        <span class="badge bg-secondary">Chưa áp dụng</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

            <div class="card-footer text-end">
                <button class="btn btn-primary">
                    💾 Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        document.getElementById('checkAll').addEventListener('change', function() {
            document.querySelectorAll('input[name="courses[]"]').forEach(cb => {
                cb.checked = this.checked;
            });
        });
    </script>
@endsection
