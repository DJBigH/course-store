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
                <p class="text-muted mb-0">Chỉnh sửa thông tin tài khoản quản trị.</p>
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
                <label class="form-label">Nhóm</label>
                <select name="group_id" class="form-select{{ $errors->has('group_id') ? ' is-invalid' : '' }}">
                    <option value="0">Chọn nhóm</option>
                    <option value="1" @selected((int) old('group_id', $users->group_id) === 1)>Quản trị</option>
                    <option value="2" @selected((int) old('group_id', $users->group_id) === 2)>Biên tập</option>
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
        </div>

        <div class="admin-form__footer">
            <button type="submit" class="btn btn-success">Lưu</button>
            <a href="{{ route('user.index') }}" class="btn btn-light border">Trở về</a>
        </div>
    </form>
@endsection
