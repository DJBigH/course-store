@extends('layouts.backend')

@section('content')
    @if (session('msg_danger'))
        <div class="alert alert-danger border-0 rounded-4">{{ session('msg_danger') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger border-0 rounded-4">
            Vui lòng kiểm tra lại dữ liệu đã nhập.
        </div>
    @endif

    <form action="" method="POST" class="admin-form">
        @csrf

        <div class="admin-form__header">
            <div>
                <h5 class="mb-1">Cập nhật mã giảm giá</h5>
                <p class="text-muted mb-0">Điều chỉnh giá trị ưu đãi, số lượng và thời gian hiệu lực của coupon.</p>
            </div>
            <a href="{{ route('coupons.index') }}" class="btn btn-light border">Quay lại danh sách</a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Mã khuyến mãi</label>
                        <div class="input-group">
                            <input type="text" class="form-control {{ $errors->has('code') ? 'is-invalid' : '' }}"
                                name="code" id="couponCode" placeholder="VD: SALE50"
                                value="{{ old('code', $coupon->code) }}">
                            <button type="button" class="btn btn-outline-secondary" id="randomCodeBtn"
                                title="Tạo mã ngẫu nhiên">
                                <i class="fas fa-random"></i>
                            </button>
                        </div>
                        @error('code')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Loại giảm giá</label>
                        <select name="discount_type"
                            class="form-select {{ $errors->has('discount_type') ? 'is-invalid' : '' }}">
                            <option value="percent"
                                {{ old('discount_type', $coupon->discount_type) == 'percent' ? 'selected' : '' }}>
                                Phần trăm (%)
                            </option>
                            <option value="value"
                                {{ old('discount_type', $coupon->discount_type) == 'value' ? 'selected' : '' }}>
                                Giá trị (VNĐ)
                            </option>
                        </select>
                        @error('discount_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Giá trị giảm</label>
                        <input type="number" class="form-control {{ $errors->has('discount_value') ? 'is-invalid' : '' }}"
                            name="discount_value" placeholder="VD: 10 hoặc 50000"
                            value="{{ old('discount_value', $coupon->discount_value) }}">
                        @error('discount_value')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Giá trị đơn hàng tối thiểu</label>
                        <input type="text" class="form-control {{ $errors->has('total_condition') ? 'is-invalid' : '' }}"
                            id="total_condition_display" placeholder="VD: 1,000,000" inputmode="numeric"
                            autocomplete="off"
                            value="{{ old('total_condition', $coupon->total_condition) ? money((int) old('total_condition', $coupon->total_condition)) : '' }}">
                        <input type="hidden" name="total_condition" id="total_condition"
                            value="{{ old('total_condition', $coupon->total_condition) }}">
                        @error('total_condition')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Số lượt sử dụng</label>
                        <input type="number" class="form-control {{ $errors->has('count') ? 'is-invalid' : '' }}"
                            name="count" placeholder="VD: 100" value="{{ old('count', $coupon->count) }}">
                        @error('count')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <div class="border rounded-3 px-3 py-3">
                            <div class="form-check form-switch d-flex align-items-start gap-3 mb-0 ps-0">
                                <input class="form-check-input flex-shrink-0 ms-0 mt-1" type="checkbox" role="switch"
                                    id="per_student_once" name="per_student_once" value="1"
                                    {{ old('per_student_once', $coupon->per_student_once) ? 'checked' : '' }}>
                                <label class="form-check-label ms-0" for="per_student_once">
                                <span class="d-block fw-semibold">Mỗi học viên chỉ dùng 1 lần</span>
                                <span class="text-muted small">Học viên đã dùng coupon này rồi sẽ không còn thấy mã này ở màn client nữa.</span>
                                </label>
                            </div>
                        </div>
                        @error('per_student_once')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Ngày bắt đầu</label>
                        <input type="date" class="form-control {{ $errors->has('start_date') ? 'is-invalid' : '' }}"
                            name="start_date" value="{{ old('start_date', $coupon->start_date) }}">
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Ngày kết thúc</label>
                        <input type="date" class="form-control {{ $errors->has('end_date') ? 'is-invalid' : '' }}"
                            name="end_date" value="{{ old('end_date', $coupon->end_date) }}">
                        @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-form__footer">
            <a href="{{ route('coupons.index') }}" class="btn btn-light border px-4">Hủy</a>
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-save me-1"></i>
                Lưu thay đổi
            </button>
        </div>
    </form>
@endsection

@section('scripts')
    <script>
        document.getElementById('randomCodeBtn').addEventListener('click', function() {
            const random = Math.random().toString(36).substring(2, 8).toUpperCase();
            document.getElementById('couponCode').value = `${random}`;
        });

        const display = document.getElementById('total_condition_display');
        const hidden = document.getElementById('total_condition');

        function onlyDigits(str) {
            return (str || '').toString().replace(/[^\d]/g, '');
        }

        function formatNumber(strDigits) {
            if (!strDigits) return '';
            return strDigits.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        function sync() {
            const digits = onlyDigits(display.value);
            hidden.value = digits ? parseInt(digits, 10) : 0;
            display.value = formatNumber(digits);
        }

        sync();
        display.addEventListener('input', sync);
    </script>
@endsection
