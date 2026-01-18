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
                <div class="col-lg-9 account-profile">
                    <div class="account-content">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="fw-semibold mb-0">Thông tin cá nhân</h2>
                            <button class="btn btn-warning js-profile-btn">
                                Chỉnh sửa thông tin
                            </button>
                        </div>

                        <table class="js-profile profile-item table table-bordered table-profile active">
                            <tbody>
                                <tr>
                                    <th>Họ và tên</th>
                                    <td>{{ $student->name }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ $student->email }}</td>
                                </tr>
                                <tr>
                                    <th>Số điện thoại</th>
                                    <td>{{ $student->phone }}</td>
                                </tr>
                                <tr>
                                    <th>Địa chỉ</th>
                                    <td>{{ $student->address ?? 'Chưa cập nhật' }}</td>
                                </tr>
                                <tr>
                                    <th>Trạng thái</th>
                                    <td>
                                        <span class="badge bg-success">Đang hoạt động</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Thời gian đăng ký</th>
                                    <td>{{ Carbon\Carbon::parse($student->created_at)->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Thời gian kích hoạt</th>
                                    <td>{{ Carbon\Carbon::parse($student->email_verified_at)->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            </tbody>
                        </table>


                        <form action="{{ route('students.account.client-updateprofile') }}"
                            class="js-profile profile-item profile-form" method="post">
                            <div class="card shadow-sm">
                                <div class="card-header bg-light fw-bold">
                                    Cập nhật thông tin cá nhân
                                    @if (session('msg'))
                                        <div class="alert alert-success">{{ session('msg') }}</div>
                                    @endif
                                </div>

                                <div class="card-body">
                                    <div class="row mb-3">
                                        <label class="col-md-4 col-form-label">Họ và tên</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" placeholder="Nhập họ và tên"
                                                value="{{ $student->name }}" name="name">
                                            <span class="error error-name text-danger"></span>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-md-4 col-form-label">Email</label>
                                        <div class="col-md-8">
                                            <input type="email" class="form-control" placeholder="Nhập email"
                                                value="{{ $student->email }}" name="email">
                                            <span class="error error-email text-danger"></span>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label class="col-md-4 col-form-label">Số điện thoại</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" placeholder="Nhập số điện thoại"
                                                value="{{ $student->phone }}" name="phone">
                                            <span class="error error-phone text-danger"></span>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <label class="col-md-4 col-form-label">Địa chỉ</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" placeholder="Nhập địa chỉ"
                                                value="{{ $student->address }}" name="address">
                                            <span class="error error-address text-danger"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer text-end">
                                    <button class="btn btn-primary px-4">
                                        Lưu thay đổi
                                    </button>
                                </div>
                            </div>
                            <p class="text-muted fst-italic mt-2">
                                *Vui lòng reload hoặc nhấn F5 sau khi thay đổi thông tin
                            </p>
                        </form>
                    </div>

                </div>
            </div>
        </div>
        </div>
    </section>
@endsection
