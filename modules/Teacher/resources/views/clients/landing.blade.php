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
        <section class="teacher-portal-hero">
            <div class="container">
                <div class="teacher-hero-layout" data-reveal="up">
                    <div class="teacher-hero-copy">
                        <span class="teacher-portal-badge">{{ $content['hero']['eyebrow'] }}</span>
                        <h1 class="teacher-portal-title mt-3">{{ $content['hero']['title'] }}</h1>
                        <p class="teacher-portal-desc mt-3">{{ $content['hero']['description'] }}</p>

                        <div class="d-flex flex-wrap gap-2 mt-4">
                            @foreach ($content['hero']['chips'] as $chip)
                                <span class="teacher-hero-chip">
                                    <i class="fa-solid fa-check-circle text-accent"></i>
                                    {{ $chip }}
                                </span>
                            @endforeach
                        </div>

                        <div class="d-flex flex-wrap gap-3 mt-5">
                            <a href="{{ $ctaUrl }}" class="btn btn-primary btn-lg teacher-landing-btn px-5">
                                <i class="fa-solid fa-paper-plane me-2"></i>
                                {{ $content['hero']['primary'] }}
                            </a>
                            <a href="#teacher-packages" class="btn btn-outline-light btn-lg teacher-landing-btn teacher-landing-btn--ghost px-4">
                                <i class="fa-solid fa-layer-group me-2"></i>
                                {{ $content['hero']['secondary'] }}
                            </a>
                        </div>

                        <p class="teacher-hero-login-hint mt-4 mb-0">
                            {{ $ui['hero_login_prefix'] }}
                            <a href="{{ $teacherLoginUrl }}">{{ $ui['hero_login_link'] }}</a>
                            {{ $ui['hero_login_suffix'] }}
                        </p>
                    </div>

                    <div class="teacher-hero-side">
                        <div class="teacher-hero-glass-card">
                            <div class="glass-card-header">
                                <div class="glass-dot dot-red"></div>
                                <div class="glass-dot dot-yellow"></div>
                                <div class="glass-dot dot-green"></div>
                                <span class="ms-2 small fw-bold opacity-75">{{ $content['hero']['panel_title'] }}</span>
                            </div>
                            <div class="glass-card-body mt-3">
                                <ul class="list-unstyled mb-0 hero-panel-list">
                                    @foreach ($content['hero']['panel_items'] as $item)
                                        <li class="mb-4 d-flex gap-3 align-items-start">
                                            <div class="step-indicator">
                                                <i class="fa-solid fa-{{ ['user-plus', 'tags', 'hourglass-half'][$loop->index] }} small"></i>
                                            </div>
                                            <span class="opacity-90 fw-medium">{{ $item }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mt-5" data-reveal="up">
                    @foreach ($content['stats'] as $stat)
                        <div class="col-md-4">
                            <div class="teacher-stat-card">
                                <div class="teacher-stat-card__value">{{ $stat['value'] }}</div>
                                <p class="mb-0 opacity-75">{{ $stat['label'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div class="hero-bg-shapes">
                <div class="shape shape-1"></div>
                <div class="shape shape-2"></div>
            </div>
        </section>

        <section class="py-5 teacher-section teacher-section--soft" data-reveal="up">
            <div class="container py-lg-4">
                <div class="teacher-section-heading text-center mb-5">
                    <span class="teacher-section-kicker">{{ $ui['highlights_kicker'] }}</span>
                    <h2 class="display-5 fw-bold mb-3">{{ $ui['highlights_title'] }}</h2>
                    <p class="lead opacity-75 mx-auto" style="max-width: 700px;">{{ $ui['highlights_description'] }}</p>
                </div>

                <div class="row g-4">
                    @foreach ($content['highlights'] as $highlight)
                        <div class="col-lg-4">
                            <article class="teacher-feature-card">
                                <div class="teacher-feature-card__icon">
                                    <i class="fa-solid fa-{{ ['rocket', 'chart-line', 'shield-halved'][$loop->index] }}"></i>
                                </div>
                                <h3 class="h4 fw-bold mt-4 mb-3">{{ $highlight['title'] }}</h3>
                                <p class="mb-0 opacity-75">{{ $highlight['description'] }}</p>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="py-5 teacher-section overflow-hidden" data-reveal="up">
            <div class="container py-lg-5">
                <div class="row g-5 align-items-center">
                    <div class="col-lg-5">
                        <div class="teacher-journey-intro">
                            <span class="teacher-section-kicker">{{ $ui['journey_kicker'] }}</span>
                            <h2 class="display-6 fw-bold mb-4">{{ $content['journey']['title'] }}</h2>
                            <p class="lead opacity-75 mb-0">{{ $content['journey']['description'] }}</p>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="teacher-steps-grid">
                            @foreach ($content['journey']['steps'] as $step)
                                <article class="teacher-step-item">
                                    <div class="teacher-step-item__count">{{ $loop->iteration }}</div>
                                    <div class="teacher-step-item__content">
                                        <h3 class="h5 fw-bold mb-2">{{ $step['title'] }}</h3>
                                        <p class="mb-0 small opacity-75">{{ $step['description'] }}</p>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-5 teacher-section" data-reveal="up">
            <div class="container py-lg-4">
                <div class="row g-4 align-items-center">
                        <div class="teacher-earnings-panel" data-reveal="up">
                            <div class="row g-5 align-items-center position-relative z-2">
                                <div class="col-lg-6">
                                    <span class="teacher-section-kicker mb-3">{{ $ui['earnings_kicker'] }}</span>
                                    <h2 class="display-6 fw-bold mb-4">{{ $content['earnings']['title'] }}</h2>
                                    <p class="lead mb-0 opacity-75">{{ $content['earnings']['description'] }}</p>
                                </div>
                                <div class="col-lg-6">
                                    <div class="teacher-points-list d-flex flex-column gap-3">
                                        @foreach ($content['earnings']['points'] as $point)
                                            <div class="teacher-point-card">
                                                <div class="teacher-point-card__bullet">
                                                    <i class="fa-solid fa-check"></i>
                                                </div>
                                                <p class="mb-0 fw-medium">{{ $point }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            
                            <div class="earnings-decor">
                                <div class="e-decor-1"></div>
                                <div class="e-decor-2"></div>
                            </div>
                        </div>
                </div>
            </div>
        </section>

        <section id="teacher-packages" class="py-5 teacher-section" data-reveal="up">
            <div class="container py-lg-5">
                <div class="teacher-section-heading text-center mb-5">
                    <span class="teacher-section-kicker">{{ $ui['packages_kicker'] }}</span>
                    <h2 class="display-5 fw-bold mb-3">{{ $content['packages']['title'] }}</h2>
                    <p class="lead opacity-75 mx-auto" style="max-width: 700px;">{{ $content['packages']['description'] }}</p>
                </div>

                <div class="teacher-package-tabs-wrap mb-5">
                    <ul class="nav nav-pills teacher-package-nav justify-content-center" id="packageTab" role="tablist">
                        @foreach ($groupedPackages as $category => $pkgs)
                            @php $catSlug = \Illuminate\Support\Str::slug($category); @endphp
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $loop->first ? 'active' : '' }}" 
                                    id="{{ $catSlug }}-tab" 
                                    data-bs-toggle="pill" 
                                    data-bs-target="#{{ $catSlug }}-content" 
                                    type="button" role="tab">
                                    {{ $content['categories'][$category] ?? ucfirst($category) }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="tab-content" id="packageTabContent">
                    @foreach ($groupedPackages as $category => $pkgs)
                        @php $catSlug = \Illuminate\Support\Str::slug($category); @endphp
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" 
                            id="{{ $catSlug }}-content" 
                            role="tabpanel">
                            <div class="row g-4 justify-content-center">
                                @foreach ($pkgs as $package)
                                    @php
                                        $isFeatured = (bool) $package->is_featured;
                                        $supportLabel = $package->support_level_locale ?: ($package->support_level ?: 'basic');
                                        $courseLimit = $package->effective_course_limit;
                                        $packageNameKey = 'teacher::landing.package_titles.' . strtolower((string)$package->code);
                                        $translatedName = __($packageNameKey);
                                        $packageName = str_contains($translatedName, 'teacher::landing') 
                                            ? ($package->name_locale ?: $package->name)
                                            : $translatedName;
                                        $packageTagline = $package->tagline_locale ?: '';
                                        $packageDescription = $package->description_locale ?: $package->description;
                                        $packageBadge = $package->badge_text_locale ?: '';
                                    @endphp

                                    <div class="col-lg-4 col-md-6 d-flex">
                                        <article class="teacher-pricing-card {{ $isFeatured ? 'is-premium' : '' }}">
                                            @if ($packageBadge !== '')
                                                <div class="teacher-pricing-card__badge">{{ $packageBadge }}</div>
                                            @endif
                                            
                                            <div class="teacher-pricing-card__header">
                                                <div class="pricing-code">{{ strtoupper((string) $package->code) }}</div>
                                                <h3 class="h4 fw-bold mt-2">{{ $packageName }}</h3>
                                                <p class="pricing-tagline small opacity-75">{{ $packageTagline }}</p>
                                                <div class="pricing-value mt-4">
                                                    {{ (float) $package->price > 0 ? money($package->price) : $ui['free_label'] }}
                                                    @if((float) $package->price > 0)
                                                        <span class="pricing-cycle">/ {{ $package->billing_cycle === 'one_time' ? __('teacher::landing.one_time') : $package->billing_cycle }}</span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="teacher-pricing-card__body mt-4">
                                                <ul class="pricing-features list-unstyled">
                                                    <li class="d-flex align-items-center mb-3">
                                                        <i class="fa-solid fa-circle-check me-3 text-primary"></i>
                                                        <span>{{ str_replace(':rate', rtrim(rtrim(number_format($package->commission_rate, 2, '.', ''), '0'), '.'), $content['packages']['features']['commission']) }}</span>
                                                    </li>
                                                    <li class="d-flex align-items-center mb-3">
                                                        <i class="fa-solid fa-circle-check me-3 text-primary"></i>
                                                        <span>
                                                            {{ $courseLimit
                                                                ? str_replace(':count', $courseLimit, $content['packages']['features']['course_limit'])
                                                                : $content['packages']['features']['unlimited'] }}
                                                        </span>
                                                    </li>
                                                    <li class="d-flex align-items-center mb-3">
                                                        <i class="fa-solid fa-{{ $package->priority_review ? 'circle-check text-primary' : 'circle-xmark text-muted' }} me-3"></i>
                                                        <span class="{{ !$package->priority_review ? 'text-muted' : '' }}">
                                                            {{ $package->priority_review ? $content['packages']['features']['priority_yes'] : $content['packages']['features']['priority_no'] }}
                                                        </span>
                                                    </li>
                                                    <li class="d-flex align-items-center mb-3">
                                                        <i class="fa-solid fa-circle-check me-3 text-primary"></i>
                                                        <span>{{ str_replace(':level', $supportLabel, $content['packages']['features']['support']) }}</span>
                                                    </li>
                                                </ul>
                                            </div>

                                            <div class="teacher-pricing-card__footer mt-auto pt-4">
                                                <a class="btn {{ $isFeatured ? 'btn-primary' : 'btn-outline-primary' }} w-100 rounded-pill fw-bold py-3"
                                                    href="{{ $packageCtaUrl }}">
                                                    {{ $content['packages']['cta'] }}
                                                </a>
                                            </div>
                                        </article>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="py-5 teacher-section teacher-section--soft" data-reveal="up">
            <div class="container py-lg-4">
                <div class="teacher-section-heading text-center mb-5">
                    <span class="teacher-section-kicker">{{ $ui['faq_kicker'] }}</span>
                    <h2 class="display-6 fw-bold mb-3">{{ $content['faq']['title'] }}</h2>
                </div>

                <div class="row g-4">
                    @foreach ($content['faq']['items'] as $faq)
                        <div class="col-lg-6">
                            <article class="teacher-faq-item">
                                <h3 class="h5 fw-bold mb-3 d-flex align-items-start gap-3">
                                    <span class="faq-q-badge">Q</span>
                                    {{ $faq['question'] }}
                                </h3>
                                <div class="faq-a-wrap ps-5">
                                    <p class="mb-0 opacity-75">{{ $faq['answer'] }}</p>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="py-5 teacher-section" data-reveal="up">
            <div class="container">
                <div class="teacher-final-panel shadow-lg">
                    <div class="row g-5 align-items-center position-relative z-2">
                        <div class="col-lg-8">
                            <span class="teacher-section-kicker teacher-section-kicker--light mb-3">{{ $ui['final_kicker'] }}</span>
                            <h2 class="display-4 fw-bold text-white mb-4">{{ $content['final']['title'] }}</h2>
                            <p class="lead text-white opacity-90 mb-0" style="max-width: 600px;">{{ $content['final']['description'] }}</p>
                        </div>
                        <div class="col-lg-4 text-lg-end">
                            <a href="{{ $ctaUrl }}" class="btn btn-light btn-lg rounded-pill fw-bold px-5 py-3 teacher-final-btn">
                                <i class="fa-solid fa-user-graduate me-2"></i>
                                {{ $content['final']['primary'] }}
                            </a>
                        </div>
                    </div>
                    
                    <div class="panel-decor">
                        <div class="decor-1"></div>
                        <div class="decor-2"></div>
                        <div class="decor-3"></div>
                    </div>
                </div>
            </div>
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
                threshold: 0.1,
            });

            reveals.forEach((item) => observer.observe(item));
        })();
    </script>
