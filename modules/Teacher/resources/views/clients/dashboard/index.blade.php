@extends('layouts.teacher')

@section('content')
    @php
        $commission = rtrim(rtrim(number_format((float) ($effectiveCommissionRate ?? $teacher->commission_rate), 2, '.', ''), '0'), '.');
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
                <a href="{{ $teacher->packageHasFeature('can_send_promotions') ? route('teacher.dashboard.promotions') : route('teacher.dashboard.package.upgrade') }}" class="teacher-overview-shortcut mt-4">
                    <span class="teacher-overview-shortcut__icon"><i class="fas fa-bullhorn"></i></span>
                    <div>
                        <strong>{{ __('teacher::dashboard.promotions.shortcut_title') }}</strong>
                        <p class="mb-0">
                            {{ $teacher->packageHasFeature('can_send_promotions')
                                ? __('teacher::dashboard.promotions.shortcut_description')
                                : __('teacher::dashboard.promotions.shortcut_locked') }}
                        </p>
                    </div>
                </a>
            </div>

            <div class="teacher-hero__rail">
                @if ($packageSummary)
                    <div class="teacher-hero__mini teacher-hero__mini--package">
                        <span>{{ __('teacher::dashboard.overview.package.current_label') }}</span>
                        <strong>{{ $packageSummary['name'] }}</strong>
                        <small>
                            {{ __('teacher::dashboard.overview.package.summary', [
                                'commission' => rtrim(rtrim(number_format($packageSummary['commission_rate'], 2, '.', ''), '0'), '.'),
                                'limit' => $packageSummary['course_limit'] ?: __('teacher::dashboard.courses.unlimited'),
                            ]) }}
                        </small>
                        @if (!empty($packageSummary['expires_at']))
                            <small>
                                {{ __('teacher::dashboard.overview.package.expires_on', [
                                    'date' => $packageSummary['expires_at']->format('d/m/Y'),
                                    'days' => $packageSummary['days_left'] ?? 0,
                                ]) }}
                            </small>
                        @endif
                        @if (!empty($packageSummary['pending_upgrade']) && !empty($packageSummary['pending_upgrade_starts_at']))
                            <small class="teacher-hero__package-note">
                                @if (!empty($packageSummary['pending_upgrade_is_queued']))
                                    {{ __('teacher::dashboard.overview.package.pending_starts_on', [
                                        'name' => $packageSummary['pending_upgrade_name'],
                                        'date' => $packageSummary['pending_upgrade_starts_at']->format('d/m/Y'),
                                    ]) }}
                                    @if (!is_null($packageSummary['pending_upgrade_days_until_activation']))
                                        <span class="d-block">
                                            {{ __('teacher::dashboard.overview.package.pending_days_left', [
                                                'days' => $packageSummary['pending_upgrade_days_until_activation'],
                                            ]) }}
                                        </span>
                                    @endif
                                @else
                                    {{ __('teacher::dashboard.overview.package.pending_status', [
                                        'status' => $packageSummary['pending_upgrade_status'],
                                    ]) }}
                                @endif
                            </small>
                        @endif
                        @if ($packageSummary['can_upgrade'])
                            <a href="{{ $packageSummary['upgrade_url'] }}" class="btn btn-sm btn-light mt-2 align-self-start">
                                @if ($packageSummary['has_higher_package'])
                                    {{ __('teacher::dashboard.overview.package.upgrade_cta', ['name' => $packageSummary['upgrade_name']]) }}
                                @else
                                    {{ __('teacher::dashboard.overview.package.change_cta') }}
                                @endif
                            </a>
                        @elseif ($packageSummary['pending_upgrade'])
                            <a href="{{ $packageSummary['pending_upgrade_url'] }}" class="btn btn-sm btn-light mt-2 align-self-start">
                                {{ __('teacher::dashboard.package.status_title') }}
                            </a>
                        @else
                            <small class="teacher-hero__package-note">{{ __('teacher::dashboard.overview.package.maxed') }}</small>
                        @endif
                    </div>
                @endif
                <div class="teacher-hero__mini">
                    <span>{{ __('teacher::dashboard.overview.labels.available_balance') }}</span>
                    <strong>{{ money($stats['available_balance'], 'đ', '0 đ') }}</strong>
                </div>
                <div class="teacher-hero__mini teacher-hero__mini--glass">
                    <span>{{ __('teacher::dashboard.overview.labels.estimated_revenue') }}</span>
                    <strong>{{ money($stats['estimated_revenue'], 'đ', '0 đ') }}</strong>
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

            <div class="teacher-overview-shortcuts">
                <a href="{{ route('teacher.dashboard.courses') }}" class="teacher-overview-shortcut">
                    <span class="teacher-overview-shortcut__icon"><i class="fas fa-book-open"></i></span>
                    <div>
                        <strong>Khóa học của tôi</strong>
                        <p class="mb-0">Tạo khóa học mới, sửa thông tin và soạn bài học.</p>
                    </div>
                </a>

                <a href="{{ route('teacher.dashboard.earnings') }}" class="teacher-overview-shortcut">
                    <span class="teacher-overview-shortcut__icon"><i class="fas fa-chart-line"></i></span>
                    <div>
                        <strong>Doanh thu</strong>
                        <p class="mb-0">Xem tiền bán khóa học và các giao dịch gần đây.</p>
                    </div>
                </a>

                <a href="{{ route('teacher.dashboard.payouts') }}" class="teacher-overview-shortcut">
                    <span class="teacher-overview-shortcut__icon"><i class="fas fa-wallet"></i></span>
                    <div>
                        <strong>Rút tiền</strong>
                        <p class="mb-0">Gửi yêu cầu rút tiền và theo dõi lịch sử xử lý.</p>
                    </div>
                </a>

                <a href="{{ route('teacher.dashboard.profile') }}" class="teacher-overview-shortcut">
                    <span class="teacher-overview-shortcut__icon"><i class="fas fa-id-card"></i></span>
                    <div>
                        <strong>Hồ sơ giảng viên</strong>
                        <p class="mb-0">Cập nhật thông tin tài khoản, mật khẩu và xác thực 2 lớp.</p>
                    </div>
                </a>
            </div>

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
                        <div class="teacher-stat-card__value">{{ money($stats['available_balance'], 'đ', '0 đ') }}</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="teacher-stat-card">
                        <div class="teacher-stat-card__label">{{ __('teacher::dashboard.overview.labels.gross_revenue') }}</div>
                        <div class="teacher-stat-card__value">{{ money($stats['gross_revenue'], 'đ', '0 đ') }}</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="teacher-stat-card">
                        <div class="teacher-stat-card__label">{{ __('teacher::dashboard.overview.labels.allocated_discount') }}</div>
                        <div class="teacher-stat-card__value">{{ money($stats['allocated_discount'], 'đ', '0 đ') }}</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="teacher-stat-card">
                        <div class="teacher-stat-card__label">{{ __('teacher::dashboard.overview.labels.estimated_revenue') }}</div>
                        <div class="teacher-stat-card__value">{{ money($stats['estimated_revenue'], 'đ', '0 đ') }}</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="teacher-stat-card">
                        <div class="teacher-stat-card__label">{{ __('teacher::dashboard.overview.labels.platform_revenue') }}</div>
                        <div class="teacher-stat-card__value">{{ money($stats['platform_revenue'], 'đ', '0 đ') }}</div>
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
                                <span class="text-primary fw-semibold">{{ money($detail->finance_breakdown['teacher_revenue'], 'đ', '0 đ') }}</span>
                            </div>
                        @empty
                            <p class="text-muted mb-0">{{ __('teacher::dashboard.overview.no_sales') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="teacher-panel h-100">
                    <div class="teacher-section-title">
                        <div>
                            <h4 class="h5">{{ __('teacher::dashboard.overview.top_bundles_title') }}</h4>
                            <p class="text-muted mb-0">{{ __('teacher::dashboard.overview.top_bundles_desc') }}</p>
                        </div>
                        <a class="teacher-soft-link" href="{{ route('teacher.dashboard.bundles') }}">
                            {{ __('teacher::dashboard.common.view_all') }}
                        </a>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        @forelse ($topBundles as $bundleStat)
                            <div class="teacher-subtle-card">
                                <strong class="d-block">
                                    {{ $bundleStat->bundle?->name ?: __('teacher::dashboard.common.unknown_course') }}
                                </strong>
                                <small class="d-block text-muted">
                                    {{ __('teacher::dashboard.overview.top_bundles_sales', ['count' => $bundleStat->sales_count]) }}
                                </small>
                                <span class="text-primary fw-semibold">{{ money($bundleStat->net_revenue, 'đ', '0 đ') }}</span>
                            </div>
                        @empty
                            <p class="text-muted mb-0">{{ __('teacher::dashboard.overview.top_bundles_empty') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('stylesheets')
    <style>
        .teacher-overview-shortcuts {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .teacher-overview-shortcut {
            display: flex;
            gap: 1rem;
            padding: 1.1rem 1.15rem;
            border: 1px solid rgba(96, 165, 250, 0.16);
            border-radius: 22px;
            background: rgba(18, 28, 50, 0.72);
            color: #e8f1ff;
            text-decoration: none;
            transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .teacher-overview-shortcut:hover {
            transform: translateY(-4px);
            color: #fff;
            border-color: rgba(125, 211, 252, 0.34);
            box-shadow: 0 18px 42px rgba(2, 6, 23, 0.18);
        }

        .teacher-overview-shortcut__icon {
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: rgba(37, 99, 235, 0.18);
            color: #8fc3ff;
        }

        .teacher-overview-shortcut strong {
            display: block;
            margin-bottom: 0.25rem;
            color: #fff;
        }

        .teacher-overview-shortcut p {
            color: rgba(226, 232, 240, 0.78);
            line-height: 1.6;
        }

        .teacher-hero__mini--package small {
            display: block;
            color: rgba(226, 232, 240, 0.78);
            margin-top: 0.35rem;
            line-height: 1.45;
        }

        .teacher-hero__package-note {
            color: rgba(125, 211, 252, 0.88);
            font-weight: 600;
        }

        html[data-theme="light"] .teacher-hero {
            background:
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.14), transparent 24%),
                radial-gradient(circle at left center, rgba(59, 130, 246, 0.1), transparent 28%),
                linear-gradient(135deg, #f8fafc 0%, #e2e8f0 60%, #dbeafe 100%);
            color: #0f172a;
        }

        html[data-theme="light"] .teacher-hero__eyebrow {
            background: rgba(37, 99, 235, 0.12);
            color: #1d4ed8;
        }

        html[data-theme="light"] .teacher-hero__title {
            color: #0f172a;
        }

        html[data-theme="light"] .teacher-hero__desc {
            color: #475569;
        }

        html[data-theme="light"] .teacher-chip--dark {
            background: var(--admin-surface);
            border-color: var(--admin-border);
            color: var(--admin-text);
        }

        html[data-theme="light"] .teacher-hero__mini {
            background: var(--admin-surface);
            border: 1px solid var(--admin-border);
            box-shadow: var(--admin-card-shadow);
            color: var(--admin-text);
        }

        html[data-theme="light"] .teacher-hero__mini span {
            color: var(--admin-muted);
        }

        html[data-theme="light"] .teacher-hero__mini strong {
            color: var(--admin-text);
        }

        html[data-theme="light"] .teacher-hero__mini small {
            color: var(--admin-muted);
        }

        html[data-theme="light"] .teacher-hero__mini--glass {
            background: var(--admin-subtle-bg);
            border-color: var(--admin-border);
        }

        html[data-theme="light"] .teacher-hero__package-note {
            color: #2563eb;
        }

        html[data-theme="light"] .teacher-overview-shortcut {
            background: var(--admin-surface);
            border-color: var(--admin-border);
            color: var(--admin-text);
            box-shadow: var(--admin-card-shadow);
        }

        html[data-theme="light"] .teacher-overview-shortcut:hover {
            color: var(--admin-text);
        }

        html[data-theme="light"] .teacher-overview-shortcut__icon {
            background: rgba(37, 99, 235, 0.12);
            color: #1d4ed8;
        }

        html[data-theme="light"] .teacher-overview-shortcut strong {
            color: var(--admin-text);
        }

        html[data-theme="light"] .teacher-overview-shortcut p {
            color: var(--admin-muted);
        }

        @media (max-width: 991.98px) {
            .teacher-overview-shortcuts {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection
