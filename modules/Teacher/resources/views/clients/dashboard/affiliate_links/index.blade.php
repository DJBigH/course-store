@extends('layouts.teacher')

@php
    $formatTrackedMoney = function ($amount) {
        $amount = (float) $amount;

        if ($amount > 0) {
            return moneyLocale($amount);
        }

        return match (app()->getLocale()) {
            'en' => '$0.00',
            'ko' => '₩0',
            'ja' => '¥0',
            'zh' => 'CN¥0.00',
            default => '0đ',
        };
    };
@endphp

@section('content')
    <div class="teacher-panel teacher-affiliate-shell">
        <div class="teacher-affiliate-hero">
            <div>
                <span class="teacher-affiliate-kicker">{{ __('teacher::dashboard.nav.affiliate_links') }}</span>
                <h3 class="teacher-affiliate-title">{{ __('teacher::dashboard.affiliate_links.title') }}</h3>
                <p class="teacher-affiliate-desc mb-0">{{ __('teacher::dashboard.affiliate_links.description') }}</p>
            </div>
            <a href="{{ route('teacher.dashboard.affiliate-links.create') }}" class="btn btn-primary btn-lg">
                {{ __('teacher::dashboard.affiliate_links.create') }}
            </a>
        </div>

        @if (session('msg_success'))
            <div class="alert alert-success border-0">{{ session('msg_success') }}</div>
        @endif

        <div class="teacher-affiliate-stats">
            <article class="teacher-affiliate-stat-card">
                <span>{{ __('teacher::dashboard.affiliate_links.stats.total_links') }}</span>
                <strong>{{ number_format((int) ($stats['total_links'] ?? 0)) }}</strong>
            </article>
            <article class="teacher-affiliate-stat-card">
                <span>{{ __('teacher::dashboard.affiliate_links.stats.total_clicks') }}</span>
                <strong>{{ number_format((int) ($stats['total_clicks'] ?? 0)) }}</strong>
            </article>
            <article class="teacher-affiliate-stat-card">
                <span>{{ __('teacher::dashboard.affiliate_links.stats.landing_links') }}</span>
                <strong>{{ number_format((int) ($stats['landing_links'] ?? 0)) }}</strong>
            </article>
            <article class="teacher-affiliate-stat-card">
                <span>{{ __('teacher::dashboard.affiliate_links.stats.paid_orders') }}</span>
                <strong>{{ number_format((int) ($stats['paid_orders'] ?? 0)) }}</strong>
            </article>
            <article class="teacher-affiliate-stat-card">
                <span>{{ __('teacher::dashboard.affiliate_links.stats.paid_revenue') }}</span>
                <strong>{{ $formatTrackedMoney((float) ($stats['paid_revenue'] ?? 0)) }}</strong>
            </article>
            <article class="teacher-affiliate-stat-card">
                <span>{{ __('teacher::dashboard.affiliate_links.stats.conversion_rate') }}</span>
                <strong>{{ number_format((float) ($stats['conversion_rate'] ?? 0), 2) }}%</strong>
            </article>
        </div>

        <div class="teacher-affiliate-overview-grid">
            <section class="teacher-affiliate-overview-card">
                <div class="teacher-affiliate-overview-card__head">
                    <div>
                        <h4>{{ __('teacher::dashboard.affiliate_links.sections.top_links') }}</h4>
                        <p class="mb-0">{{ __('teacher::dashboard.affiliate_links.sections.top_links_desc') }}</p>
                    </div>
                </div>
                <div class="teacher-affiliate-top-list">
                    @forelse ($topLinks as $index => $topLink)
                        @php
                            $topLabel = __('teacher::dashboard.affiliate_links.target_types.' . $topLink->target_type);
                        @endphp
                        <article class="teacher-affiliate-top-item">
                            <div class="teacher-affiliate-top-item__rank">#{{ $index + 1 }}</div>
                            <div class="teacher-affiliate-top-item__body">
                                <strong>{{ $topLink->name }}</strong>
                                <span>{{ $topLabel }}</span>
                            </div>
                            <div class="teacher-affiliate-top-item__metrics">
                                <span>{{ $formatTrackedMoney((float) ($topLink->paid_revenue ?? 0)) }}</span>
                                <small>{{ number_format((int) ($topLink->paid_orders_count ?? 0)) }} {{ __('teacher::dashboard.affiliate_links.labels.paid_orders') }}</small>
                            </div>
                        </article>
                    @empty
                        <div class="teacher-affiliate-top-empty">{{ __('teacher::dashboard.common.empty') }}</div>
                    @endforelse
                </div>
            </section>

            <section class="teacher-affiliate-overview-card">
                <div class="teacher-affiliate-overview-card__head">
                    <div>
                        <h4>{{ __('teacher::dashboard.affiliate_links.sections.breakdown') }}</h4>
                        <p class="mb-0">{{ __('teacher::dashboard.affiliate_links.sections.breakdown_desc') }}</p>
                    </div>
                </div>
                <div class="teacher-affiliate-breakdown-list">
                    @foreach ($breakdown as $row)
                        <article class="teacher-affiliate-breakdown-item">
                            <div class="teacher-affiliate-breakdown-item__title">
                                <strong>{{ __('teacher::dashboard.affiliate_links.target_types.' . $row['target_type']) }}</strong>
                                <span>{{ number_format((int) $row['links_count']) }} {{ __('teacher::dashboard.affiliate_links.labels.links_count') }}</span>
                            </div>
                            <div class="teacher-affiliate-breakdown-item__metrics">
                                <span>{{ number_format((int) $row['clicks']) }} click</span>
                                <span>{{ number_format((int) $row['paid_orders']) }} {{ __('teacher::dashboard.affiliate_links.labels.paid_orders') }}</span>
                                <span>{{ $formatTrackedMoney((float) $row['paid_revenue']) }}</span>
                                <span>{{ number_format((float) $row['conversion_rate'], 2) }}%</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        </div>

        <div class="teacher-affiliate-grid">
            @forelse ($links as $link)
                @php
                    $targetLabel = __('teacher::dashboard.affiliate_links.target_types.' . $link->target_type);
                    $targetName = match ($link->target_type) {
                        'course' => $link->course?->name_locale ?: $link->course?->name,
                        'bundle' => $link->bundle?->name,
                        default => $link->teacher?->display_name ?: $link->teacher?->name,
                    };
                    $publicUrl = $link->public_url ?? null;
                @endphp
                <article class="teacher-affiliate-card">
                    <div class="teacher-affiliate-card__head">
                        <div>
                            <span class="teacher-affiliate-card__status {{ $link->status ? 'is-active' : 'is-paused' }}">
                                {{ $link->status ? __('teacher::dashboard.common.status_active') : __('teacher::dashboard.courses.status.draft') }}
                            </span>
                            <h4>{{ $link->name }}</h4>
                            <p class="mb-0">{{ $targetLabel }} @if ($targetName) • {{ $targetName }} @endif</p>
                        </div>
                        <div class="teacher-affiliate-card__meta">
                            <span>{{ __('teacher::dashboard.affiliate_links.fields.code') }}</span>
                            <strong>{{ $link->code }}</strong>
                        </div>
                    </div>

                    <div class="teacher-affiliate-card__stats">
                        <div class="teacher-affiliate-card__info">
                            <span>{{ __('teacher::dashboard.affiliate_links.fields.clicks') }}</span>
                            <strong>{{ number_format((int) $link->clicks_count) }}</strong>
                        </div>
                        <div class="teacher-affiliate-card__info">
                            <span>{{ __('teacher::dashboard.affiliate_links.fields.last_clicked_at') }}</span>
                            <strong>{{ $link->last_clicked_at ? $link->last_clicked_at->format('d/m/Y H:i') : __('teacher::dashboard.common.empty') }}</strong>
                        </div>
                        <div class="teacher-affiliate-card__info">
                            <span>{{ __('teacher::dashboard.affiliate_links.labels.paid_orders') }}</span>
                            <strong>{{ number_format((int) ($link->paid_orders_count ?? 0)) }}</strong>
                        </div>
                        <div class="teacher-affiliate-card__info">
                            <span>{{ __('teacher::dashboard.affiliate_links.labels.paid_revenue') }}</span>
                            <strong>{{ $formatTrackedMoney((float) ($link->paid_revenue ?? 0)) }}</strong>
                        </div>
                        <div class="teacher-affiliate-card__info">
                            <span>{{ __('teacher::dashboard.affiliate_links.labels.conversion_rate') }}</span>
                            <strong>{{ number_format((float) ($link->conversion_rate ?? 0), 2) }}%</strong>
                        </div>
                    </div>

                    <div class="teacher-affiliate-card__url">
                        <label class="form-label">{{ __('teacher::dashboard.affiliate_links.fields.public_url') }}</label>
                        <div class="teacher-affiliate-card__url-row">
                            <input type="text" class="form-control"
                                value="{{ $publicUrl ?: __('teacher::dashboard.affiliate_links.unavailable_target') }}" readonly>
                            <button type="button" class="btn btn-outline-primary affiliate-copy-btn"
                                data-copy-value="{{ $publicUrl }}" {{ $publicUrl ? '' : 'disabled' }}>
                                {{ __('teacher::dashboard.affiliate_links.actions.copy') }}
                            </button>
                        </div>
                    </div>

                    <div class="teacher-affiliate-card__actions">
                        @if ($publicUrl)
                            <a href="{{ $publicUrl }}" target="_blank" class="btn btn-outline-secondary">
                                {{ __('teacher::dashboard.affiliate_links.actions.open') }}
                            </a>
                        @endif
                        <a href="{{ route('teacher.dashboard.affiliate-links.edit', $link->id) }}" class="btn btn-outline-primary">
                            {{ __('teacher::dashboard.affiliate_links.actions.edit') }}
                        </a>
                        <form action="{{ route('teacher.dashboard.affiliate-links.delete', $link->id) }}" method="POST"
                            onsubmit="return confirm(@js(__('teacher::dashboard.affiliate_links.confirm_delete')));">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">
                                {{ __('teacher::dashboard.affiliate_links.actions.delete') }}
                            </button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="teacher-affiliate-empty">
                    <div class="teacher-affiliate-empty__icon"><i class="fas fa-link"></i></div>
                    <h4>{{ __('teacher::dashboard.affiliate_links.empty') }}</h4>
                    <p class="mb-0">{{ __('teacher::dashboard.affiliate_links.empty_description') }}</p>
                </div>
            @endforelse
        </div>

        @if ($links->hasPages())
            <div class="mt-4">{{ $links->links() }}</div>
        @endif
    </div>
