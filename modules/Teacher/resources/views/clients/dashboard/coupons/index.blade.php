@extends('layouts.teacher')

@section('content')
    <div class="teacher-panel teacher-coupons-shell">
        <div class="teacher-coupons-hero">
            <div>
                <span class="teacher-coupons-kicker">{{ __('coupons::teacher/messages.hero.kicker') }}</span>
                <h3 class="teacher-coupons-title">{{ __('coupons::teacher/messages.hero.title') }}</h3>
                <p class="teacher-coupons-desc mb-0">{{ __('coupons::teacher/messages.hero.description') }}</p>
            </div>
            <div class="teacher-coupons-actions">
                @if ($canCreateCoupons)
                    <a href="{{ route('teacher.dashboard.coupons.create') }}" class="btn btn-primary">
                        {{ __('coupons::teacher/messages.actions.create') }}
                    </a>
                @elseif ($couponUsage['has_limit'] ?? false)
                    <div class="teacher-disabled-action-wrap">
                        <span class="teacher-disabled-action" title="{{ __('coupons::teacher/messages.flash.limit_reached', ['limit' => $couponLimit]) }}">
                            <button type="button" class="btn btn-primary" disabled>
                                {{ __('coupons::teacher/messages.actions.create') }}
                            </button>
                        </span>
                        <div class="teacher-disabled-action__note">
                            {{ __('coupons::teacher/messages.flash.limit_reached', ['limit' => $couponLimit]) }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @if (!$canManageCoupons || ($couponUsage['has_limit'] ?? false))
            @include('teacher::clients.dashboard.partials.package_usage_banner', [
                'variant' => 'info',
                'title' => __('coupons::teacher/messages.limit.title'),
                'lines' => [
                    __('coupons::teacher/messages.limit.description', [
                        'count' => $couponCount,
                        'limit' => $couponLimit ?? __('coupons::teacher/messages.labels.unlimited'),
                    ]),
                    __('coupons::teacher/messages.limit.active_description', [
                        'count' => $activeCouponCount,
                        'locked' => $lockedCouponCount,
                        'limit' => $couponLimit ?? __('coupons::teacher/messages.labels.unlimited'),
                    ]),
                    !$canManageCoupons
                        ? __('coupons::teacher/messages.limit.feature_locked_notice')
                        : ($lockedCouponCount > 0 ? __('coupons::teacher/messages.limit.locked_notice', ['count' => $lockedCouponCount]) : null),
                ],
                'showUpgrade' => !$canManageCoupons || $lockedCouponCount > 0,
                'upgradeUrl' => route('teacher.dashboard.package.upgrade'),
            ])
        @endif

        <div class="row g-3">
            @forelse ($coupons as $coupon)
                @php
                    $isLockedCoupon = (bool) $coupon->package_locked_at;
                @endphp
                <div class="col-xl-6">
                    <article class="teacher-coupon-card">
                        <div class="teacher-coupon-card__head">
                            <div>
                                <h4 class="teacher-coupon-card__code mb-0">{{ $coupon->code }}</h4>
                                <p class="teacher-coupon-card__meta mb-0">
                                    {{ $coupon->discount_type === 'percent' ? $coupon->discount_value . '%' : money($coupon->discount_value) }}
                                    {{ __('coupons::teacher/messages.labels.discount') }}
                                </p>
                                @if ($isLockedCoupon)
                                    <div class="teacher-coupon-card__limit-badge">
                                        {{ __('coupons::teacher/messages.labels.limited_actions_only') }}
                                    </div>
                                @endif
                                @if (($couponUsage['has_limit'] ?? false) && $coupon->is_package_priority)
                                    <div class="teacher-coupon-card__priority">
                                        {{ __('coupons::teacher/messages.labels.priority_active') }}
                                    </div>
                                @endif
                            </div>
                            <span class="teacher-coupon-card__badge">
                                @if ($coupon->package_locked_at)
                                    {{ __('coupons::teacher/messages.labels.locked') }}
                                @else
                                    {{ $coupon->per_student_once ? __('coupons::teacher/messages.labels.once') : __('coupons::teacher/messages.labels.multi') }}
                                @endif
                            </span>
                        </div>

                        @if ($coupon->package_locked_at)
                            <div class="teacher-coupon-card__lock">
                                {{ __('coupons::teacher/messages.labels.lock_reason_' . ($coupon->package_lock_reason ?: 'package_limit_locked')) }}
                            </div>
                        @endif

                        <div class="teacher-coupon-card__grid">
                            <div class="teacher-coupon-card__info">
                                <span>{{ __('coupons::teacher/messages.labels.total_condition') }}</span>
                                <strong>{{ $coupon->total_condition ? money($coupon->total_condition) : __('coupons::teacher/messages.labels.none') }}</strong>
                            </div>
                            <div class="teacher-coupon-card__info">
                                <span>{{ __('coupons::teacher/messages.labels.usage_limit') }}</span>
                                <strong>
                                    @if ($coupon->count)
                                        {{ max($coupon->count - ($coupon->usagescoupon_count ?? 0), 0) }} / {{ $coupon->count }}
                                    @else
                                        {{ __('coupons::teacher/messages.labels.unlimited') }}
                                    @endif
                                </strong>
                            </div>
                            <div class="teacher-coupon-card__info">
                                <span>{{ __('coupons::teacher/messages.labels.time_range') }}</span>
                                <strong>
                                    @if ($coupon->start_date && $coupon->end_date)
                                        {{ \Carbon\Carbon::parse($coupon->start_date)->format('d/m/Y') }}
                                        ->
                                        {{ \Carbon\Carbon::parse($coupon->end_date)->format('d/m/Y') }}
                                    @else
                                        {{ __('coupons::teacher/messages.labels.unlimited') }}
                                    @endif
                                </strong>
                            </div>
                            <div class="teacher-coupon-card__info">
                                <span>{{ __('coupons::teacher/messages.labels.applies') }}</span>
                                <strong>
                                    @if ($coupon->courses->isNotEmpty())
                                        {{ __('coupons::teacher/messages.labels.course_limited', ['count' => $coupon->courses->count()]) }}
                                    @elseif ($coupon->students->isNotEmpty())
                                        {{ __('coupons::teacher/messages.labels.student_limited', ['count' => $coupon->students->count()]) }}
                                    @else
                                        {{ __('coupons::teacher/messages.labels.all') }}
                                    @endif
                                </strong>
                            </div>
                        </div>

                        @if ($teacher->packageHasFeature('can_view_activity_logs'))
                            <div class="teacher-coupon-card__history">
                                <div class="teacher-coupon-card__history-title">{{ __('coupons::teacher/messages.labels.recent_activity') }}</div>
                                <div class="teacher-coupon-card__history-list">
                                    @forelse ($coupon->teacher_activity_preview ?? collect() as $activity)
                                        <article class="teacher-coupon-card__history-item">
                                            <strong>
                                                {{ match ($activity->action) {
                                                    'coupon_created' => __('coupons::teacher/messages.logs.created'),
                                                    'coupon_updated' => __('coupons::teacher/messages.logs.updated'),
                                                    'coupon_deleted' => __('coupons::teacher/messages.logs.deleted'),
                                                    'coupon_priority_enabled' => __('coupons::teacher/messages.logs.priority_enabled'),
                                                    'coupon_priority_disabled' => __('coupons::teacher/messages.logs.priority_disabled'),
                                                    'coupon_students_updated' => __('coupons::teacher/messages.logs.students_updated'),
                                                    'coupon_courses_updated' => __('coupons::teacher/messages.logs.courses_updated'),
                                                    default => $activity->description ?: $activity->action,
                                                } }}
                                            </strong>
                                            <span>{{ optional($activity->created_at)->format('d/m/Y H:i') }}</span>
                                        </article>
                                    @empty
                                        <div class="teacher-coupon-card__history-empty">{{ __('coupons::teacher/messages.labels.no_activity') }}</div>
                                    @endforelse
                                </div>
                            </div>
                        @else
                            <div class="teacher-coupon-card__history teacher-coupon-card__history--locked">
                                <div class="teacher-coupon-card__history-title">{{ __('coupons::teacher/messages.labels.recent_activity') }}</div>
                                <div class="teacher-coupon-card__history-empty">
                                    {{ __('courses::teacher/messages.package_features.activity_logs_locked') }}
                                </div>
                                <a href="{{ route('teacher.dashboard.package.upgrade') }}" class="btn btn-sm btn-outline-warning mt-3">
                                    {{ __('courses::teacher/messages.package_features.upgrade_cta') }}
                                </a>
                            </div>
                        @endif

                        <div class="teacher-coupon-card__actions">
                            @if ($canManageCoupons && ($couponUsage['has_limit'] ?? false))
                                <form action="{{ route('teacher.dashboard.coupons.priority', $coupon->id) }}" method="POST" class="d-inline-block">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-warning btn-sm">
                                        {{ $coupon->is_package_priority ? __('coupons::teacher/messages.actions.unprioritize') : __('coupons::teacher/messages.actions.prioritize') }}
                                    </button>
                                </form>
                            @endif
                            @if ($canManageCoupons && !$isLockedCoupon)
                                <a href="{{ route('teacher.dashboard.coupons.edit', $coupon->id) }}" class="btn btn-outline-warning btn-sm">
                                    {{ __('coupons::teacher/messages.actions.edit') }}
                                </a>
                                <a href="{{ route('teacher.dashboard.coupons.courses', $coupon->id) }}" class="btn btn-outline-primary btn-sm">
                                    {{ __('coupons::teacher/messages.actions.assign_courses') }}
                                </a>
                                <a href="{{ route('teacher.dashboard.coupons.students', $coupon->id) }}" class="btn btn-outline-secondary btn-sm">
                                    {{ __('coupons::teacher/messages.actions.assign_students') }}
                                </a>
                            @endif
                            <form action="{{ route('teacher.dashboard.coupons.delete', $coupon->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('{{ __('coupons::teacher/messages.confirm_delete') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    {{ __('coupons::teacher/messages.actions.delete') }}
                                </button>
                            </form>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <div class="teacher-coupons-empty">
                        <div class="teacher-coupons-empty__icon"><i class="fas fa-ticket"></i></div>
                        <h4>{{ __('coupons::teacher/messages.empty.title') }}</h4>
                        <p class="mb-0">{{ __('coupons::teacher/messages.empty.description') }}</p>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $coupons->links() }}
        </div>
    </div>
