@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    @php
        $currentLocale = app()->getLocale();
        $ui = $content['ui'];
        $teacherApplication = auth('students')->user()?->teacherApplications()?->latest('id')->first();
        $isActiveTeacher = auth('students')->check() && auth('students')->user()?->teacher?->status === 'active';
        $ctaUrl = $isActiveTeacher
            ? route('teacher.dashboard.index')
            : ($teacherApplication
                ? route('teacher.account.status', ['locale' => $currentLocale])
                : route('teacher.account.begin', ['locale' => $currentLocale]));
        $packageCtaUrl = $ctaUrl;
        $teacherLoginUrl = route('teacher.auth.login', ['locale' => $currentLocale]);
    @endphp

    <section class="teacher-landing-shell">
        <section class="teacher-portal-hero py-5">
            <div class="container">
                <div class="teacher-hero-layout" data-reveal="up">
                    <div class="teacher-hero-copy">
                        <span class="teacher-portal-badge">{{ $content['hero']['eyebrow'] }}</span>
                        <h1 class="teacher-portal-title mt-3">{{ $content['hero']['title'] }}</h1>
                        <p class="teacher-portal-desc mt-3">{{ $content['hero']['description'] }}</p>

                        <div class="teacher-hero-chips mt-4">
                            @foreach ($content['hero']['chips'] as $chip)
                                <span class="teacher-hero-chip">{{ $chip }}</span>
                            @endforeach
                        </div>

                        <div class="d-flex flex-wrap gap-3 mt-4">
                            <a href="{{ $ctaUrl }}" class="btn btn-primary btn-lg teacher-landing-btn">
                                {{ $content['hero']['primary'] }}
                            </a>
                            <a href="#teacher-packages" class="btn btn-outline-light btn-lg teacher-landing-btn teacher-landing-btn--ghost">
                                {{ $content['hero']['secondary'] }}
                            </a>
                        </div>

                        <p class="teacher-hero-login-hint mt-3 mb-0">
                            {{ $ui['hero_login_prefix'] }}
                            <a href="{{ $teacherLoginUrl }}">{{ $ui['hero_login_link'] }}</a>
                            {{ $ui['hero_login_suffix'] }}
                        </p>
                    </div>

                    <div class="teacher-hero-side">
                        <div class="teacher-hero-card">
                            <h3>{{ $content['hero']['panel_title'] }}</h3>
                            <ol class="mb-0">
                                @foreach ($content['hero']['panel_items'] as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-4" data-reveal="up">
                    @foreach ($content['stats'] as $stat)
                        <div class="col-md-4">
                            <div class="teacher-stat-surface">
                                <div class="teacher-stat-surface__value">{{ $stat['value'] }}</div>
                                <p class="mb-0">{{ $stat['label'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="py-5 teacher-section teacher-section--soft" data-reveal="up">
            <div class="container">
                <div class="teacher-section-heading text-center mb-5">
                    <span class="teacher-section-kicker">{{ $ui['highlights_kicker'] }}</span>
                    <h2 class="fw-bold mb-3">{{ $ui['highlights_title'] }}</h2>
                    <p class="text-muted mb-0">{{ $ui['highlights_description'] }}</p>
                </div>

                <div class="row g-4">
                    @foreach ($content['highlights'] as $highlight)
                        <div class="col-lg-4">
                            <article class="teacher-info-card">
                                <span class="teacher-info-card__index">0{{ $loop->iteration }}</span>
                                <h3>{{ $highlight['title'] }}</h3>
                                <p class="mb-0">{{ $highlight['description'] }}</p>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="py-5 teacher-section" data-reveal="up">
            <div class="container">
                <div class="row g-4 align-items-start">
                    <div class="col-lg-5">
                        <div class="teacher-stacked-panel">
                            <span class="teacher-section-kicker">{{ $ui['journey_kicker'] }}</span>
                            <h2 class="fw-bold mb-3">{{ $content['journey']['title'] }}</h2>
                            <p class="text-muted mb-0">{{ $content['journey']['description'] }}</p>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="teacher-timeline">
                            @foreach ($content['journey']['steps'] as $step)
                                <article class="teacher-timeline-item">
                                    <div class="teacher-timeline-item__step">{{ $loop->iteration }}</div>
                                    <div>
                                        <h3>{{ $step['title'] }}</h3>
                                        <p class="mb-0">{{ $step['description'] }}</p>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-5 teacher-section teacher-section--accent" data-reveal="up">
            <div class="container">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-6">
                        <div class="teacher-earnings-panel">
                            <span class="teacher-section-kicker teacher-section-kicker--light">{{ $ui['earnings_kicker'] }}</span>
                            <h2 class="fw-bold mb-3">{{ $content['earnings']['title'] }}</h2>
                            <p class="mb-0">{{ $content['earnings']['description'] }}</p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="teacher-bullet-grid">
                            @foreach ($content['earnings']['points'] as $point)
                                <div class="teacher-bullet-card">
                                    <div class="teacher-bullet-card__icon">
                                        <i class="fa-solid fa-check"></i>
                                    </div>
                                    <p class="mb-0">{{ $point }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="teacher-packages" class="py-5 teacher-section" data-reveal="up">
            <div class="container">
                <div class="teacher-section-heading text-center mb-5">
                    <span class="teacher-section-kicker">{{ $ui['packages_kicker'] }}</span>
                    <h2 class="fw-bold mb-3">{{ $content['packages']['title'] }}</h2>
                    <p class="text-muted mb-0">{{ $content['packages']['description'] }}</p>
                </div>

                <div class="row g-4">
                    @foreach ($packages as $package)
                        @php
                            $isFeatured = (bool) $package->is_featured;
                            $supportLabel = $package->support_level_locale ?: ($package->support_level ?: 'basic');
                            $courseLimit = $package->effective_course_limit;
                            $packageName = $package->name_locale ?: $package->name;
                            $packageTagline = $package->tagline_locale ?: '';
                            $packageDescription = $package->description_locale ?: $package->description;
                            $packageBadge = $package->badge_text_locale ?: '';
                        @endphp

                        <div class="col-lg-4 d-flex">
                            <article class="teacher-package-card {{ $isFeatured ? 'is-featured' : '' }}">
                                <div class="teacher-package-card__top">
                                    <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                        <span class="teacher-package-card__tag">{{ strtoupper((string) $package->code) }}</span>
                                        @if ($packageBadge !== '')
                                            <span class="teacher-package-card__badge">{{ $packageBadge }}</span>
                                        @endif
                                    </div>

                                    <h3>{{ $packageName }}</h3>
                                    @if ($packageTagline !== '')
                                        <p class="teacher-package-card__meta">{{ $packageTagline }}</p>
                                    @endif
                                    <div class="teacher-package-card__price">
                                        {{ (float) $package->price > 0 ? money($package->price) : $ui['free_label'] }}
                                    </div>
                                </div>

                                @if (!empty($packageDescription) && $packageDescription !== $packageTagline)
                                    <p class="teacher-package-card__desc">{{ $packageDescription }}</p>
                                @endif

                                <ul class="teacher-package-card__features">
                                    <li>{{ str_replace(':rate', rtrim(rtrim(number_format($package->commission_rate, 2, '.', ''), '0'), '.'), $content['packages']['features']['commission']) }}</li>
                                    <li>
                                        {{ $courseLimit
                                            ? str_replace(':count', $courseLimit, $content['packages']['features']['course_limit'])
                                            : $content['packages']['features']['unlimited'] }}
                                    </li>
                                    <li>{{ $package->priority_review ? $content['packages']['features']['priority_yes'] : $content['packages']['features']['priority_no'] }}</li>
                                    <li>{{ str_replace(':level', $supportLabel, $content['packages']['features']['support']) }}</li>
                                </ul>

                                <a class="btn {{ $isFeatured ? 'btn-light' : 'btn-primary' }} w-100 teacher-package-card__cta"
                                    href="{{ $packageCtaUrl }}">
                                    {{ $content['packages']['cta'] }}
                                </a>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="py-5 teacher-section teacher-section--soft" data-reveal="up">
            <div class="container">
                <div class="teacher-section-heading text-center mb-5">
                    <span class="teacher-section-kicker">{{ $ui['faq_kicker'] }}</span>
                    <h2 class="fw-bold mb-3">{{ $content['faq']['title'] }}</h2>
                </div>

                <div class="teacher-faq-grid">
                    @foreach ($content['faq']['items'] as $faq)
                        <article class="teacher-faq-card">
                            <h3>{{ $faq['question'] }}</h3>
                            <p class="mb-0">{{ $faq['answer'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="py-5 teacher-section" data-reveal="up">
            <div class="container">
                <div class="teacher-final-cta">
                    <div>
                        <span class="teacher-section-kicker teacher-section-kicker--light">{{ $ui['final_kicker'] }}</span>
                        <h2 class="fw-bold mb-3">{{ $content['final']['title'] }}</h2>
                        <p class="mb-0">{{ $content['final']['description'] }}</p>
                    </div>

                    <div class="d-flex flex-wrap gap-3 mt-4">
                        <a href="{{ $ctaUrl }}" class="btn btn-light btn-lg teacher-landing-btn">
                            {{ $content['final']['primary'] }}
                        </a>
                        <a href="#teacher-packages" class="btn btn-outline-light btn-lg teacher-landing-btn teacher-landing-btn--ghost-light">
                            {{ $content['final']['secondary'] }}
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </section>
@endsection

@section('scripts')
    <script>
        (() => {
            const reveals = document.querySelectorAll('[data-reveal]');
            if (!reveals.length || !('IntersectionObserver' in window)) {
                return;
            }

            document.documentElement.classList.add('reveal-ready');

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                });
            }, {
                threshold: 0.14,
            });

            reveals.forEach((item) => observer.observe(item));
        })();
    </script>
@endsection

@section('stylesheets')
    <style>
        .teacher-landing-shell {
            --teacher-bg: #f6f9ff;
            --teacher-surface: #ffffff;
            --teacher-surface-alt: #edf4ff;
            --teacher-border: rgba(37, 99, 235, 0.12);
            --teacher-text: #0f172a;
            --teacher-muted: #5f6f89;
            --teacher-primary: #2563eb;
            --teacher-secondary: #0ea5e9;
            --teacher-accent: #14b8a6;
            --teacher-shadow: 0 24px 54px rgba(15, 23, 42, 0.08);
            background:
                radial-gradient(circle at top, rgba(37, 99, 235, 0.06), transparent 22%),
                linear-gradient(180deg, var(--teacher-bg) 0%, #ffffff 100%);
            color: var(--teacher-text);
        }

        html[data-theme="dark"] .teacher-landing-shell {
            --teacher-bg: #08111f;
            --teacher-surface: #0f172a;
            --teacher-surface-alt: #111c33;
            --teacher-border: rgba(96, 165, 250, 0.2);
            --teacher-text: #e5eefc;
            --teacher-muted: #a9bbd5;
            --teacher-primary: #60a5fa;
            --teacher-secondary: #38bdf8;
            --teacher-accent: #2dd4bf;
            --teacher-shadow: 0 26px 58px rgba(2, 6, 23, 0.36);
            background:
                radial-gradient(circle at top, rgba(56, 189, 248, 0.1), transparent 24%),
                linear-gradient(180deg, #07111f 0%, #08111f 100%);
        }

        .teacher-section {
            position: relative;
        }

        .teacher-landing-shell .text-muted {
            color: var(--teacher-muted) !important;
        }

        .teacher-section--soft {
            background: color-mix(in srgb, var(--teacher-surface-alt) 66%, transparent);
        }

        html[data-theme="dark"] .teacher-section--soft {
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.94) 0%, rgba(17, 28, 51, 0.94) 100%);
        }

        .teacher-section--accent {
            background:
                radial-gradient(circle at left center, rgba(34, 197, 94, 0.12), transparent 32%),
                linear-gradient(135deg, #081528 0%, #102845 56%, #10345c 100%);
            color: #fff;
        }

        .teacher-portal-hero {
            background:
                radial-gradient(circle at top left, rgba(56, 189, 248, 0.24), transparent 32%),
                radial-gradient(circle at right bottom, rgba(20, 184, 166, 0.18), transparent 28%),
                linear-gradient(135deg, #081528 0%, #0f2748 52%, #12325b 100%);
            color: #fff;
            overflow: hidden;
        }

        .teacher-hero-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.25fr) minmax(320px, 0.75fr);
            gap: 2rem;
            align-items: start;
            position: relative;
        }

        .teacher-portal-badge,
        .teacher-section-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            font-size: 0.78rem;
        }

        .teacher-section-kicker {
            background: rgba(37, 99, 235, 0.1);
            color: var(--teacher-primary);
        }

        .teacher-section-kicker--light {
            background: rgba(255, 255, 255, 0.14);
            color: rgba(255, 255, 255, 0.92);
        }

        .teacher-portal-title {
            font-size: clamp(2.2rem, 4.2vw, 4.3rem);
            line-height: 1.04;
            font-weight: 900;
            letter-spacing: -0.03em;
            max-width: 760px;
        }

        .teacher-portal-desc {
            max-width: 700px;
            color: rgba(255, 255, 255, 0.84);
            font-size: 1.08rem;
            line-height: 1.8;
        }

        .teacher-hero-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 0.85rem;
        }

        .teacher-hero-chip,
        .teacher-stat-surface,
        .teacher-bullet-card {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .teacher-hero-chip {
            display: inline-flex;
            align-items: center;
            min-height: 42px;
            padding: 0.55rem 0.95rem;
            border-radius: 999px;
            color: rgba(255, 255, 255, 0.92);
            font-weight: 600;
        }

        .teacher-landing-btn {
            min-width: 190px;
            border-radius: 16px;
            padding-inline: 1.4rem;
            font-weight: 700;
            transition: transform 0.22s ease, box-shadow 0.22s ease, background-color 0.22s ease, color 0.22s ease, border-color 0.22s ease;
        }

        .teacher-landing-btn--ghost,
        .teacher-landing-btn--ghost-light {
            border-color: rgba(255, 255, 255, 0.28);
            color: #fff;
        }

        .teacher-landing-btn--ghost {
            background: rgba(255, 255, 255, 0.02);
        }

        .teacher-landing-btn:hover,
        .teacher-landing-btn:active {
            transform: translateY(-2px);
            box-shadow: 0 16px 30px rgba(37, 99, 235, 0.24);
        }

        .teacher-landing-btn--ghost:hover,
        .teacher-landing-btn--ghost:active {
            background: linear-gradient(180deg, #ffffff 0%, #dbeafe 100%);
            border-color: transparent;
            color: #14365f;
        }

        .teacher-landing-btn--ghost-light:hover {
            color: #fff;
        }

        .teacher-landing-btn--ghost-light:active {
            color: #fff;
        }

        .teacher-landing-shell a:focus-visible,
        .teacher-landing-shell button:focus-visible,
        .teacher-landing-shell [tabindex]:focus-visible {
            outline: none;
            box-shadow:
                0 0 0 3px rgba(8, 17, 31, 0.82),
                0 0 0 6px rgba(96, 165, 250, 0.72);
        }

        html[data-theme="dark"] .teacher-landing-shell a:focus-visible,
        html[data-theme="dark"] .teacher-landing-shell button:focus-visible,
        html[data-theme="dark"] .teacher-landing-shell [tabindex]:focus-visible {
            box-shadow:
                0 0 0 3px rgba(7, 17, 31, 0.96),
                0 0 0 6px rgba(125, 211, 252, 0.82);
        }

        .teacher-hero-login-hint {
            color: rgba(255, 255, 255, 0.76);
            font-size: 0.98rem;
        }

        .teacher-hero-login-hint a {
            color: #fff;
            font-weight: 700;
            text-decoration: underline;
            text-underline-offset: 0.2rem;
        }

        [data-reveal] {
            opacity: 1;
            transform: translateY(0);
        }

        html.reveal-ready [data-reveal] {
            opacity: 0;
            transform: translateY(22px);
            transition: opacity 0.65s ease, transform 0.65s ease;
        }

        html.reveal-ready [data-reveal].is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .teacher-hero-card,
        .teacher-info-card,
        .teacher-stacked-panel,
        .teacher-timeline-item,
        .teacher-package-card,
        .teacher-faq-card {
            background: var(--teacher-surface);
            border: 1px solid var(--teacher-border);
            box-shadow: var(--teacher-shadow);
        }

        .teacher-hero-card {
            padding: 1.75rem;
            border-radius: 28px;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
        }

        html[data-theme="dark"] .teacher-hero-card,
        html[data-theme="dark"] .teacher-stat-surface,
        html[data-theme="dark"] .teacher-bullet-card {
            background: rgba(15, 23, 42, 0.62);
            border-color: rgba(96, 165, 250, 0.16);
        }

        .teacher-stat-surface,
        .teacher-info-card,
        .teacher-package-card,
        .teacher-faq-card,
        .teacher-bullet-card,
        .teacher-final-cta {
            border-radius: 28px;
            padding: 1.6rem;
        }

        .teacher-info-card,
        .teacher-package-card,
        .teacher-faq-card,
        .teacher-bullet-card,
        .teacher-final-cta,
        .teacher-timeline-item,
        .teacher-stacked-panel {
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease, background 0.3s ease;
        }

        .teacher-info-card:hover,
        .teacher-faq-card:hover,
        .teacher-bullet-card:hover,
        .teacher-timeline-item:hover,
        .teacher-stacked-panel:hover {
            transform: translateY(-6px);
            box-shadow: 0 28px 58px rgba(15, 23, 42, 0.16);
        }

        .teacher-info-card:focus-within,
        .teacher-faq-card:focus-within,
        .teacher-bullet-card:focus-within,
        .teacher-timeline-item:focus-within,
        .teacher-stacked-panel:focus-within,
        .teacher-package-card:focus-within,
        .teacher-final-cta:focus-within {
            transform: translateY(-6px);
            border-color: rgba(96, 165, 250, 0.42);
            box-shadow: 0 0 0 3px rgba(8, 17, 31, 0.76), 0 0 0 6px rgba(96, 165, 250, 0.32), 0 28px 58px rgba(15, 23, 42, 0.16);
        }

        html[data-theme="dark"] .teacher-info-card:focus-within,
        html[data-theme="dark"] .teacher-faq-card:focus-within,
        html[data-theme="dark"] .teacher-bullet-card:focus-within,
        html[data-theme="dark"] .teacher-timeline-item:focus-within,
        html[data-theme="dark"] .teacher-stacked-panel:focus-within,
        html[data-theme="dark"] .teacher-package-card:focus-within,
        html[data-theme="dark"] .teacher-final-cta:focus-within {
            border-color: rgba(125, 211, 252, 0.48);
            box-shadow: 0 0 0 3px rgba(7, 17, 31, 0.92), 0 0 0 6px rgba(125, 211, 252, 0.36), 0 32px 64px rgba(2, 6, 23, 0.4);
        }

        .teacher-stat-surface {
            height: 100%;
            color: rgba(255, 255, 255, 0.9);
        }

        .teacher-stat-surface__value {
            margin-bottom: 0.55rem;
            font-size: 1.6rem;
            font-weight: 900;
            color: #fff;
        }

        .teacher-section-heading {
            max-width: 860px;
            margin-inline: auto;
        }

        .teacher-info-card__index {
            display: inline-flex;
            margin-bottom: 1rem;
            font-weight: 800;
            color: var(--teacher-primary);
        }

        .teacher-info-card h3,
        .teacher-timeline-item h3,
        .teacher-package-card h3,
        .teacher-faq-card h3 {
            font-size: 1.2rem;
            font-weight: 800;
            margin-bottom: 0.85rem;
            color: var(--teacher-text);
        }

        .teacher-info-card p,
        .teacher-timeline-item p,
        .teacher-package-card p,
        .teacher-faq-card p,
        .teacher-stacked-panel p {
            color: var(--teacher-muted);
            line-height: 1.75;
        }

        .teacher-stacked-panel {
            border-radius: 30px;
            padding: 1.9rem;
            position: sticky;
            top: 110px;
        }

        .teacher-timeline {
            display: grid;
            gap: 1rem;
        }

        .teacher-timeline-item {
            display: flex;
            gap: 1rem;
            border-radius: 24px;
            padding: 1.2rem 1.25rem;
        }

        .teacher-timeline-item__step,
        .teacher-bullet-card__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #fff;
        }

        .teacher-timeline-item__step {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--teacher-primary), var(--teacher-secondary));
            font-weight: 800;
        }

        .teacher-earnings-panel h2,
        .teacher-final-cta h2 {
            color: #fff;
        }

        .teacher-earnings-panel p,
        .teacher-final-cta p,
        .teacher-bullet-card p {
            color: rgba(255, 255, 255, 0.84);
            line-height: 1.8;
        }

        .teacher-bullet-grid {
            display: grid;
            gap: 1rem;
        }

        .teacher-bullet-card {
            display: flex;
            gap: 0.9rem;
            align-items: flex-start;
        }

        .teacher-bullet-card__icon {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.12);
        }

        .teacher-package-card.is-featured,
        .teacher-final-cta {
            background:
                radial-gradient(circle at top right, rgba(56, 189, 248, 0.18), transparent 26%),
                linear-gradient(180deg, #0f172a 0%, #11294a 100%);
            color: #fff;
            border-color: rgba(96, 165, 250, 0.18);
        }

        .teacher-package-card {
            display: flex;
            flex-direction: column;
            width: 100%;
            min-height: 100%;
            position: relative;
            overflow: hidden;
        }

        .teacher-package-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(96, 165, 250, 0.08), transparent 42%);
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .teacher-package-card:hover {
            transform: translateY(-10px);
            border-color: rgba(96, 165, 250, 0.36);
            box-shadow: 0 32px 64px rgba(2, 6, 23, 0.22);
        }

        .teacher-package-card:hover::before {
            opacity: 1;
        }

        .teacher-package-card__top,
        .teacher-package-card__desc {
            position: relative;
            z-index: 1;
        }

        .teacher-package-card__tag,
        .teacher-package-card__badge {
            display: inline-flex;
            align-items: center;
            padding: 0.45rem 0.75rem;
            border-radius: 999px;
            font-size: 0.74rem;
            font-weight: 800;
            letter-spacing: 0.05em;
        }

        .teacher-package-card__tag {
            background: rgba(37, 99, 235, 0.12);
            color: var(--teacher-primary);
        }

        .teacher-package-card__badge {
            background: rgba(20, 184, 166, 0.12);
            color: #0f766e;
        }

        html[data-theme="dark"] .teacher-package-card__badge {
            background: rgba(45, 212, 191, 0.16);
            color: #7cead9;
        }

        .teacher-package-card.is-featured .teacher-package-card__tag,
        .teacher-package-card.is-featured .teacher-package-card__badge,
        .teacher-package-card.is-featured .teacher-package-card__price,
        .teacher-package-card.is-featured h3,
        .teacher-package-card.is-featured .teacher-package-card__desc,
        .teacher-package-card.is-featured .teacher-package-card__meta,
        .teacher-package-card.is-featured .teacher-package-card__features {
            color: #fff;
        }

        .teacher-package-card__price {
            font-size: 1.85rem;
            font-weight: 900;
            color: var(--teacher-text);
        }

        .teacher-package-card__features {
            margin: 1.2rem 0 1.5rem;
            padding-left: 1.15rem;
            color: var(--teacher-text);
            position: relative;
            z-index: 1;
            flex-grow: 1;
        }

        .teacher-package-card__features li + li {
            margin-top: 0.35rem;
        }

        .teacher-package-card__cta {
            position: relative;
            z-index: 1;
            margin-top: auto;
            border-radius: 14px;
            font-weight: 800;
            transition: transform 0.25s ease, box-shadow 0.25s ease, background-color 0.25s ease, color 0.25s ease;
        }

        .teacher-package-card:hover .teacher-package-card__cta {
            transform: translateY(-2px);
            box-shadow: 0 16px 32px rgba(37, 99, 235, 0.28);
        }

        .teacher-package-card__cta:active {
            transform: translateY(0);
        }

        .teacher-package-card.is-featured .teacher-package-card__cta {
            color: #12325b;
            background: #f8fbff;
            border-color: transparent;
        }

        .teacher-package-card.is-featured:hover .teacher-package-card__cta {
            background: #ffffff;
            color: #0f2748;
            box-shadow: 0 18px 34px rgba(148, 197, 255, 0.22);
        }

        .teacher-final-cta .teacher-landing-btn.btn-light {
            background: linear-gradient(180deg, #ffffff 0%, #e8f1ff 100%);
            color: #163760;
            border-color: transparent;
            box-shadow: 0 16px 30px rgba(8, 17, 31, 0.2);
        }

        .teacher-final-cta .teacher-landing-btn.btn-light:hover,
        .teacher-final-cta .teacher-landing-btn.btn-light:active {
            background: linear-gradient(180deg, #ffffff 0%, #dbeafe 100%);
            color: #0f2748;
        }

        .teacher-final-cta .teacher-landing-btn--ghost-light {
            border-color: rgba(191, 219, 254, 0.34);
            color: #dcecff;
            background: rgba(15, 23, 42, 0.08);
        }

        .teacher-final-cta .teacher-landing-btn--ghost-light:hover,
        .teacher-final-cta .teacher-landing-btn--ghost-light:active {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(191, 219, 254, 0.54);
            color: #ffffff;
        }

        .teacher-final-cta .teacher-landing-btn--ghost-light:focus-visible,
        .teacher-final-cta .teacher-landing-btn.btn-light:focus-visible,
        .teacher-package-card__cta:focus-visible,
        .teacher-landing-btn--ghost:focus-visible {
            color: inherit;
        }

        html[data-theme="dark"] .teacher-final-cta .teacher-landing-btn.btn-light {
            background: linear-gradient(180deg, #f8fbff 0%, #dbeafe 100%);
            color: #12325b;
            box-shadow: 0 18px 34px rgba(2, 6, 23, 0.34);
        }

        html[data-theme="dark"] .teacher-final-cta .teacher-landing-btn.btn-light:hover,
        html[data-theme="dark"] .teacher-final-cta .teacher-landing-btn.btn-light:active {
            background: linear-gradient(180deg, #ffffff 0%, #eaf3ff 100%);
            color: #0f2748;
        }

        html[data-theme="dark"] .teacher-package-card:not(.is-featured) {
            background:
                linear-gradient(180deg, rgba(15, 23, 42, 0.98) 0%, rgba(17, 28, 51, 0.98) 100%);
            border-color: rgba(96, 165, 250, 0.18);
        }

        html[data-theme="dark"] .teacher-package-card:not(.is-featured):hover {
            border-color: rgba(125, 211, 252, 0.38);
            box-shadow: 0 34px 68px rgba(2, 6, 23, 0.42);
        }

        html[data-theme="dark"] .teacher-package-card:not(.is-featured) h3,
        html[data-theme="dark"] .teacher-package-card:not(.is-featured) .teacher-package-card__price,
        html[data-theme="dark"] .teacher-package-card:not(.is-featured) .teacher-package-card__features {
            color: #e7eefc;
        }

        html[data-theme="dark"] .teacher-package-card:not(.is-featured) .teacher-package-card__meta,
        html[data-theme="dark"] .teacher-package-card:not(.is-featured) .teacher-package-card__desc {
            color: #a9bbd5;
        }

        html[data-theme="dark"] .teacher-package-card__tag {
            background: rgba(96, 165, 250, 0.16);
            color: #8fc3ff;
        }

        .teacher-faq-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        @media (max-width: 991.98px) {
            .teacher-hero-layout {
                grid-template-columns: 1fr;
            }

            .teacher-stacked-panel,
            .teacher-faq-grid {
                position: static;
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767.98px) {
            .teacher-portal-title {
                font-size: 2.45rem;
            }

            .teacher-landing-btn {
                width: 100%;
            }

            .teacher-stat-surface,
            .teacher-info-card,
            .teacher-package-card,
            .teacher-faq-card,
            .teacher-bullet-card,
            .teacher-stacked-panel,
            .teacher-final-cta {
                padding: 1.25rem;
                border-radius: 22px;
            }
        }
    </style>
@endsection
