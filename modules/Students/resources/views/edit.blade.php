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
        <div class="row">
            <div class="col-6">
                <div class="mb-3">
                    <label for="">Tên</label>
                    <input type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name"
                        placeholder="Tên..." value="{{ old('name') ?? $students->name }}">
                    @error('name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-6">
                <div class="mb-3">
                    <label for="">Email</label>
                    <input type="text" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                        name="email" placeholder="Email..." value="{{ old('email') ?? $students->email }}">
                    @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-6">
                <div class="mb-3">
                    <label for="">Trạng thái</label>
                    <select name="status" id=""
                        class="form-select{{ $errors->has('status') ? ' is-invalid' : '' }}">
                        <option value="0" {{ old('status', $students->status) == 0 ? 'selected' : '' }}>Chưa kích hoạt
                        </option>
                        <option value="1" {{ old('status', $students->status) == 1 ? 'selected' : '' }}>Kích hoạt
                        </option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-6">
                <div class="mb-3">
                    <label for="">Mất khẩu</label>
                    <input type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                        name="password" placeholder="Mất khẩu...">
                    @error('password')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-6">
                <div class="mb-3">
                    <label for="">Địa chỉ</label>
                    <input type="text" class="form-control{{ $errors->has('address') ? ' is-invalid' : '' }}"
                        name="address" placeholder="Địa chỉ..." value="{{ old('address') ?? $students->address }}">
                    @error('address')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>


            <div class="col-6">
                <div class="mb-3">
                    <label for="">Điện thoại</label>
                    <input type="text" class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}"
                        name="phone" placeholder="Điện thoại..." value="{{ old('phone') ?? $students->phone }}">
                    @error('phone')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-12 text-end admin-form__footer">
                <button type="submit" class="btn btn-success">Lưu</button>
                <a href="{{ route('students.index') }}" class="btn btn-warning">Trở về</a>
            </div>
        </div>
    </form>
@endsection
