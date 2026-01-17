@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    <section class="account-page py-4">
        <div class="container">
            <div class="row">
                {{-- Sidebar --}}
                <div class="col-lg-3 mb-4">
                    <div class="account-sidebar">
                        @include('students::clients.menu')
                    </div>
                </div>

                {{-- Content --}}
                <div class="col-lg-9">
                    <div class="account-content card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h2 class="mb-2 fw-semibold">Đổi mật khẩu</h2>
                            @if (session('msg'))
                                <div class="alert alert-{{ session('msgType') }}">{{ session('msg') }}</div>
                            @endif
                            @if ($errors->any())
                                <div class="alert alert-danger">Vui lòng kiểm tra lại dữ liệu</div>
                            @endif
                            <form action="" method="post" class="js-change-password">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-medium">
                                        Mật khẩu cũ
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-lock"></i>
                                        </span>
                                        <input type="password" name="old_password" class="form-control"
                                            placeholder="Nhập mật khẩu cũ">
                                        </div>
                                        @error('old_password')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-medium">
                                        Mật khẩu mới
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-key"></i>
                                        </span>
                                        <input type="password" name="password" class="form-control"
                                            placeholder="Nhập mật khẩu mới">
                                        </div>
                                        @error('password')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-medium">
                                        Nhập lại mật khẩu mới
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-key-fill"></i>
                                        </span>
                                        <input type="password" name="confirm_password" class="form-control"
                                            placeholder="Nhập lại mật khẩu mới">
                                        </div>
                                        @error('confirm_password')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Đổi mật khẩu
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection
