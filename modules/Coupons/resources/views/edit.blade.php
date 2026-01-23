@extends('layouts.backend')

@section('content')
    @if (session('msg_danger'))
        <div class="alert alert-danger">{{ session('msg_danger') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            Vui lòng kiểm tra lại dữ liệu đã nhập.
        </div>
    @endif

    <form action="" method="POST">
        @csrf

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white fw-semibold">
                <i class="fas fa-ticket-alt me-1"></i>
                Cập nhập mã khuyến mãi
            </div>

            <div class="card-body">
                <div class="row g-3">

                    {{-- Mã khuyến mãi --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Mã khuyến mãi</label>
                        <div class="input-group">
                            <input type="text" class="form-control {{ $errors->has('code') ? 'is-invalid' : '' }}"
                                name="code" id="couponCode" placeholder="VD: SALE50" value="{{ old('code',$coupon->code) }}">
                            <button type="button" class="btn btn-outline-secondary" id="randomCodeBtn"
                                title="Tạo mã ngẫu nhiên">
                                <i class="fas fa-random"></i>
                            </button>
                        </div>
                        @error('code')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Loại giảm --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Loại giảm giá</label>
                        <select name="discount_type"
                            class="form-select {{ $errors->has('discount_type') ? 'is-invalid' : '' }}">
                            <option value="percent" {{ old('discount_type', $coupon->discount_type) == 'percent' ? 'selected' : '' }}>
                                Phần trăm (%)
                            </option>
                            <option value="value" {{ old('discount_type', $coupon->discount_type) == 'value' ? 'selected' : '' }}>
                                Giá trị (VNĐ)
                            </option>
                        </select>
                        @error('discount_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Giá trị giảm --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Giá trị giảm</label>
                        <input type="number" class="form-control {{ $errors->has('discount_value') ? 'is-invalid' : '' }}"
                            name="discount_value" placeholder="VD: 10 (%) hoặc 50000 (đ)"
                            value="{{ old('discount_value', $coupon->discount_value) }}">
                        @error('discount_value')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tổng tiền tối thiểu --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Giá trị đơn hàng tối thiểu</label>
                        <input type="number"
                            class="form-control {{ $errors->has('total_condition') ? 'is-invalid' : '' }}"
                            name="total_condition" placeholder="VD: 200000" value="{{ old('total_condition',$coupon->total_condition) }}">
                        @error('total_condition')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Số lượng --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Số lượt sử dụng</label>
                        <input type="number" class="form-control {{ $errors->has('count') ? 'is-invalid' : '' }}"
                            name="count" placeholder="VD: 100" value="{{ old('count',$coupon->count) }}">
                        @error('count')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Ngày bắt đầu --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Ngày bắt đầu</label>
                        <input type="text"
                            class="form-control datepicker {{ $errors->has('start_date') ? 'is-invalid' : '' }}"
                            name="start_date" placeholder="dd/mm/yyyy" value="{{ old('start_date',$coupon->start_date) }}">
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Ngày kết thúc --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Ngày kết thúc</label>
                        <input type="text"
                            class="form-control datepicker {{ $errors->has('end_date') ? 'is-invalid' : '' }}"
                            name="end_date" placeholder="dd/mm/yyyy" value="{{ old('end_date',$coupon->end_date) }}">
                        @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>

            <div class="card-footer text-end">
                <a href="{{ route('coupons.index') }}" class="btn btn-secondary px-4">
                    Quay lại
                </a>
                <button type="submit" class="btn btn-success px-4">
                    <i class="fas fa-save me-1"></i> Lưu
                </button>
            </div>
        </div>
    </form>
@endsection
@section('scripts')
<script>
    document.getElementById('randomCodeBtn').addEventListener('click', function () {
        const random = Math.random().toString(36).substring(2, 8).toUpperCase();
        document.getElementById('couponCode').value = `${random}`;
    });
</script>
@endsection