@endsection

@section('stylesheets')
    <style>
        .teacher-coupons-shell {
            background:
                radial-gradient(circle at top right, rgba(56, 189, 248, 0.08), transparent 30%),
                linear-gradient(180deg, rgba(17, 24, 39, 0.94) 0%, rgba(15, 23, 42, 0.98) 100%);
        }

        .teacher-coupons-hero {
            display: flex;
            justify-content: space-between;
            gap: 1.5rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }

        .teacher-coupons-kicker {
            display: inline-flex;
            padding: 0.45rem 0.8rem;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.14);
            color: #8fc3ff;
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .teacher-coupons-title {
            margin-top: 1rem;
            margin-bottom: 0.55rem;
            font-size: clamp(2rem, 3vw, 2.8rem);
            font-weight: 900;
            color: #f8fbff;
        }

        .teacher-coupons-desc {
            max-width: 760px;
            color: #a9bbd5;
            line-height: 1.75;
        }

        .teacher-disabled-action {
            display: inline-flex;
            cursor: not-allowed;
        }

        .teacher-disabled-action-wrap {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 0.45rem;
        }

        .teacher-disabled-action .btn[disabled] {
            pointer-events: none;
            opacity: 0.55;
        }

        .teacher-disabled-action__note {
            display: none;
            max-width: 320px;
            color: #fbbf24;
            font-size: 0.82rem;
            line-height: 1.45;
        }

        .teacher-coupon-card,
        .teacher-coupons-empty {
            border: 1px solid rgba(96, 165, 250, 0.16);
            border-radius: 22px;
            background: rgba(18, 28, 50, 0.72);
            box-shadow: 0 18px 42px rgba(2, 6, 23, 0.18);
            padding: 1.25rem;
        }

        .teacher-coupon-card__head {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: center;
            margin-bottom: 1rem;
        }

        .teacher-coupon-card__code {
            color: #f8fbff;
            font-weight: 800;
        }

        .teacher-coupon-card__meta {
            color: #9fb5d0;
        }

        .teacher-coupon-card__badge {
            padding: 0.35rem 0.8rem;
            border-radius: 999px;
            background: rgba(59, 130, 246, 0.16);
            color: #93c5fd;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .teacher-coupon-card__priority {
            display: inline-flex;
            margin-top: 0.55rem;
            padding: 0.3rem 0.7rem;
            border-radius: 999px;
            background: rgba(250, 204, 21, 0.18);
            color: #fde68a;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .teacher-coupon-card__limit-badge {
            display: inline-flex;
            margin-top: 0.55rem;
            padding: 0.35rem 0.8rem;
            border-radius: 999px;
            background: rgba(248, 113, 113, 0.18);
            color: #fecaca;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.01em;
        }

        .teacher-coupon-card__lock {
            margin-bottom: 1rem;
            padding: 0.85rem 1rem;
            border-radius: 16px;
            background: rgba(245, 158, 11, 0.14);
            color: #fde68a;
            line-height: 1.55;
        }

        .teacher-coupon-card__grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.85rem;
            margin-bottom: 1rem;
        }

        .teacher-coupon-card__info {
            padding: 0.95rem 1rem;
            border-radius: 18px;
            background: rgba(11, 19, 36, 0.72);
            border: 1px solid rgba(96, 165, 250, 0.12);
        }

        .teacher-coupon-card__info span {
            display: block;
            color: #8ca6c6;
            margin-bottom: 0.4rem;
            font-size: 0.9rem;
        }

        .teacher-coupon-card__info strong {
            display: block;
            color: #f8fbff;
            font-weight: 700;
            line-height: 1.55;
        }

        .teacher-coupon-card__history {
            margin-bottom: 1rem;
            padding: 0.95rem 1rem;
            border-radius: 16px;
            border: 1px solid rgba(96, 165, 250, 0.12);
            background: var(--admin-history-bg);
        }

        .teacher-coupon-card__history-title {
            margin-bottom: 0.7rem;
            color: var(--admin-history-text);
            font-size: 0.9rem;
            font-weight: 800;
        }

        .teacher-coupon-card__history-list {
            display: grid;
            gap: 0.55rem;
        }

        .teacher-coupon-card__history-item {
            display: flex;
            justify-content: space-between;
            gap: 0.9rem;
            align-items: flex-start;
        }

        .teacher-coupon-card__history-item strong {
            color: var(--admin-history-text);
            font-size: 0.9rem;
            font-weight: 700;
        }

        .teacher-coupon-card__history-item span,
        .teacher-coupon-card__history-empty {
            color: var(--admin-history-text);
            opacity: 0.8;
            font-size: 0.8rem;
            line-height: 1.5;
        }

        .teacher-coupon-card__history--locked {
            border-style: dashed;
            background: rgba(245, 158, 11, 0.08);
        }

        .teacher-coupon-card__actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
        }

        .teacher-coupons-empty {
            text-align: center;
        }

        .teacher-coupons-empty__icon {
            width: 68px;
            height: 68px;
            margin: 0 auto 1rem;
            border-radius: 20px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.22), rgba(37, 99, 235, 0.24));
            color: #dbeafe;
            font-size: 1.6rem;
        }

        .teacher-coupons-empty h4 {
            color: #f8fbff;
            margin-bottom: 0.55rem;
        }

        .teacher-coupons-empty p {
            color: #a9bbd5;
            max-width: 640px;
            margin: 0 auto;
            line-height: 1.75;
        }

        html[data-theme="light"] .teacher-coupons-shell {
            background:
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.08), transparent 30%),
                linear-gradient(180deg, rgba(248, 250, 252, 0.96) 0%, rgba(241, 245, 249, 0.98) 100%);
        }

        html[data-theme="light"] .teacher-coupons-kicker {
            background: rgba(37, 99, 235, 0.12);
            color: #1d4ed8;
        }

        html[data-theme="light"] .teacher-coupons-title {
            color: #0f172a;
        }

        html[data-theme="light"] .teacher-coupons-desc {
            color: #475569;
        }

        html[data-theme="light"] .teacher-disabled-action__note {
            color: #b45309;
        }

        html[data-theme="light"] .teacher-coupon-card,
        html[data-theme="light"] .teacher-coupons-empty {
            background: var(--admin-surface);
            border-color: var(--admin-border);
            box-shadow: var(--admin-card-shadow);
        }

        html[data-theme="light"] .teacher-coupon-card__code,
        html[data-theme="light"] .teacher-coupons-empty h4 {
            color: var(--admin-text);
        }

        html[data-theme="light"] .teacher-coupon-card__meta,
        html[data-theme="light"] .teacher-coupons-empty p {
            color: var(--admin-muted);
        }

        html[data-theme="light"] .teacher-coupon-card__badge {
            background: rgba(37, 99, 235, 0.12);
            color: #1d4ed8;
        }

        html[data-theme="light"] .teacher-coupon-card__priority {
            background: rgba(250, 204, 21, 0.2);
            color: #92400e;
        }

        html[data-theme="light"] .teacher-coupon-card__limit-badge {
            background: rgba(248, 113, 113, 0.14);
            color: #b91c1c;
        }

        html[data-theme="light"] .teacher-coupon-card__lock {
            background: rgba(245, 158, 11, 0.12);
            color: #92400e;
        }

        html[data-theme="light"] .teacher-coupon-card__info {
            background: var(--admin-subtle-bg);
            border-color: var(--admin-border);
        }

        html[data-theme="light"] .teacher-coupon-card__info span {
            color: var(--admin-muted);
        }

        html[data-theme="light"] .teacher-coupon-card__info strong {
            color: var(--admin-text);
        }

        html[data-theme="light"] .teacher-coupons-empty__icon {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.12), rgba(56, 189, 248, 0.15));
            color: #1d4ed8;
        }

        @media (max-width: 767.98px) {
            .teacher-coupon-card__grid {
                grid-template-columns: 1fr;
            }

            .teacher-disabled-action__note {
                display: block;
            }
        }
    </style>
@endsection
