@extends('layouts.teacher')

@section('content')
    <div class="teacher-panel teacher-coupons-shell">
        <div class="teacher-coupons-hero">
            <div>
                <span class="teacher-coupons-kicker">{{ __('teacher::coupons.hero.kicker') }}</span>
                <h3 class="teacher-coupons-title">{{ __('teacher::coupons.hero.title') }}</h3>
                <p class="teacher-coupons-desc mb-0">{{ __('teacher::coupons.hero.description') }}</p>
            </div>
            <div class="teacher-coupons-actions">
                @if ($couponLimit === null || $couponCount < $couponLimit)
                    <a href="{{ route('teacher.dashboard.coupons.create') }}" class="btn btn-primary">
                        {{ __('teacher::coupons.actions.create') }}
                    </a>
                @else
                    <a href="{{ route('teacher.dashboard.package.upgrade') }}" class="btn btn-warning">
                        {{ __('teacher::dashboard.package_features.upgrade_cta') }}
                    </a>
                @endif
            </div>
        </div>

        <div class="alert alert-info border-0 mb-4">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center">
                <div>
                    <strong>{{ __('teacher::coupons.limit.title') }}</strong>
                    <div class="mt-1 text-muted">
                        {{ __('teacher::coupons.limit.description', [
                            'count' => $couponCount,
                            'limit' => $couponLimit ?? __('teacher::coupons.labels.unlimited'),
                        ]) }}
                    </div>
                </div>
                @if ($couponLimit !== null && $couponCount >= $couponLimit)
                    <a href="{{ route('teacher.dashboard.package.upgrade') }}" class="btn btn-sm btn-warning">
                        {{ __('teacher::dashboard.package_features.upgrade_cta') }}
                    </a>
                @endif
            </div>
        </div>

        <div class="row g-3">
            @forelse ($coupons as $coupon)
                <div class="col-xl-6">
                    <article class="teacher-coupon-card">
                        <div class="teacher-coupon-card__head">
                            <div>
                                <h4 class="teacher-coupon-card__code mb-0">{{ $coupon->code }}</h4>
                                <p class="teacher-coupon-card__meta mb-0">
                                    {{ $coupon->discount_type === 'percent' ? $coupon->discount_value . '%' : money($coupon->discount_value) }}
                                    {{ __('teacher::coupons.labels.discount') }}
                                </p>
                            </div>
                            <span class="teacher-coupon-card__badge">
                                {{ $coupon->per_student_once ? __('teacher::coupons.labels.once') : __('teacher::coupons.labels.multi') }}
                            </span>
                        </div>

                        <div class="teacher-coupon-card__grid">
                            <div class="teacher-coupon-card__info">
                                <span>{{ __('teacher::coupons.labels.total_condition') }}</span>
                                <strong>{{ $coupon->total_condition ? money($coupon->total_condition) : __('teacher::coupons.labels.none') }}</strong>
                            </div>
                            <div class="teacher-coupon-card__info">
                                <span>{{ __('teacher::coupons.labels.usage_limit') }}</span>
                                <strong>
                                    @if ($coupon->count)
                                        {{ max($coupon->count - ($coupon->usagescoupon_count ?? 0), 0) }} / {{ $coupon->count }}
                                    @else
                                        {{ __('teacher::coupons.labels.unlimited') }}
                                    @endif
                                </strong>
                            </div>
                            <div class="teacher-coupon-card__info">
                                <span>{{ __('teacher::coupons.labels.time_range') }}</span>
                                <strong>
                                    @if ($coupon->start_date && $coupon->end_date)
                                        {{ \Carbon\Carbon::parse($coupon->start_date)->format('d/m/Y') }}
                                        ->
                                        {{ \Carbon\Carbon::parse($coupon->end_date)->format('d/m/Y') }}
                                    @else
                                        {{ __('teacher::coupons.labels.unlimited') }}
                                    @endif
                                </strong>
                            </div>
                            <div class="teacher-coupon-card__info">
                                <span>{{ __('teacher::coupons.labels.applies') }}</span>
                                <strong>
                                    @if ($coupon->courses->isNotEmpty())
                                        {{ __('teacher::coupons.labels.course_limited', ['count' => $coupon->courses->count()]) }}
                                    @elseif ($coupon->students->isNotEmpty())
                                        {{ __('teacher::coupons.labels.student_limited', ['count' => $coupon->students->count()]) }}
                                    @else
                                        {{ __('teacher::coupons.labels.all') }}
                                    @endif
                                </strong>
                            </div>
                        </div>

                        <div class="teacher-coupon-card__actions">
                            <a href="{{ route('teacher.dashboard.coupons.edit', $coupon->id) }}" class="btn btn-outline-secondary btn-sm">
                                {{ __('teacher::coupons.actions.edit') }}
                            </a>
                            <a href="{{ route('teacher.dashboard.coupons.courses', $coupon->id) }}" class="btn btn-outline-secondary btn-sm">
                                {{ __('teacher::coupons.actions.assign_courses') }}
                            </a>
                            <a href="{{ route('teacher.dashboard.coupons.students', $coupon->id) }}" class="btn btn-outline-secondary btn-sm">
                                {{ __('teacher::coupons.actions.assign_students') }}
                            </a>
                            <form action="{{ route('teacher.dashboard.coupons.delete', $coupon->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('{{ __('teacher::coupons.confirm_delete') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    {{ __('teacher::coupons.actions.delete') }}
                                </button>
                            </form>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <div class="teacher-coupons-empty">
                        <div class="teacher-coupons-empty__icon"><i class="fas fa-ticket"></i></div>
                        <h4>{{ __('teacher::coupons.empty.title') }}</h4>
                        <p class="mb-0">{{ __('teacher::coupons.empty.description') }}</p>
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
        }
    </style>
@endsection
