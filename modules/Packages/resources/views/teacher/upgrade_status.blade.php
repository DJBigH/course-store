@extends('layouts.teacher')

@section('content')
    <div class="teacher-page-shell">
        <div class="teacher-panel teacher-upgrade-shell">
            <div class="teacher-upgrade-hero">
                <div>
                    <span class="teacher-upgrade-kicker">{{ __('packages::teacher.upgrade.status_title') }}</span>
                    <h3 class="teacher-upgrade-title">{{ __('packages::teacher.upgrade.status_title') }}</h3>
                    <p class="teacher-upgrade-desc mb-0">{{ __('packages::teacher.upgrade.status_description') }}</p>
                </div>
                <a href="{{ route('teacher.dashboard.index') }}" class="btn btn-outline-secondary teacher-upgrade-back">
                    {{ __('packages::teacher.upgrade.back') }}
                </a>
            </div>

            @if (session('msg_success'))
                <div class="alert alert-success">{{ session('msg_success') }}</div>
            @endif
            @if (session('msg_danger'))
                <div class="alert alert-danger">{{ session('msg_danger') }}</div>
            @endif

            <div class="row g-4 align-items-start">
                <div class="col-xl-8">
                    @php
                        $targetPackage = $upgradeRequest->package;
                        $isQueuedActivation = $upgradeRequest->status === 'approved'
                            && $upgradeRequest->activated_at === null
                            && $upgradeRequest->activates_at !== null;
                        $isDowngrade = $currentPackage
                            && $targetPackage
                            && (int) $targetPackage->sort_order < (int) $currentPackage->sort_order;
                        $noticeTitle = null;
                        $noticeDescription = null;

                        if ($upgradeRequest->status === 'pending_review' && $upgradeRequest->payment_method === 'bank_transfer') {
                            $noticeTitle = __('packages::teacher.upgrade.notice_thank_you_title');
                            $noticeDescription = __('packages::teacher.upgrade.notice_thank_you_description');
                        } elseif ($isQueuedActivation && $isDowngrade) {
                            $noticeTitle = __('packages::teacher.upgrade.notice_downgrade_title');
                            $noticeDescription = __('packages::teacher.upgrade.notice_downgrade_description', [
                                'date' => $upgradeRequest->activates_at->format('d/m/Y'),
                            ]);
                        } elseif ($upgradeRequest->activates_at) {
                            $noticeTitle = __('packages::teacher.upgrade.notice_activation_title');
                            $noticeDescription = __('packages::teacher.upgrade.notice_activation_description', [
                                'date' => $upgradeRequest->activates_at->format('d/m/Y'),
                            ]);
                        }
                    @endphp

                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="teacher-upgrade-card h-100">
                                <div class="teacher-upgrade-card__top">
                                    <span class="teacher-upgrade-card__tag">{{ __('packages::teacher.common.current_title') }}</span>
                                </div>
                                <div class="teacher-upgrade-card__body">
                                    <h5>{{ $currentPackage?->name_locale ?: $currentPackage?->name }}</h5>
                                    <p class="teacher-upgrade-card__desc mb-0">
                                        {{ __('packages::teacher.common.current_meta', [
                                            'commission' => rtrim(rtrim(number_format((float) ($currentPackage?->commission_rate ?? 0), 2, '.', ''), '0'), '.'),
                                            'limit' => $currentPackage?->effective_course_limit ?: __('courses::teacher/messages.courses.unlimited'),
                                        ]) }}
                                    </p>
                                    @if ($teacher->package_expires_at)
                                        <p class="teacher-upgrade-card__time mb-0">
                                            {{ __('packages::teacher.common.expires_at', [
                                                'date' => $teacher->package_expires_at->format('d/m/Y'),
                                                'days' => max(now()->startOfDay()->diffInDays($teacher->package_expires_at->copy()->startOfDay(), false), 0)
                                            ]) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="teacher-upgrade-card is-selected h-100">
                                @if ($upgradeRequest->package?->is_featured)
                                    <span class="teacher-upgrade-card__ribbon">
                                        {{ __('teacher::landing.packages.most_popular') }}
                                    </span>
                                @endif

                                <div class="teacher-upgrade-card__top">
                                    <span class="teacher-upgrade-card__tag">{{ __('packages::teacher.common.target_title') }}</span>
                                    @if (($upgradeRequest->package?->badge_text_locale ?: '') !== '')
                                        <span class="teacher-upgrade-card__badge">{{ $upgradeRequest->package?->badge_text_locale }}</span>
                                    @endif
                                </div>
                                <div class="teacher-upgrade-card__body">
                                    <h5>{{ $upgradeRequest->package?->name_locale ?: $upgradeRequest->package?->name }}</h5>
                                    <div class="teacher-upgrade-card__price">
                                        {{ $upgradeRequest->payable_amount > 0 ? moneyLocale($upgradeRequest->payable_amount) : moneyLocale(0) }}
                                    </div>
                                    <p class="teacher-upgrade-card__desc mb-0">
                                        {{ __('packages::teacher.common.current_meta', [
                                            'commission' => rtrim(rtrim(number_format((float) ($upgradeRequest->package?->commission_rate ?? 0), 2, '.', ''), '0'), '.'),
                                            'limit' => $upgradeRequest->package?->effective_course_limit ?: __('courses::teacher/messages.courses.unlimited'),
                                        ]) }}
                                    </p>
                                    @if ($upgradeRequest->activates_at)
                                        <p class="teacher-upgrade-card__time mb-0">
                                            {{ $upgradeRequest->activated_at ? __('packages::teacher.upgrade.status_starts_at') : __('packages::teacher.upgrade.status_will_start_at') }} {{ $upgradeRequest->activates_at->format('d/m/Y') }}
                                            @if ($upgradeRequest->package_expires_at)
                                                • {{ __('packages::teacher.common.current_expiry_short', ['date' => $upgradeRequest->package_expires_at->format('d/m/Y')]) }}
                                            @endif
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($noticeTitle && $noticeDescription)
                        <div class="teacher-upgrade-notice mt-4">
                            <div class="teacher-upgrade-notice__icon">i</div>
                            <div>
                                <div class="teacher-upgrade-notice__title">{{ $noticeTitle }}</div>
                                <p class="teacher-upgrade-notice__desc mb-0">{{ $noticeDescription }}</p>
                            </div>
                        </div>
                    @endif

                    @if (!empty($overLimitWarnings))
                        <div class="teacher-upgrade-warning mt-4">
                            <div class="teacher-upgrade-warning__icon">!</div>
                            <div>
                                <div class="teacher-upgrade-warning__title">{{ __('packages::teacher.upgrade.over_limit_title') }}</div>
                                <p class="teacher-upgrade-warning__desc">{{ __('packages::teacher.upgrade.over_limit_description') }}</p>
                                <ul class="teacher-upgrade-warning__list mb-0">
                                    @foreach ($overLimitWarnings as $warning)
                                        <li>{{ $warning }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    @if (!empty($featureLossWarnings))
                        <div class="teacher-upgrade-warning is-feature-loss mt-4">
                            <div class="teacher-upgrade-warning__icon">!</div>
                            <div>
                                <div class="teacher-upgrade-warning__title">{{ __('packages::teacher.upgrade.feature_loss_title') }}</div>
                                <p class="teacher-upgrade-warning__desc">{{ __('packages::teacher.upgrade.feature_loss_description') }}</p>
                                <ul class="teacher-upgrade-warning__list mb-0">
                                    @foreach ($featureLossWarnings as $warning)
                                        <li>{{ $warning }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    @if ($upgradeRequest->status === 'pending_payment')
                        <div class="teacher-upgrade-action mt-4">
                            <div>
                                <div class="teacher-upgrade-action__title">{{ __('packages::teacher.upgrade.pending_payment_title') }}</div>
                                <p class="teacher-upgrade-action__desc mb-0">{{ __('packages::teacher.common.pending_payment_description') }}</p>
                            </div>
                            <form method="POST" action="{{ route('teacher.dashboard.package.upgrade.mark-paid') }}">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    {{ __('packages::teacher.upgrade.mark_paid') }}
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                <div class="col-xl-4">
                    <aside class="teacher-upgrade-summary">
                        <div class="teacher-upgrade-summary__label">{{ __('packages::teacher.common.status_title') }}</div>
                        <div class="teacher-upgrade-summary__name">{{ $upgradeRequest->display_status }}</div>
                        <div class="teacher-upgrade-summary__price">
                            {{ $upgradeRequest->payable_amount > 0 ? moneyLocale($upgradeRequest->payable_amount) : moneyLocale(0) }}
                        </div>
                        <p class="teacher-upgrade-summary__meta">{{ $upgradeRequest->payment_method_label }}</p>

                        <div class="teacher-upgrade-summary__stats">
                            <div class="teacher-upgrade-summary__stat">
                                <span>{{ __('packages::teacher.upgrade.label_status') }}</span>
                                <strong>{{ $upgradeRequest->display_status }}</strong>
                            </div>
                            <div class="teacher-upgrade-summary__stat">
                                <span>{{ __('packages::teacher.upgrade.label_payable') }}</span>
                                <strong>{{ $upgradeRequest->payable_amount > 0 ? moneyLocale($upgradeRequest->payable_amount) : moneyLocale(0) }}</strong>
                            </div>
                            <div class="teacher-upgrade-summary__stat">
                                <span>{{ __('packages::teacher.common.payment_method') }}</span>
                                <strong>{{ $upgradeRequest->payment_method_label }}</strong>
                            </div>
                            <div class="teacher-upgrade-summary__stat">
                                <span>{{ __('packages::teacher.upgrade.label_submitted_at') }}</span>
                                <strong>{{ optional($upgradeRequest->submitted_at)->format('d/m/Y H:i') ?: '-' }}</strong>
                            </div>
                        </div>

@if (in_array($upgradeRequest->status, ['pending_payment', 'pending_review'], true))
    <form method="POST" action="{{ route('teacher.dashboard.package.upgrade.cancel') }}" class="mt-4">
        @csrf
        <button
            type="submit"
            class="btn btn-outline-danger w-100"
            onclick="return confirm(@json(__('packages::teacher.upgrade.confirm_cancel')))"
        >
            {{ __('packages::teacher.upgrade.cancel_transaction') }}
        </button>
    </form>
@endif

                        <a href="{{ route('teacher.dashboard.index') }}" class="btn btn-outline-secondary w-100 mt-4">
                            {{ __('packages::teacher.upgrade.back') }}
                        </a>
                    </aside>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('stylesheets')
    <style>
        .teacher-upgrade-shell {
            background:
                radial-gradient(circle at top right, rgba(56, 189, 248, 0.08), transparent 26%),
                linear-gradient(180deg, rgba(17, 24, 39, 0.94) 0%, rgba(15, 23, 42, 0.98) 100%);
        }

        .teacher-upgrade-hero {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        .teacher-upgrade-kicker {
            display: inline-flex;
            align-items: center;
            padding: 0.45rem 0.85rem;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.14);
            color: #8fc3ff;
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .teacher-upgrade-title {
            margin-top: 1rem;
            margin-bottom: 0.5rem;
            font-size: clamp(2rem, 3vw, 2.8rem);
            font-weight: 900;
            color: #f8fbff;
        }

        .teacher-upgrade-desc {
            max-width: 740px;
            color: #a9bbd5;
            font-size: 1rem;
            line-height: 1.7;
        }

        .teacher-upgrade-card,
        .teacher-upgrade-summary,
        .teacher-upgrade-action,
        .teacher-upgrade-notice,
        .teacher-upgrade-warning {
            border: 1px solid rgba(96, 165, 250, 0.18);
            background: rgba(15, 23, 42, 0.72);
            border-radius: 26px;
            box-shadow: 0 22px 54px rgba(2, 6, 23, 0.2);
        }

        .teacher-upgrade-card {
            position: relative;
            display: flex;
            flex-direction: column;
            min-height: 100%;
            padding: 1.25rem;
            background:
                radial-gradient(circle at top right, rgba(59, 130, 246, 0.12), transparent 24%),
                linear-gradient(180deg, rgba(18, 28, 50, 0.96) 0%, rgba(12, 21, 39, 0.96) 100%);
            overflow: hidden;
        }

        .teacher-upgrade-card.is-selected {
            border-color: rgba(96, 165, 250, 0.48);
            box-shadow:
                0 0 0 3px rgba(8, 17, 31, 0.86),
                0 0 0 6px rgba(96, 165, 250, 0.24),
                0 34px 68px rgba(2, 6, 23, 0.32);
        }

        .teacher-upgrade-card__ribbon {
            position: absolute;
            top: 1rem;
            left: 1rem;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            padding: 0.42rem 0.82rem;
            border-radius: 999px;
            background: linear-gradient(135deg, rgba(45, 212, 191, 0.95), rgba(59, 130, 246, 0.92));
            color: #04111f;
            font-size: 0.76rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            box-shadow: 0 14px 28px rgba(20, 184, 166, 0.22);
        }

        .teacher-upgrade-card__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding-top: 2rem;
        }

        .teacher-upgrade-card__tag,
        .teacher-upgrade-card__badge {
            display: inline-flex;
            align-items: center;
            padding: 0.45rem 0.8rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 800;
        }

        .teacher-upgrade-card__tag {
            background: rgba(37, 99, 235, 0.16);
            color: #8fc3ff;
        }

        .teacher-upgrade-card__badge {
            background: rgba(45, 212, 191, 0.16);
            color: #8af4e3;
        }

        .teacher-upgrade-card__body h5 {
            color: #f8fbff;
            font-size: 1.35rem;
            font-weight: 800;
            margin-bottom: 0.45rem;
        }

        .teacher-upgrade-card__price {
            color: #ffffff;
            font-size: 2rem;
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 0.65rem;
        }

        .teacher-upgrade-card__desc {
            color: #a9bbd5;
            line-height: 1.65;
        }

        .teacher-upgrade-card__time {
            margin-top: 0.85rem;
            color: #8fc3ff;
            font-weight: 700;
            line-height: 1.6;
        }

        .teacher-upgrade-action {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.25rem 1.35rem;
        }

        .teacher-upgrade-notice {
            display: flex;
            align-items: flex-start;
            gap: 0.95rem;
            padding: 1.15rem 1.25rem;
            background:
                radial-gradient(circle at top right, rgba(59, 130, 246, 0.12), transparent 24%),
                linear-gradient(180deg, rgba(17, 31, 58, 0.96) 0%, rgba(11, 22, 41, 0.96) 100%);
            border-color: rgba(96, 165, 250, 0.32);
        }

        .teacher-upgrade-notice__icon {
            width: 2rem;
            height: 2rem;
            flex: 0 0 2rem;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: #eff6ff;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.92), rgba(14, 165, 233, 0.92));
            box-shadow: 0 10px 24px rgba(14, 165, 233, 0.28);
        }

        .teacher-upgrade-notice__title {
            color: #f8fbff;
            font-size: 1rem;
            font-weight: 800;
            margin-bottom: 0.3rem;
        }

        .teacher-upgrade-notice__desc {
            color: #b8cae2;
            line-height: 1.65;
        }

        .teacher-upgrade-warning {
            display: flex;
            align-items: flex-start;
            gap: 0.95rem;
            padding: 1.15rem 1.25rem;
            background:
                radial-gradient(circle at top right, rgba(245, 158, 11, 0.14), transparent 24%),
                linear-gradient(180deg, rgba(55, 35, 11, 0.96) 0%, rgba(34, 23, 10, 0.96) 100%);
            border-color: rgba(251, 191, 36, 0.34);
        }

        .teacher-upgrade-warning.is-feature-loss {
            background:
                radial-gradient(circle at top right, rgba(244, 114, 182, 0.14), transparent 24%),
                linear-gradient(180deg, rgba(56, 21, 48, 0.96) 0%, rgba(34, 15, 31, 0.96) 100%);
            border-color: rgba(244, 114, 182, 0.3);
        }

        .teacher-upgrade-warning__icon {
            width: 2rem;
            height: 2rem;
            flex: 0 0 2rem;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            color: #1f1304;
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.98), rgba(245, 158, 11, 0.96));
            box-shadow: 0 10px 24px rgba(245, 158, 11, 0.22);
        }

        .teacher-upgrade-warning.is-feature-loss .teacher-upgrade-warning__icon {
            color: #2f0f25;
            background: linear-gradient(135deg, rgba(244, 114, 182, 0.98), rgba(236, 72, 153, 0.96));
            box-shadow: 0 10px 24px rgba(236, 72, 153, 0.2);
        }

        .teacher-upgrade-warning__title {
            color: #fff7ed;
            font-size: 1rem;
            font-weight: 800;
            margin-bottom: 0.3rem;
        }

        .teacher-upgrade-warning__desc {
            color: #fed7aa;
            line-height: 1.65;
            margin-bottom: 0.65rem;
        }

        .teacher-upgrade-warning__list {
            margin: 0;
            padding-left: 1.1rem;
            color: #ffedd5;
            line-height: 1.7;
        }

        .teacher-upgrade-action__title {
            color: #f8fbff;
            font-size: 1.05rem;
            font-weight: 800;
            margin-bottom: 0.35rem;
        }

        .teacher-upgrade-action__desc {
            color: #9fb1cc;
            line-height: 1.65;
        }

        .teacher-upgrade-summary {
            position: sticky;
            top: 96px;
            padding: 1.4rem;
        }

        .teacher-upgrade-summary__label {
            color: #8fb5e9;
            font-size: 0.84rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 700;
        }

        .teacher-upgrade-summary__name {
            margin-top: 0.35rem;
            color: #f8fbff;
            font-size: 1.4rem;
            font-weight: 800;
        }

        .teacher-upgrade-summary__price {
            margin-top: 0.85rem;
            color: #ffffff;
            font-size: 2.2rem;
            font-weight: 900;
            line-height: 1.1;
        }

        .teacher-upgrade-summary__meta {
            margin-top: 0.35rem;
            color: #a9bbd5;
        }

        .teacher-upgrade-summary__stats {
            display: grid;
            gap: 0.9rem;
            margin-top: 1.3rem;
        }

        .teacher-upgrade-summary__stat {
            padding: 0.9rem 1rem;
            border-radius: 18px;
            border: 1px solid rgba(96, 165, 250, 0.14);
            background: rgba(18, 28, 50, 0.72);
        }

        .teacher-upgrade-summary__stat span {
            display: block;
            color: #8fb5e9;
            font-size: 0.82rem;
            margin-bottom: 0.3rem;
        }

        .teacher-upgrade-summary__stat strong {
            color: #f8fbff;
            font-size: 1rem;
            font-weight: 700;
        }

        @media (max-width: 1199.98px) {
            .teacher-upgrade-summary {
                position: static;
            }
        }

        @media (max-width: 767.98px) {
            .teacher-upgrade-hero,
            .teacher-upgrade-action {
                flex-direction: column;
                align-items: stretch;
            }

            .teacher-upgrade-title {
                font-size: 2rem;
            }

            .teacher-upgrade-notice,
            .teacher-upgrade-warning {
                padding: 1rem;
            }
        }
    </style>
@endsection