@endsection

@section('stylesheets')
    <style>
        .teacher-landing-shell {
            --teacher-primary: #3b82f6;
            --teacher-primary-rgb: 59, 130, 246;
            --teacher-secondary: #0ea5e9;
            --teacher-accent: #22d3ee;
            --teacher-bg: #f8fafc;
            --teacher-surface: #ffffff;
            --teacher-text: #0f172a;
            --teacher-muted: #475569;
            --teacher-border: rgba(148, 163, 184, 0.15);
            --teacher-glass: rgba(0, 0, 0, 0.03);
            --teacher-shadow: 0 30px 80px rgba(0, 0, 0, 0.08);
            
            background-color: var(--teacher-bg);
            color: var(--teacher-text);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            scroll-behavior: smooth;
        }

        html[data-theme="dark"] .teacher-landing-shell {
            --teacher-bg: #030816;
            --teacher-surface: #0f172a;
            --teacher-text: #f1f5f9;
            --teacher-muted: #94a3b8;
            --teacher-border: rgba(255, 255, 255, 0.06);
            --teacher-glass: rgba(15, 23, 42, 0.6);
            --teacher-shadow: 0 40px 100px rgba(0, 0, 0, 0.4);
        }

        .teacher-section { position: relative; }
        .teacher-section--soft { background-color: rgba(241, 245, 249, 0.6); }
        html[data-theme="dark"] .teacher-section--soft { background-color: rgba(15, 23, 42, 0.4); }

        .teacher-section--dark {
            background: var(--teacher-surface);
            color: var(--teacher-text);
            border-top: 1px solid var(--teacher-border);
            border-bottom: 1px solid var(--teacher-border);
        }

        html[data-theme="dark"] .teacher-section--dark {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #fff;
            border: none;
        }

        /* Hero Section */
        .teacher-portal-hero {
            position: relative;
            padding: 140px 0 160px;
            background: radial-gradient(circle at 20% 30%, rgba(59, 130, 246, 0.12), transparent 40%),
                        radial-gradient(circle at 80% 70%, rgba(34, 211, 238, 0.08), transparent 40%),
                        var(--teacher-bg);
            color: var(--teacher-text);
            overflow: hidden;
            border-bottom: 1px solid var(--teacher-border);
        }

        html:not([data-theme="dark"]) .teacher-portal-hero {
            background: radial-gradient(circle at 20% 30%, rgba(59, 130, 246, 0.08), transparent 40%),
                        radial-gradient(circle at 80% 70%, rgba(34, 211, 238, 0.05), transparent 40%),
                        #f8fafc;
        }

        html[data-theme="dark"] .teacher-portal-hero {
            background: radial-gradient(circle at 20% 30%, rgba(59, 130, 246, 0.15), transparent 40%),
                        radial-gradient(circle at 80% 70%, rgba(34, 211, 238, 0.1), transparent 40%),
                        #020617;
            color: #fff;
        }

        .teacher-landing-shell section {
            overflow-x: clip; /* Prevent horizontal overflow per section */
        }

        .teacher-hero-layout {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 5rem;
            align-items: center;
            position: relative;
            z-index: 5;
        }

        .teacher-portal-badge {
            display: inline-block;
            padding: 8px 16px;
            background: rgba(59, 130, 246, 0.15);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 100px;
            color: #60a5fa;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .teacher-hero-content {
            position: relative;
            z-index: 5;
        }

        .teacher-portal-title {
            font-size: clamp(2.8rem, 6vw, 4.5rem);
            font-weight: 900;
            line-height: 1.05;
            letter-spacing: -0.04em;
            background: linear-gradient(to bottom right, var(--teacher-text) 40%, var(--teacher-muted));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        html[data-theme="dark"] .teacher-portal-title {
            background: linear-gradient(to bottom right, #fff 40%, rgba(255, 255, 255, 0.6));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .teacher-portal-desc {
            font-size: 1.2rem;
            color: var(--teacher-muted);
            max-width: 600px;
            line-height: 1.6;
        }

        html[data-theme="dark"] .teacher-portal-desc {
            color: rgba(255, 255, 255, 0.7);
        }

        .teacher-hero-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            background: var(--teacher-glass);
            border: 1px solid var(--teacher-border);
            border-radius: 100px;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--teacher-text);
            transition: all 0.3s ease;
        }

        html[data-theme="dark"] .teacher-hero-chip {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: rgba(255, 255, 255, 0.85);
        }

        .teacher-hero-chip:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.15);
        }

        .text-accent { color: var(--teacher-accent); }

        .teacher-landing-btn {
            padding: 14px 32px;
            border-radius: 14px;
            font-weight: 700;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .teacher-landing-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4);
        }

        .teacher-landing-btn--ghost {
            background: var(--teacher-glass);
            border: 1px solid var(--teacher-border);
            color: var(--teacher-text);
        }

        html[data-theme="dark"] .teacher-landing-btn--ghost {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .teacher-hero-login-hint a {
            color: var(--teacher-primary);
            text-decoration: none;
            font-weight: 600;
            border-bottom: 1px solid transparent;
            transition: all 0.3s ease;
        }

        .teacher-hero-login-hint a:hover {
            border-bottom-color: currentColor;
        }

        /* Glass Card */
        .teacher-hero-glass-card {
            background: var(--teacher-surface);
            backdrop-filter: blur(24px);
            border: 1px solid var(--teacher-border);
            border-radius: 32px;
            padding: 32px;
            box-shadow: var(--teacher-shadow);
            position: relative;
        }

        html[data-theme="dark"] .teacher-hero-glass-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 50px 100px rgba(0, 0, 0, 0.5);
        }

        .teacher-hero-glass-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 32px;
            padding: 1px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.15), transparent 50%);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
        }

        .hero-panel-list li {
            transition: transform 0.3s ease;
        }

        .hero-panel-list li:hover {
            transform: translateX(8px);
        }

        .glass-card-header {
            display: flex;
            align-items: center;
            padding-bottom: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .glass-dot { width: 10px; height: 10px; border-radius: 50%; margin-right: 6px; }
        .dot-red { background: #ff5f56; }
        .dot-yellow { background: #ffbd2e; }
        .dot-green { background: #27c93f; }

        .step-indicator {
            width: 32px;
            height: 32px;
            background: rgba(59, 130, 246, 0.2);
            border: 1px solid rgba(59, 130, 246, 0.4);
            color: #60a5fa;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            font-weight: 700;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }

        li:hover .step-indicator {
            background: var(--teacher-primary);
            color: #fff;
            transform: scale(1.1);
        }

        /* Earnings Panel */
        .teacher-earnings-panel {
            background: var(--teacher-surface);
            border-radius: 48px;
            padding: 80px 60px;
            color: var(--teacher-text);
            position: relative;
            overflow: hidden;
            border: 1px solid var(--teacher-border);
            box-shadow: var(--teacher-shadow);
        }

        html[data-theme="dark"] .teacher-earnings-panel {
            background: linear-gradient(135deg, #0f172a 0%, #020617 100%);
            color: #fff;
            border-color: rgba(255, 255, 255, 0.08);
        }

        .earnings-decor {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .earnings-decor div {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.15;
        }

        html[data-theme="dark"] .earnings-decor div { opacity: 0.3; }

        .e-decor-1 { width: 400px; height: 400px; top: -150px; right: -100px; background: var(--teacher-primary); }
        .e-decor-2 { width: 300px; height: 300px; bottom: -100px; left: -50px; background: var(--teacher-accent); }

        .earning-point-card {
            background: var(--teacher-glass);
            border: 1px solid var(--teacher-border);
            padding: 24px;
            border-radius: 24px;
            transition: all 0.3s ease;
        }

        html[data-theme="dark"] .earning-point-card {
            background: rgba(255, 255, 255, 0.03);
            border-color: rgba(255, 255, 255, 0.08);
        }

        /* Stats Section */
        .teacher-stat-card {
            background: var(--teacher-surface);
            padding: 32px;
            border-radius: 24px;
            height: 100%;
            border: 1px solid var(--teacher-border);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
        }

        html[data-theme="dark"] .teacher-stat-card {
            background: rgba(255, 255, 255, 0.03);
            border-color: rgba(255, 255, 255, 0.06);
            box-shadow: none;
        }

        .teacher-stat-card h3 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 12px;
            color: var(--teacher-primary);
        }

        .teacher-stat-card p {
            color: var(--teacher-muted);
            margin-bottom: 0;
            font-size: 1.1rem;
            line-height: 1.5;
        }

        html[data-theme="dark"] .teacher-stat-card p {
            color: rgba(255, 255, 255, 0.6);
        }

        /* Hero Shapes */
        .hero-bg-shapes {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .shape { position: absolute; border-radius: 50%; filter: blur(80px); }
        .shape-1 {
            width: 400px; height: 400px;
            background: rgba(59, 130, 246, 0.15);
            top: -100px; right: -50px;
        }
        .shape-2 {
            width: 300px; height: 300px;
            background: rgba(34, 211, 238, 0.1);
            bottom: -50px; left: -50px;
        }

        /* Section kicker */
        .teacher-section-kicker {
            display: inline-block;
            padding: 6px 14px;
            background: rgba(59, 130, 246, 0.1);
            color: var(--teacher-primary);
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 16px;
        }

        .teacher-section-kicker--light {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        /* Feature Cards */
        .teacher-feature-card {
            background: var(--teacher-surface);
            border: 1px solid var(--teacher-border);
            border-radius: 24px;
            padding: 40px;
            height: 100%;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--teacher-shadow);
        }

        .teacher-feature-card:hover {
            transform: translateY(-10px);
            border-color: var(--teacher-primary);
            box-shadow: 0 30px 60px rgba(59, 130, 246, 0.1);
        }

        .teacher-feature-card__icon {
            width: 60px;
            height: 60px;
            background: rgba(59, 130, 246, 0.1);
            color: var(--teacher-primary);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        /* Journey Steps */
        .teacher-steps-grid {
            display: grid;
            gap: 1.5rem;
        }

        .teacher-step-item {
            display: flex;
            gap: 20px;
            padding: 24px;
            background: var(--teacher-surface);
            border: 1px solid var(--teacher-border);
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .teacher-step-item:hover {
            border-color: var(--teacher-primary);
            background: var(--teacher-bg);
        }

        .teacher-step-item__count {
            width: 40px;
            height: 40px;
            background: var(--teacher-primary);
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            flex-shrink: 0;
        }

        /* Earnings / Point Card */
        .teacher-point-card {
            display: flex;
            gap: 20px;
            padding: 24px;
            background: var(--teacher-surface);
            border: 1px solid var(--teacher-border);
            border-radius: 24px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 2;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
        }

        html:not([data-theme="dark"]) .teacher-point-card {
            background: rgba(var(--teacher-primary-rgb), 0.04);
            border-color: rgba(var(--teacher-primary-rgb), 0.1);
        }

        .teacher-point-card:hover {
            transform: translateX(12px) scale(1.02);
            background: var(--teacher-surface);
            border-color: var(--teacher-primary);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
        }

        html[data-theme="dark"] .teacher-point-card {
            background: rgba(255, 255, 255, 0.03);
            border-color: rgba(255, 255, 255, 0.08);
        }

        html[data-theme="dark"] .teacher-point-card:hover {
            background: rgba(255, 255, 255, 0.06);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .teacher-point-card__bullet {
            width: 28px;
            height: 28px;
            background: var(--teacher-accent);
            color: #000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            flex-shrink: 0;
        }

        /* Pricing Section Tabs */
        .teacher-package-nav {
            background: var(--teacher-surface);
            padding: 8px;
            border-radius: 100px;
            border: 1px solid var(--teacher-border);
            display: inline-flex;
            width: auto;
        }

        .teacher-package-nav .nav-link {
            border-radius: 100px;
            padding: 10px 24px;
            font-weight: 600;
            color: var(--teacher-muted);
            transition: all 0.3s ease;
        }

        .teacher-package-nav .nav-link.active {
            background: var(--teacher-primary);
            color: #fff;
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }

        /* Pricing Card */
        .teacher-pricing-card {
            background: var(--teacher-surface);
            border: 1px solid var(--teacher-border);
            border-radius: 30px;
            padding: 40px;
            width: 100%;
            position: relative;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
        }

        .teacher-pricing-card:hover {
            transform: translateY(-12px);
            box-shadow: var(--teacher-shadow);
        }

        .teacher-pricing-card.is-premium {
            background: linear-gradient(180deg, var(--teacher-surface) 0%, var(--teacher-bg) 100%);
            border-color: var(--teacher-primary);
            transform: scale(1.05);
            z-index: 2;
        }

        .teacher-pricing-card.is-premium:hover {
            transform: translateY(-12px) scale(1.05);
        }

        .teacher-pricing-card__badge {
            position: absolute;
            top: 24px;
            right: 24px;
            background: var(--teacher-primary);
            color: #fff;
            padding: 4px 12px;
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .pricing-code {
            color: var(--teacher-primary);
            font-weight: 800;
            letter-spacing: 0.1em;
            font-size: 0.8rem;
        }

        .pricing-value {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--teacher-text);
        }

        .pricing-cycle {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--teacher-muted);
        }

        .pricing-features li span {
            font-size: 0.95rem;
            font-weight: 500;
        }

        /* FAQ */
        .teacher-faq-item {
            background: var(--teacher-surface);
            padding: 24px;
            border-radius: 20px;
            border: 1px solid var(--teacher-border);
        }

        .faq-q-badge {
            width: 32px;
            height: 32px;
            background: rgba(59, 130, 246, 0.1);
            color: var(--teacher-primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            flex-shrink: 0;
        }

        /* Final Panel */
        .teacher-final-panel {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            border-radius: 40px;
            position: relative;
            overflow: hidden;
            padding: 80px 60px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .teacher-final-btn {
            transition: all 0.3s ease;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
            color: #1e3a8a !important; /* Deep blue for maximum contrast on white */
        }

        .teacher-final-btn i {
            color: inherit;
        }

        .teacher-final-btn:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            background: #fff !important;
        }

        .panel-decor div {
            position: absolute;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.3) 0%, transparent 70%);
            filter: blur(60px);
            z-index: 1;
        }

        .decor-1 { width: 500px; height: 500px; top: -250px; right: -150px; }
        .decor-2 { width: 400px; height: 400px; bottom: -200px; left: -100px; }
        .decor-3 { width: 300px; height: 300px; top: 20%; left: 30%; opacity: 0.2; }

        [data-reveal] { 
            opacity: 0; 
            transform: translateY(20px); 
            transition: opacity 0.8s ease, transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            will-change: transform, opacity;
        }
        .is-visible { 
            opacity: 1; 
            transform: translateY(0); 
        }

        @media (max-width: 991px) {
            .teacher-hero-layout { grid-template-columns: 1fr; text-align: center; gap: 3rem; }
            .teacher-hero-copy { display: flex; flex-direction: column; align-items: center; }
            .teacher-portal-desc { margin-left: auto; margin-right: auto; }
            .teacher-hero-chips { justify-content: center; }
            .teacher-pricing-card.is-premium { transform: none; }
            .teacher-pricing-card.is-premium:hover { transform: translateY(-12px); }
        }
    </style>
@endsection
