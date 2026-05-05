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

                            <div class="col-md-6 mb-3">
                                <a class="overview-card-link"
                                    href="{{ route('students.account.certificates.index', ['locale' => app()->getLocale()]) }}">
                                    <div class="overview-card overview-card--certificates">
                                        <i class="fa-solid fa-award"></i>
                                        <h5>{{ __('students::clients/account.account.certificates') }}</h5>
                                        <div class="overview-card__meta">
                                            <span class="overview-card__value">{{ $totalCertificates }}</span>
                                            <span class="overview-card__unit">
                                                {{ __('students::clients/account.account.certificates_unit') }}
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="overview-card overview-card--progress">
                                    <i class="fa-solid fa-chart-line"></i>
                                    <h5>{{ __('students::clients/account.account.avg_progress') }}</h5>
                                    <div class="overview-card__meta">
                                        <span class="overview-card__value">{{ $averageProgress }}%</span>
                                    </div>
                                    <div class="progress mt-2" style="height: 6px; background-color: rgba(255,255,255,0.2);">
                                        <div class="progress-bar bg-white" role="progressbar" style="width: {{ $averageProgress }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($upcomingQuizzes->count())
                            <div class="account-dashboard-block mt-4">
                                <div class="account-dashboard-block__head">
                                    <h4>{{ __('students::clients/account.account.upcoming_quizzes') }}</h4>
                                    <a href="{{ route('students.account.my-quizzes', ['locale' => app()->getLocale()]) }}">
                                        {{ __('students::clients/account.account.view_all_quizzes') }}
                                    </a>
                                </div>
                                <div class="list-group shadow-sm rounded-3 border-0 overflow-hidden">
                                    @foreach ($upcomingQuizzes as $quiz)
                                        <a href="{{ route('teacher.dashboard.quizzes.show', ['locale' => app()->getLocale(), 'course' => $quiz->course_id, 'quiz' => $quiz->id]) }}"
                                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 border-start-0 border-end-0">
                                            <div>
                                                <h6 class="mb-1 fw-bold text-primary">{{ $quiz->title }}</h6>
                                                <small class="text-muted">
                                                    <i class="fa-solid fa-book-open me-1"></i>
                                                    {{ $quiz->course->name_locale }}
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <div class="badge bg-danger rounded-pill mb-1 px-3">
                                                    {{ $quiz->deadline_at->format('d/m/Y H:i') }}
                                                </div>
                                                <div class="small text-muted" style="font-size: 0.75rem;">
                                                    <i class="fa-regular fa-clock me-1"></i>
                                                    {{ $quiz->deadline_at->diffForHumans() }}
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

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
                                                        {{ $order->status->name_locale ?? __('students::clients/account.core.status') }}
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
    <style>
        .overview-card--certificates {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
        }

        .overview-card--progress {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            color: #fff !important;
        }

        .overview-card--progress h5,
        .overview-card--progress .overview-card__value,
        .overview-card--progress .overview-card__unit {
            color: #fff !important;
        }

        .overview-card--progress i {
            color: rgba(255, 255, 255, 0.5) !important;
        }

        .list-group-item-action:hover {
            background-color: #f8fafc;
        }
        
        .border-start-0 { border-left: 0 !important; }
        .border-end-0 { border-right: 0 !important; }

        /* Dark Mode Fixes */
        html[data-theme="dark"] .account-content h2 {
            color: #f8fafc;
        }

        html[data-theme="dark"] .account-dashboard-block h4 {
            color: #f8fafc;
        }

        html[data-theme="dark"] .list-group-item {
            background-color: #1e293b;
            border-color: #334155 !important;
            color: #e2e8f0;
        }

        html[data-theme="dark"] .list-group-item-action:hover {
            background-color: #334155;
            color: #f8fafc;
        }

        html[data-theme="dark"] .text-primary {
            color: #60a5fa !important;
        }

        html[data-theme="dark"] .text-muted {
            color: #94a3b8 !important;
        }

        html[data-theme="dark"] .account-mini-card, 
        html[data-theme="dark"] .account-order-card {
            background-color: #1e293b;
            border: 1px solid #334155;
        }
        
        html[data-theme="dark"] .account-mini-card h5,
        html[data-theme="dark"] .account-order-card__code {
            color: #f8fafc;
        }

        html[data-theme="dark"] .account-order-card__meta strong {
            color: #f8fafc;
        }
    </style>
@endsection

