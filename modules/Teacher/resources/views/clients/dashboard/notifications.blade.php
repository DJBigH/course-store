@extends('layouts.teacher')

@section('content')
    <div class="teacher-page-shell">
        <div class="teacher-panel teacher-notification-shell">
            <div class="teacher-notification-hero">
                <div>
                    <span class="teacher-notification-kicker">{{ __('teacher::dashboard.notifications.hero_kicker') }}</span>
                    <h3 class="teacher-notification-title">{{ __('teacher::dashboard.notifications.title') }}</h3>
                    <p class="teacher-notification-desc mb-0">{{ __('teacher::dashboard.notifications.description') }}</p>
                </div>
                <div class="teacher-notification-count">
                    <strong>{{ $notificationSummary['count'] ?? $notifications->count() }}</strong>
                    <span>{{ __('teacher::dashboard.notifications.active_count') }}</span>
                </div>
            </div>

            <div class="teacher-notification-list">
                @forelse ($notifications as $notification)
                    @php
                        $severityClass = match ($notification['severity'] ?? 'secondary') {
                            'success' => 'success',
                            'warning' => 'warning',
                            'danger', 'error', 'critical' => 'danger',
                            'info', 'primary' => 'primary',
                            default => 'secondary',
                        };
                    @endphp
                    <a href="{{ $notification['url'] ?? route('teacher.dashboard.notifications') }}"
                        class="teacher-notification-card teacher-notification-card--{{ $severityClass }}">
                        <div class="teacher-notification-card__icon">
                            <i class="{{ $notification['icon'] ?? 'fas fa-bell' }}"></i>
                        </div>
                        <div class="teacher-notification-card__body">
                            <div class="teacher-notification-card__meta">
                                <span class="teacher-notification-pill">{{ $notification['type_label'] ?? __('teacher::dashboard.notifications.types.system') }}</span>
                                @if (!empty($notification['is_unread']))
                                    <span class="teacher-notification-unread">{{ __('teacher::dashboard.notifications.unread') }}</span>
                                @endif
                            </div>
                            <h4>{{ $notification['title'] ?? __('teacher::dashboard.notifications.types.system') }}</h4>
                            <p class="mb-0">{{ $notification['message'] ?? '' }}</p>
                        </div>
                        <div class="teacher-notification-card__time">
                            <span>{{ optional($notification['created_at'] ?? null)->diffForHumans() }}</span>
                            <strong>{{ __('teacher::dashboard.notifications.open_cta') }}</strong>
                        </div>
                    </a>
                @empty
                    <div class="teacher-notification-empty">
                        <div class="teacher-notification-empty__icon"><i class="fas fa-bell-slash"></i></div>
                        <h4>{{ __('teacher::dashboard.notifications.empty') }}</h4>
                        <p class="mb-0">{{ __('teacher::dashboard.notifications.empty_description') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@section('stylesheets')
    <style>
        .teacher-notification-shell {
            background:
                radial-gradient(circle at top right, rgba(56, 189, 248, 0.12), transparent 28%),
                radial-gradient(circle at left bottom, rgba(34, 197, 94, 0.08), transparent 26%),
                linear-gradient(180deg, rgba(17, 24, 39, 0.94) 0%, rgba(15, 23, 42, 0.98) 100%);
        }

        .teacher-notification-hero {
            display: flex;
            justify-content: space-between;
            gap: 1.25rem;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }

        .teacher-notification-kicker {
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

        .teacher-notification-title {
            margin-top: 1rem;
            margin-bottom: 0.55rem;
            font-size: clamp(2rem, 3vw, 2.7rem);
            font-weight: 900;
            color: #f8fbff;
        }

        .teacher-notification-desc {
            max-width: 760px;
            color: #a9bbd5;
            line-height: 1.75;
        }

        .teacher-notification-count {
            min-width: 180px;
            padding: 1.15rem 1.25rem;
            border-radius: 22px;
            background: rgba(18, 28, 50, 0.72);
            border: 1px solid rgba(96, 165, 250, 0.16);
            text-align: right;
            box-shadow: 0 18px 42px rgba(2, 6, 23, 0.18);
        }

        .teacher-notification-count strong {
            display: block;
            font-size: clamp(1.8rem, 4vw, 2.8rem);
            line-height: 1;
            color: #f8fbff;
        }

        .teacher-notification-count span {
            display: block;
            margin-top: 0.45rem;
            color: #a9bbd5;
        }

        .teacher-notification-list {
            display: grid;
            gap: 1rem;
        }

        .teacher-notification-card {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            gap: 1rem;
            align-items: center;
            padding: 1.15rem 1.25rem;
            border-radius: 22px;
            text-decoration: none;
            color: inherit;
            background: rgba(18, 28, 50, 0.72);
            border: 1px solid rgba(96, 165, 250, 0.16);
            box-shadow: 0 18px 42px rgba(2, 6, 23, 0.18);
            transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .teacher-notification-card:hover {
            transform: translateY(-2px);
            border-color: rgba(125, 211, 252, 0.4);
            box-shadow: 0 24px 44px rgba(2, 6, 23, 0.24);
            color: inherit;
        }

        .teacher-notification-card__icon {
            width: 54px;
            height: 54px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            font-size: 1.2rem;
        }

        .teacher-notification-card--primary .teacher-notification-card__icon,
        .teacher-notification-card--secondary .teacher-notification-card__icon {
            background: rgba(59, 130, 246, 0.14);
            color: #93c5fd;
        }

        .teacher-notification-card--success .teacher-notification-card__icon {
            background: rgba(34, 197, 94, 0.14);
            color: #86efac;
        }

        .teacher-notification-card--warning .teacher-notification-card__icon {
            background: rgba(245, 158, 11, 0.14);
            color: #fcd34d;
        }

        .teacher-notification-card--danger .teacher-notification-card__icon {
            background: rgba(239, 68, 68, 0.14);
            color: #fca5a5;
        }

        .teacher-notification-card__meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 0.45rem;
        }

        .teacher-notification-pill,
        .teacher-notification-unread {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 0.28rem 0.7rem;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .teacher-notification-pill {
            background: rgba(148, 163, 184, 0.16);
            color: #dce9ff;
        }

        .teacher-notification-unread {
            background: rgba(245, 158, 11, 0.18);
            color: #fcd34d;
        }

        .teacher-notification-card__body h4 {
            margin-bottom: 0.35rem;
            color: #f8fbff;
            font-size: 1.08rem;
            font-weight: 800;
        }

        .teacher-notification-card__body p {
            color: #a9bbd5;
            line-height: 1.7;
        }

        .teacher-notification-card__time {
            text-align: right;
            min-width: 128px;
        }

        .teacher-notification-card__time span {
            display: block;
            color: #94a3b8;
            font-size: 0.86rem;
        }

        .teacher-notification-card__time strong {
            display: inline-block;
            margin-top: 0.35rem;
            color: #dce9ff;
            font-size: 0.86rem;
        }

        .teacher-notification-empty {
            padding: 2.5rem 1.5rem;
            text-align: center;
            border-radius: 22px;
            background: rgba(18, 28, 50, 0.72);
            border: 1px solid rgba(96, 165, 250, 0.16);
            box-shadow: 0 18px 42px rgba(2, 6, 23, 0.18);
        }

        .teacher-notification-empty__icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 1rem;
            border-radius: 22px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.22), rgba(37, 99, 235, 0.24));
            color: #dbeafe;
            font-size: 1.8rem;
        }

        .teacher-notification-empty h4 {
            color: #f8fbff;
            margin-bottom: 0.55rem;
        }

        .teacher-notification-empty p {
            color: #a9bbd5;
            max-width: 560px;
            margin: 0 auto;
            line-height: 1.75;
        }

        html[data-theme="light"] .teacher-notification-shell {
            background:
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.08), transparent 30%),
                linear-gradient(180deg, rgba(248, 250, 252, 0.96) 0%, rgba(241, 245, 249, 0.98) 100%);
        }

        html[data-theme="light"] .teacher-notification-kicker {
            background: rgba(37, 99, 235, 0.12);
            color: #1d4ed8;
        }

        html[data-theme="light"] .teacher-notification-title {
            color: #0f172a;
        }

        html[data-theme="light"] .teacher-notification-desc,
        html[data-theme="light"] .teacher-notification-count span,
        html[data-theme="light"] .teacher-notification-card__body p,
        html[data-theme="light"] .teacher-notification-empty p,
        html[data-theme="light"] .teacher-notification-card__time span {
            color: #475569;
        }

        html[data-theme="light"] .teacher-notification-count,
        html[data-theme="light"] .teacher-notification-card,
        html[data-theme="light"] .teacher-notification-empty {
            background: var(--admin-surface);
            border-color: var(--admin-border);
            box-shadow: var(--admin-card-shadow);
        }

        html[data-theme="light"] .teacher-notification-count strong,
        html[data-theme="light"] .teacher-notification-card__body h4,
        html[data-theme="light"] .teacher-notification-empty h4 {
            color: #0f172a;
        }

        html[data-theme="light"] .teacher-notification-pill {
            color: #334155;
            background: rgba(148, 163, 184, 0.14);
        }

        html[data-theme="light"] .teacher-notification-card__time strong {
            color: #1d4ed8;
        }

        @media (max-width: 991.98px) {
            .teacher-notification-hero,
            .teacher-notification-card {
                grid-template-columns: minmax(0, 1fr);
            }

            .teacher-notification-count,
            .teacher-notification-card__time {
                text-align: left;
            }
        }
    </style>
@endsection
