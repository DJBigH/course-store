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

    <form action="" method="post" class="admin-form">
        @csrf
        <div class="admin-form__header">
            <div>
                <h5 class="mb-1">Cập nhật người dùng</h5>
                <p class="text-muted mb-0">Chỉnh sửa tài khoản nội bộ, nhóm quyền và quyền truy cập admin panel.</p>
            </div>
        </div>

        <div class="row p-4 g-3">
            <div class="col-md-6">
                <label class="form-label">Tên</label>
                <input type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name"
                    placeholder="Tên..." value="{{ old('name') ?? $users->name }}">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="text" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email"
                    placeholder="Email..." value="{{ old('email') ?? $users->email }}">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Nhóm quyền</label>
                <select name="group_id" class="form-select{{ $errors->has('group_id') ? ' is-invalid' : '' }}">
                    <option value="">Chọn nhóm quyền</option>
                    @foreach ($groups as $group)
                        <option value="{{ $group->id }}" @selected((int) old('group_id', $users->group_id) === (int) $group->id)>
                            {{ $group->name }}
                        </option>
                    @endforeach
                </select>
                @error('group_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Mật khẩu</label>
                <input type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                    name="password" placeholder="Để trống nếu không đổi">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Trạng thái tài khoản</label>
                <select name="is_locked" class="form-select{{ $errors->has('is_locked') ? ' is-invalid' : '' }}">
                    <option value="0" @selected((string) old('is_locked', $users->is_locked ?? 0) === '0')>Hoạt động bình thường</option>
                    <option value="1" @selected((string) old('is_locked', $users->is_locked ?? 0) === '1')>Khóa, không cho vào admin panel</option>
                </select>
                @if ((int) auth()->id() === (int) $users->id)
                    <small class="text-muted d-block mt-2">Không nên tự khóa chính tài khoản đang đăng nhập.</small>
                @endif
                @error('is_locked')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="admin-form__footer">
            <button type="submit" class="btn btn-success">Lưu</button>
            <a href="{{ route('user.index') }}" class="btn btn-light border">Trở về</a>
        </div>
    </form>
@endsection