@endsection

@section('stylesheets')
    <style>
        .teacher-affiliate-shell { background: radial-gradient(circle at top right, rgba(14, 165, 233, 0.1), transparent 28%), linear-gradient(180deg, rgba(17, 24, 39, 0.94) 0%, rgba(15, 23, 42, 0.98) 100%); }
        .teacher-affiliate-hero { display: flex; justify-content: space-between; gap: 1rem; align-items: flex-start; margin-bottom: 1.5rem; }
        .teacher-affiliate-kicker { display: inline-flex; padding: 0.45rem 0.8rem; border-radius: 999px; background: rgba(14, 165, 233, 0.16); color: #bae6fd; font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; }
        .teacher-affiliate-title { margin-top: 1rem; margin-bottom: 0.55rem; font-size: clamp(2rem, 3vw, 2.7rem); font-weight: 900; color: #f8fbff; }
        .teacher-affiliate-desc { max-width: 760px; color: #a9bbd5; line-height: 1.75; }
        .teacher-affiliate-stats { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 1rem; margin-bottom: 1.25rem; }
        .teacher-affiliate-overview-grid { display: grid; grid-template-columns: 1.1fr 1fr; gap: 1rem; margin-bottom: 1.25rem; }
        .teacher-affiliate-overview-card { background: rgba(18, 28, 50, 0.72); border: 1px solid rgba(56, 189, 248, 0.16); border-radius: 22px; box-shadow: 0 18px 42px rgba(2, 6, 23, 0.18); padding: 1.2rem; }
        .teacher-affiliate-overview-card__head h4 { color: #f8fbff; font-weight: 800; margin-bottom: 0.35rem; }
        .teacher-affiliate-overview-card__head p { color: #94a3b8; }
        .teacher-affiliate-top-list, .teacher-affiliate-breakdown-list { display: grid; gap: 0.85rem; }
        .teacher-affiliate-top-item, .teacher-affiliate-breakdown-item { display: flex; justify-content: space-between; gap: 1rem; align-items: center; padding: 0.95rem 1rem; border-radius: 18px; background: rgba(15, 23, 42, 0.52); border: 1px solid rgba(56, 189, 248, 0.12); }
        .teacher-affiliate-top-item__rank { width: 44px; height: 44px; border-radius: 14px; display: grid; place-items: center; background: rgba(37, 99, 235, 0.18); color: #dbeafe; font-weight: 900; flex-shrink: 0; }
        .teacher-affiliate-top-item__body, .teacher-affiliate-breakdown-item__title { display: flex; flex-direction: column; gap: 0.22rem; min-width: 0; }
        .teacher-affiliate-top-item__body strong, .teacher-affiliate-breakdown-item__title strong { color: #f8fbff; }
        .teacher-affiliate-top-item__body span, .teacher-affiliate-breakdown-item__title span, .teacher-affiliate-top-item__metrics small { color: #94a3b8; }
        .teacher-affiliate-top-item__metrics { display: flex; flex-direction: column; align-items: flex-end; gap: 0.22rem; }
        .teacher-affiliate-top-item__metrics span, .teacher-affiliate-breakdown-item__metrics span { color: #f8fbff; font-weight: 700; }
        .teacher-affiliate-breakdown-item__metrics { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 0.8rem; }
        .teacher-affiliate-top-empty { color: #94a3b8; padding: 0.4rem 0; }
        .teacher-affiliate-stat-card, .teacher-affiliate-card, .teacher-affiliate-empty { background: rgba(18, 28, 50, 0.72); border: 1px solid rgba(56, 189, 248, 0.16); border-radius: 22px; box-shadow: 0 18px 42px rgba(2, 6, 23, 0.18); }
        .teacher-affiliate-stat-card { padding: 1rem 1.15rem; display: flex; flex-direction: column; gap: 0.45rem; }
        .teacher-affiliate-stat-card span { color: #93c5fd; font-size: 0.85rem; }
        .teacher-affiliate-stat-card strong { color: #f8fbff; font-size: 1.8rem; font-weight: 900; }
        .teacher-affiliate-grid { display: grid; gap: 1rem; }
        .teacher-affiliate-card { padding: 1.25rem; }
        .teacher-affiliate-card__head { display: flex; justify-content: space-between; gap: 1rem; align-items: flex-start; }
        .teacher-affiliate-card__head h4 { margin: 0.6rem 0 0.4rem; color: #f8fbff; font-weight: 800; }
        .teacher-affiliate-card__head p, .teacher-affiliate-card__meta span, .teacher-affiliate-card__info span { color: #94a3b8; }
        .teacher-affiliate-card__meta { text-align: right; }
        .teacher-affiliate-card__meta strong, .teacher-affiliate-card__info strong { color: #f8fbff; display: block; }
        .teacher-affiliate-card__status { display: inline-flex; padding: 0.3rem 0.72rem; border-radius: 999px; font-size: 0.75rem; font-weight: 800; }
        .teacher-affiliate-card__status.is-active { background: rgba(34, 197, 94, 0.18); color: #bbf7d0; }
        .teacher-affiliate-card__status.is-paused { background: rgba(148, 163, 184, 0.18); color: #cbd5e1; }
        .teacher-affiliate-card__stats { margin: 1rem 0; display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 1rem; }
        .teacher-affiliate-card__info { padding: 0.95rem 1rem; border-radius: 18px; background: rgba(15, 23, 42, 0.52); border: 1px solid rgba(56, 189, 248, 0.12); }
        .teacher-affiliate-card__url .form-label { color: #dce9ff; font-weight: 700; }
        .teacher-affiliate-card__url-row { display: flex; gap: 1rem; }
        .teacher-affiliate-card__url-row .form-control { background: rgba(15, 23, 42, 0.72); border-color: rgba(56, 189, 248, 0.18); color: #f8fbff; }
        .teacher-affiliate-card__actions { margin-top: 1rem; display: flex; gap: 1rem; flex-wrap: wrap; }
        .teacher-affiliate-empty { text-align: center; padding: 2.4rem 1.5rem; }
        .teacher-affiliate-empty__icon { width: 72px; height: 72px; margin: 0 auto 1rem; border-radius: 22px; display: grid; place-items: center; background: linear-gradient(135deg, rgba(56, 189, 248, 0.24), rgba(59, 130, 246, 0.24)); color: #ecfeff; font-size: 1.8rem; }
        .teacher-affiliate-empty h4 { color: #f8fbff; margin-bottom: 0.5rem; }
        .teacher-affiliate-empty p { color: #a9bbd5; max-width: 560px; margin: 0 auto; }
        html[data-theme="light"] .teacher-affiliate-shell { background: radial-gradient(circle at top right, rgba(14, 165, 233, 0.08), transparent 28%), linear-gradient(180deg, rgba(248, 250, 252, 0.96) 0%, rgba(241, 245, 249, 0.98) 100%); }
        html[data-theme="light"] .teacher-affiliate-stat-card, html[data-theme="light"] .teacher-affiliate-card, html[data-theme="light"] .teacher-affiliate-empty, html[data-theme="light"] .teacher-affiliate-overview-card { background: var(--admin-surface); border-color: var(--admin-border); box-shadow: var(--admin-card-shadow); }
        html[data-theme="light"] .teacher-affiliate-title, html[data-theme="light"] .teacher-affiliate-card__head h4, html[data-theme="light"] .teacher-affiliate-card__meta strong, html[data-theme="light"] .teacher-affiliate-card__info strong, html[data-theme="light"] .teacher-affiliate-stat-card strong, html[data-theme="light"] .teacher-affiliate-empty h4, html[data-theme="light"] .teacher-affiliate-overview-card__head h4, html[data-theme="light"] .teacher-affiliate-top-item__body strong, html[data-theme="light"] .teacher-affiliate-breakdown-item__title strong, html[data-theme="light"] .teacher-affiliate-top-item__metrics span, html[data-theme="light"] .teacher-affiliate-breakdown-item__metrics span { color: #0f172a; }
        html[data-theme="light"] .teacher-affiliate-desc, html[data-theme="light"] .teacher-affiliate-card__head p, html[data-theme="light"] .teacher-affiliate-card__meta span, html[data-theme="light"] .teacher-affiliate-card__info span, html[data-theme="light"] .teacher-affiliate-empty p, html[data-theme="light"] .teacher-affiliate-overview-card__head p, html[data-theme="light"] .teacher-affiliate-top-item__body span, html[data-theme="light"] .teacher-affiliate-breakdown-item__title span, html[data-theme="light"] .teacher-affiliate-top-item__metrics small, html[data-theme="light"] .teacher-affiliate-top-empty { color: #475569; }
        html[data-theme="light"] .teacher-affiliate-card__info { background: #fff; border-color: var(--admin-border); }
        html[data-theme="light"] .teacher-affiliate-top-item, html[data-theme="light"] .teacher-affiliate-breakdown-item { background: #fff; border-color: var(--admin-border); }
        html[data-theme="light"] .teacher-affiliate-card__url-row .form-control { background: #fff; border-color: var(--admin-border); color: #0f172a; }
        @media (max-width: 991.98px) {
            .teacher-affiliate-hero, .teacher-affiliate-card__head { flex-direction: column; align-items: flex-start; }
            .teacher-affiliate-stats { grid-template-columns: 1fr; }
            .teacher-affiliate-overview-grid { grid-template-columns: 1fr; }
            .teacher-affiliate-card__meta { text-align: left; }
        }
        @media (max-width: 767.98px) {
            .teacher-affiliate-card__stats { grid-template-columns: 1fr; }
            .teacher-affiliate-top-item, .teacher-affiliate-breakdown-item { flex-direction: column; align-items: flex-start; }
            .teacher-affiliate-top-item__metrics, .teacher-affiliate-breakdown-item__metrics { align-items: flex-start; justify-content: flex-start; }
            .teacher-affiliate-card__url-row { flex-direction: column; }
        }
    </style>
@endsection

@section('scripts')
    <script>
        document.addEventListener('click', async function (event) {
            const button = event.target.closest('.affiliate-copy-btn');
            if (!button) {
                return;
            }

            const value = button.getAttribute('data-copy-value');
            if (!value) {
                return;
            }

            try {
                await navigator.clipboard.writeText(value);
                const originalText = button.textContent;
                button.textContent = '{{ __('teacher::dashboard.profile.actions.copied') }}';
                setTimeout(() => {
                    button.textContent = originalText;
                }, 1400);
            } catch (error) {
                window.prompt('Copy link:', value);
            }
        });
    </script>
@endsection
