@extends('layouts.backend')

@section('content')
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                Cấp mã cho học viên
                <span class="text-primary">({{ $coupon->code }})</span>
            </h5>
            <a href="{{ route('coupons.index') }}" class="btn btn-sm btn-secondary">
                Quay lại
            </a>
        </div>

        @if (session('msg'))
            <div class="alert alert-success alert-dismissible fade show m-3 mb-0">
                {{ session('msg') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('msg_danger'))
            <div class="alert alert-danger alert-dismissible fade show m-3 mb-0">
                {{ session('msg_danger') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="" method="POST">
            @csrf

            <div class="card-body">
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

                <div class="mb-3">
                    <label class="form-label fw-bold">Chọn học viên áp dụng mã</label>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="checkAll">
                                    </th>
                                    <th>Tên học viên</th>
                                    <th>Email</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($students as $student)
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" name="students[]" value="{{ $student->id }}"
                                                {{ in_array($student->id, $assignedStudentIds) ? 'checked' : '' }}>
                                        </td>
                                        <td>{{ $student->name }}</td>
                                        <td>{{ $student->email }}</td>
                                        <td>
                                            @if (in_array($student->id, $assignedStudentIds))
                                                <span class="badge bg-success">Đã cấp</span>
                                            @else
                                                <span class="badge bg-secondary">Chưa cấp</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card-footer text-end">
                <button class="btn btn-primary">
                    Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        document.getElementById('checkAll').addEventListener('change', function() {
            document.querySelectorAll('input[name="students[]"]').forEach((cb) => {
                cb.checked = this.checked;
            });
        });
    </script>
@endsection
