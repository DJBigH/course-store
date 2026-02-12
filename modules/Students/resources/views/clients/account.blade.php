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
                        <h2 class="mb-3">{{ __('students::clients/account.account.title') }}</h2>
                        <p class="text-muted mb-4">
                            {{ __('students::clients/account.account.welcome') }}
                        </p>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <a href="{{ route('students.account.my-courses', ['locale' => app()->getLocale()]) }}">
                                    <div class="overview-card">
                                        <i class="fa-solid fa-book"></i>
                                        <h5>{{ __('students::clients/account.account.courses') }}</h5>
                                        <span>{{ $totalCourses }} {{ __('students::clients/account.account.courses_unit') }}</span>
                                    </div>
                                </a>
                            </div>

                            <div class="col-md-4 mb-3">
                                <a href="{{ route('students.account.my-coupon', ['locale' => app()->getLocale()]) }}">
                                    <div class="overview-card">
                                        <i class="fa-solid fa-ticket"></i>
                                        <h5>{{ __('students::clients/account.account.coupons') }}</h5>
                                        <span>{{ $totalCoupons }} {{ __('students::clients/account.account.coupons_unit') }}</span>
                                    </div>
                                </a>
                            </div>

                            <div class="col-md-4 mb-3">
                                <a href="{{ route('students.account.my-order', ['locale' => app()->getLocale()]) }}">
                                    <div class="overview-card">
                                        <i class="fa-solid fa-cart-shopping"></i>
                                        <h5>{{ __('students::clients/account.account.orders') }}</h5>
                                        <span>{{ $totalOrders }} {{ __('students::clients/account.account.orders_unit') }}</span>
                                    </div>
                                </a>
                            </div>
                        </div>

                        {{-- 📘 Khóa học gần đây
                        @if ($recentCourses->count())
                            <div class="mt-4">
                                <h4 class="mb-3">📘 Khóa học gần đây</h4>

                                <div class="row">
                                    @foreach ($recentCourses as $course)
                                        <div class="col-md-4 mb-3">
                                            <div class="card h-100">
                                                <img src="{{ asset($course->thumbnail) }}" class="card-img-top">
                                                <div class="card-body d-flex flex-column">
                                                    <h6 class="mb-3">{{ $course->name }}</h6>
                                                    <a href="{{ route('courses.detail', $course->slug) }}"
                                                        class="btn btn-sm btn-primary mt-auto">
                                                        Tiếp tục học
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif --}}

                    </div>
                </div>


            </div>
        </div>
    </section>
@endsection
