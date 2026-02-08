@extends('layouts.backend')

@section('content')
    <div class="container-fluid">
        @if ($errors->any())
            <div class="alert alert-danger">
                Vui lòng kiểm tra lại dữ liệu đã nhập.
            </div>
        @endif

        @if (session('msg'))
            <div class="alert alert-success">
                {{ session('msg') }}
            </div>
        @endif

        <div class="row">
            <div class="col-md-4">
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile text-center">
                        <img class="profile-user-img img-fluid img-circle"
                            src="{{ $user->avatar ?? asset('backend/assets/img/admin.jfif') }}" alt="User profile picture"
                            width="100px">

                        <h3 class="profile-username mt-3">
                            {{ $user->name }}
                        </h3>

                        <p class="text-muted">
                            {{ $user->role->name ?? 'Quản trị viên' }}
                        </p>

                        <ul class="list-group list-group-unbordered mb-3 text-start">
                            <li class="list-group-item">
                                <b>Email</b>
                                <span class="float-end">{{ $user->email }}</span>
                            </li>
                            <li class="list-group-item">
                                <b>Ngày tham gia</b>
                                <span class="float-end">
                                    {{ $user->created_at->format('d/m/Y') }}
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Cập nhật thông tin cá nhân</h3>
                    </div>

                    <form action="{{ route('user.post-show') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="">Tên</label>
                                    <input type="text"
                                        class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name"
                                        placeholder="Tên..." value="{{ old('name') ?? $user->name }}">
                                    @error('name')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="">Email</label>
                                    <input type="text"
                                        class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email"
                                        placeholder="Email..." readonly value="{{ old('email') ?? $user->email }}">
                                    @error('email')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="">Mất khẩu</label>
                                    <input type="password"
                                        class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                                        name="password" placeholder="Mất khẩu...">
                                    @error('password')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                            </div>
                        </div>

                        <div class="card-footer text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
