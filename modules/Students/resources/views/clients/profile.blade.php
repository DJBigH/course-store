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
                <div class="account-content">
                    <h2 class="mb-3">Thông tin cá nhân</h2>
                    <button class="btn btn-warning mb-3 js-profile-btn float-end">Chỉnh sửa thông tin</button>
                    <!-- <table class="table table-bordered">
                        <tr>
                            <td>Họ và tên</td>
                            <td>{{ $student->name }}</td>
                        </tr>

                        <tr>
                            <td>Email</td>
                            <td>{{ $student->email }}</td>
                        </tr>

                        <tr>
                            <td>Số điện thoại</td>
                            <td>{{ $student->phone }}</td>
                        </tr>

                        <tr>
                            <td>Địa chỉ</td>
                            <td>{{ $student->address ?? 'Chưa cập nhật' }}</td>
                        </tr>

                        <tr>
                            <td>Trạng thái</td>
                            <td>Đang hoạt động</td>
                        </tr>

                        <tr>
                            <td>Thời gian đăng ký</td>
                            <td>{{ Carbon\Carbon::parse($student->created_at)->format('d/m/Y H:i:s') }}</td>
                        </tr>

                        <tr>
                            <td>Thời gian kích hoạt</td>
                            <td>{{ Carbon\Carbon::parse($student->email_verified_at)->format('d/m/Y H:i:s') }}</td>
                        </tr>
                    </table> -->

                    <form action="" method="post">
                        <tr>
                            <td>Họ và tên</td>
                            <td>{{ $student->name }}</td>
                        </tr>

                        <tr>
                            <td>Email</td>
                            <td>{{ $student->email }}</td>
                        </tr>

                        <tr>
                            <td>Số điện thoại</td>
                            <td>{{ $student->phone }}</td>
                        </tr>

                        <tr>
                            <td>Địa chỉ</td>
                            <td>{{ $student->address ?? 'Chưa cập nhật' }}</td>
                        </tr>

                        <tr>
                            <td>Trạng thái</td>
                            <td>Đang hoạt động</td>
                        </tr>

                        <tr>
                            <td>Thời gian đăng ký</td>
                            <td>{{ Carbon\Carbon::parse($student->created_at)->format('d/m/Y H:i:s') }}</td>
                        </tr>

                        <tr>
                            <td>Thời gian kích hoạt</td>
                            <td>{{ Carbon\Carbon::parse($student->email_verified_at)->format('d/m/Y H:i:s') }}</td>
                        </tr>
                    </form>

                </div>

            </div>
        </div>
    </div>
    </div>
</section>
@endsection