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
                        <h2 class="mb-3">Đổi mật khẩu</h2>
                        <p class="text-muted mb-4">
                            Chào mừng bạn quay trở lại! Dưới đây là thông tin tổng quan về tài khoản học viên của bạn.
                        </p>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="overview-card">
                                    <i class="fa-solid fa-book"></i>
                                    <h5>Khóa học</h5>
                                    <span>5 khóa</span>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="overview-card">
                                    <i class="fa-solid fa-clock"></i>
                                    <h5>Thời gian học</h5>
                                    <span>12 giờ</span>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="overview-card">
                                    <i class="fa-solid fa-cart-shopping"></i>
                                    <h5>Đơn hàng</h5>
                                    <span>3 đơn</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
