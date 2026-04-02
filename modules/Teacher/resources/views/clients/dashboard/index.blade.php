@extends('layouts.teacher')

@section('content')
    @php
        $commission = rtrim(rtrim(number_format((float) $teacher->commission_rate, 2, '.', ''), '0'), '.');
    @endphp

    <div class="teacher-page-shell">
        <section class="teacher-hero teacher-hero--dashboard">
            <div class="teacher-hero__content">
                <div class="teacher-hero__eyebrow">
                    <i class="fas fa-star"></i>
                    {{ __('teacher::dashboard.overview.eyebrow') }}
                </div>
                <h3 class="teacher-hero__title">
                    {{ __('teacher::dashboard.overview.title', ['name' => $teacher->name]) }}
                </h3>
                <p class="teacher-hero__desc">
                    {{ __('teacher::dashboard.overview.description') }}
                </p>

                <div class="teacher-chip-list mt-4">
                    <span class="teacher-chip teacher-chip--dark">
                        <i class="fas fa-percent"></i>
                        {{ __('teacher::dashboard.common.commission', ['rate' => $commission]) }}
                    </span>
                    <span class="teacher-chip teacher-chip--dark">
                        <i class="fas fa-book"></i>
                        {{ __('teacher::dashboard.common.courses_count', ['count' => $stats['courses']]) }}
                    </span>
                    <span class="teacher-chip teacher-chip--dark">
                        <i class="fas fa-user-graduate"></i>
                        {{ __('teacher::dashboard.common.students_count', ['count' => $stats['students']]) }}
                    </span>
                </div>
            </div>

            <div class="teacher-hero__rail">
                <div class="teacher-hero__mini">
                    <span>{{ __('teacher::dashboard.overview.labels.available_balance') }}</span>
                    <strong>{{ money($stats['available_balance']) }}</strong>
                </div>
                <div class="teacher-hero__mini teacher-hero__mini--glass">
                    <span>{{ __('teacher::dashboard.overview.labels.estimated_revenue') }}</span>
                    <strong>{{ money($stats['estimated_revenue']) }}</strong>
                </div>
            </div>
        </section>

        <div class="teacher-panel">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start mb-4">
                <div>
                    <h3 class="fw-bold mb-2">{{ __('teacher::dashboard.overview.section_title') }}</h3>
                    <p class="text-muted mb-0">{{ __('teacher::dashboard.overview.section_desc') }}</p>
                </div>
                <span class="teacher-status-badge">{{ __('teacher::dashboard.common.active') }}</span>
            </div>

            @include('teacher::clients.dashboard._tabs')

            <div class="row g-3">
                <div class="col-md-6 col-xl-3">
                    <div class="teacher-stat-card">
                        <div class="teacher-stat-card__label">{{ __('teacher::dashboard.overview.labels.total_courses') }}</div>
                        <div class="teacher-stat-card__value">{{ $stats['courses'] }}</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="teacher-stat-card">
                        <div class="teacher-stat-card__label">{{ __('teacher::dashboard.overview.labels.active_courses') }}</div>
                        <div class="teacher-stat-card__value">{{ $stats['active_courses'] }}</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="teacher-stat-card">
                        <div class="teacher-stat-card__label">{{ __('teacher::dashboard.overview.labels.students') }}</div>
                        <div class="teacher-stat-card__value">{{ $stats['students'] }}</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="teacher-stat-card">
                        <div class="teacher-stat-card__label">{{ __('teacher::dashboard.overview.labels.available_balance') }}</div>
                        <div class="teacher-stat-card__value">{{ money($stats['available_balance']) }}</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="teacher-stat-card">
                        <div class="teacher-stat-card__label">{{ __('teacher::dashboard.overview.labels.gross_revenue') }}</div>
                        <div class="teacher-stat-card__value">{{ money($stats['gross_revenue']) }}</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="teacher-stat-card">
                        <div class="teacher-stat-card__label">{{ __('teacher::dashboard.overview.labels.allocated_discount') }}</div>
                        <div class="teacher-stat-card__value">{{ money($stats['allocated_discount']) }}</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="teacher-stat-card">
                        <div class="teacher-stat-card__label">{{ __('teacher::dashboard.overview.labels.estimated_revenue') }}</div>
                        <div class="teacher-stat-card__value">{{ money($stats['estimated_revenue']) }}</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="teacher-stat-card">
                        <div class="teacher-stat-card__label">{{ __('teacher::dashboard.overview.labels.platform_revenue') }}</div>
                        <div class="teacher-stat-card__value">{{ money($stats['platform_revenue']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="teacher-panel h-100">
                    <div class="teacher-section-title">
                        <div>
                            <h4 class="h5">{{ __('teacher::dashboard.overview.recent_courses_title') }}</h4>
                            <p class="text-muted mb-0">{{ __('teacher::dashboard.overview.recent_courses_desc') }}</p>
                        </div>
                        <a class="teacher-soft-link" href="{{ route('teacher.dashboard.courses') }}">
                            {{ __('teacher::dashboard.common.view_all') }}
                        </a>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        @forelse ($recentCourses as $course)
                            <div class="teacher-subtle-card">
                                <strong class="d-block">{{ $course->name_locale }}</strong>
                                <small class="text-muted">
                                    {{ __('teacher::dashboard.overview.labels.course_status', [
                                        'status' => $course->status ? __('teacher::dashboard.common.status_active') : __('teacher::dashboard.common.course_hidden'),
                                    ]) }}
                                </small>
                            </div>
                        @empty
                            <p class="text-muted mb-0">{{ __('teacher::dashboard.overview.no_courses') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="teacher-panel h-100">
                    <div class="teacher-section-title">
                        <div>
                            <h4 class="h5">{{ __('teacher::dashboard.overview.recent_sales_title') }}</h4>
                            <p class="text-muted mb-0">{{ __('teacher::dashboard.overview.recent_sales_desc') }}</p>
                        </div>
                        <a class="teacher-soft-link" href="{{ route('teacher.dashboard.earnings') }}">
                            {{ __('teacher::dashboard.common.view_earnings') }}
                        </a>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        @forelse ($recentSales as $detail)
                            <div class="teacher-subtle-card">
                                <strong class="d-block">
                                    {{ $detail->courses?->name_locale ?: __('teacher::dashboard.common.unknown_course') }}
                                </strong>
                                <small class="d-block text-muted">
                                    {{ __('teacher::dashboard.overview.labels.order_code', ['code' => $detail->order?->code]) }}
                                    - {{ optional($detail->created_at)->format('d/m/Y H:i') }}
                                </small>
                                <span class="text-primary fw-semibold">{{ money($detail->finance_breakdown['teacher_revenue']) }}</span>
                            </div>
                        @empty
                            <p class="text-muted mb-0">{{ __('teacher::dashboard.overview.no_sales') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
