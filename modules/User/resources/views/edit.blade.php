@extends('layouts.backend')
@section('content')
@if (session('msg'))
        <div class="alert alert-success">{{ session('msg') }}</div>
    @endif
    @if (session('msg_danger'))
        <div class="alert alert-danger">{{ session('msg_danger') }}</div>
    @endif
    <form action="" method="post">
        @csrf
        <div class="row">
            <div class="col-6">
                <div class="mb-3">
                    <label for="">Tên</label>
                    <input type="text" class="form-control{{ $errors->has('name')?' is-invalid':'' }}" name="name" placeholder="Tên..." value="{{ old('name') ?? $users->name }}">
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
                    <input type="text" class="form-control{{ $errors->has('email')?' is-invalid':'' }}" name="email" placeholder="Email..." value="{{ old('email') ?? $users->email }}">
                     @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-6">
                <div class="mb-3">
                    <label for="">Nhóm</label>
                    <select name="group_id" id="" class="form-select{{ $errors->has('email')?' is-invalid':'' }}">
                        <option value="0">Chọn nhóm</option>
                        <option value="1">Test</option>
                    </select>
                     @error('group_id')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="col-6">
                <div class="mb-3">
                    <label for="">Mất khẩu</label>
                    <input type="password" class="form-control{{ $errors->has('password')?' is-invalid':'' }}" name="password" placeholder="Mất khẩu...">
                     @error('password')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>
            <div class="col-12 text-end">
                <button type="submit" class="btn btn-success">Lưu</button>
                <a href="{{ route('user.index') }}" class="btn btn-warning">Trở về</a>
            </div>
        </div>
    </form>
@endsection
