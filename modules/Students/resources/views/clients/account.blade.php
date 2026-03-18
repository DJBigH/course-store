@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    <section class="account-page py-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 mb-4">
                    <div class="account-sidebar">
                        @include('students::clients.menu')
                    </div>
                </div>

                <div class="col-lg-9">
                    <div class="account-content">
                        <h2 class="mb-3">{{ __('students::clients/account.account.title') }}</h2>
                        <p class="text-muted mb-4">
                            {{ __('students::clients/account.account.welcome') }}
                        </p>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <a class="overview-card-link"
                                    href="{{ route('students.account.my-courses', ['locale' => app()->getLocale()]) }}">
                                    <div class="overview-card">
                                        <i class="fa-solid fa-book"></i>
                                        <h5>{{ __('students::clients/account.account.courses') }}</h5>
                                        <div class="overview-card__meta">
                                            <span class="overview-card__value">{{ $totalCourses }}</span>
                                            <span class="overview-card__unit">
                                                {{ __('students::clients/account.account.courses_unit') }}
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-md-4 mb-3">
                                <a class="overview-card-link"
                                    href="{{ route('students.account.my-coupon', ['locale' => app()->getLocale()]) }}">
                                    <div class="overview-card">
                                        <i class="fa-solid fa-ticket"></i>
                                        <h5>{{ __('students::clients/account.account.coupons') }}</h5>
                                        <div class="overview-card__meta">
                                            <span class="overview-card__value">{{ $totalCoupons }}</span>
                                            <span class="overview-card__unit">
                                                {{ __('students::clients/account.account.coupons_unit') }}
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-md-4 mb-3">
                                <a class="overview-card-link"
                                    href="{{ route('students.account.my-order', ['locale' => app()->getLocale()]) }}">
                                    <div class="overview-card">
                                        <i class="fa-solid fa-cart-shopping"></i>
                                        <h5>{{ __('students::clients/account.account.orders') }}</h5>
                                        <div class="overview-card__meta">
                                            <span class="overview-card__value">{{ $totalOrders }}</span>
                                            <span class="overview-card__unit">
                                                {{ __('students::clients/account.account.orders_unit') }}
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        @if ($recentCourses->count())
                            <div class="account-dashboard-block mt-4">
                                <div class="account-dashboard-block__head">
                                    <h4>{{ __('students::clients/account.account.recent_courses') }}</h4>
                                    <a href="{{ route('students.account.my-courses', ['locale' => app()->getLocale()]) }}">
                                        {{ __('students::clients/account.account.view_all_courses') }}
                                    </a>
                                </div>

                                <div class="row g-3">
                                    @foreach ($recentCourses as $course)
                                        @php
                                            $thumbnail = $course->thumbnail
                                                ? (\Illuminate\Support\Str::startsWith($course->thumbnail, ['http://', 'https://'])
                                                    ? $course->thumbnail
                                                    : asset($course->thumbnail))
                                                : asset('clients/assets/logo.png');
                                        @endphp
                                        <div class="col-md-6 col-xl-4">
                                            <article class="account-mini-card">
                                                <img class="account-mini-card__thumb" src="{{ $thumbnail }}"
                                                    alt="{{ $course->name_locale }}"
                                                    onerror="this.onerror=null;this.src='{{ asset('clients/assets/logo.png') }}';">
                                                <div class="account-mini-card__body">
                                                    <h5>{{ $course->name_locale }}</h5>
                                                    <p>{{ $course->teacher?->name_locale ?? __('students::clients/account.core.instructor') }}</p>
                                                    <a class="account-mini-card__link"
                                                        href="{{ route('courses.detail', ['locale' => app()->getLocale(), 'slug' => $course->slug_locale]) }}">
                                                        {{ __('students::clients/account.account.continue_learning') }}
                                                    </a>
                                                </div>
                                            </article>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($recentOrders->count())
                            <div class="account-dashboard-block mt-4">
                                <div class="account-dashboard-block__head">
                                    <h4>{{ __('students::clients/account.account.recent_orders') }}</h4>
                                    <a href="{{ route('students.account.my-order', ['locale' => app()->getLocale()]) }}">
                                        {{ __('students::clients/account.account.view_all_orders') }}
                                    </a>
                                </div>

                                <div class="row g-3">
                                    @foreach ($recentOrders as $order)
                                        <div class="col-md-6 col-xl-4">
                                            <article class="account-order-card">
                                                <div class="account-order-card__top">
                                                    <span class="account-order-card__code">#{{ $order->code }}</span>
                                                    <span class="account-order-card__status">
                                                        {{ $order->status->name ?? __('students::clients/account.core.status') }}
                                                    </span>
                                                </div>
                                                <div class="account-order-card__meta">
                                                    <strong>{{ money($order->total) }}</strong>
                                                    <span>{{ optional($order->created_at)->format('d/m/Y H:i') }}</span>
                                                </div>
                                                <a class="account-order-card__link"
                                                    href="{{ route('students.account.order-detail', ['locale' => app()->getLocale(), 'id' => $order->id]) }}">
                                                    {{ __('students::clients/account.account.view_order_detail') }}
                                                </a>
                                            </article>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
