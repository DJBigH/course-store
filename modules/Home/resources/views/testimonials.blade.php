@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    <section class="testimonial-landing py-5">
        <div class="container">
            <div class="testimonial-hero testimonial-reveal">
                <div class="testimonial-hero__content">
                    <span class="testimonial-hero__badge">{{ $heroBadge }}</span>
                    <h2>{{ $pageTitle }}</h2>
                    <p>{{ $heroDescription }}</p>
                </div>

                <div class="testimonial-stats">
                    @foreach ($highlightStats as $item)
                        <div class="testimonial-stat testimonial-reveal testimonial-reveal--delay" style="--reveal-delay: {{ $loop->index * 90 }}ms;">
                            <strong
                                class="js-testimonial-counter"
                                data-value="{{ $item['value'] }}"
                                data-display="{{ $item['display'] }}"
                                data-suffix="{{ $item['suffix'] ?? '' }}"
                                data-decimals="{{ $item['decimals'] ?? 0 }}"
                            >0</strong>
                            <span>{{ $item['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="row g-4 testimonial-grid">
                @foreach ($testimonials as $item)
                    <div class="col-12 col-md-6 col-xl-4">
                        <article
                            class="testimonial-card h-100 testimonial-reveal testimonial-reveal--delay"
                            style="--reveal-delay: {{ 180 + ($loop->index * 70) }}ms;"
                        >
                            <span class="testimonial-card__glow"></span>
                            <div class="testimonial-card__quote">
                                <i class="fa-solid fa-quote-left"></i>
                            </div>
                            <p>{{ $item['content'] }}</p>
                            <div class="testimonial-card__author">
                                <strong>{{ $item['name'] }}</strong>
                                <span>{{ $item['role'] }}</span>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection

@section('stylesheets')
    <style>
        .testimonial-landing {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at 10% 10%, rgba(56, 189, 248, 0.16), transparent 20%),
                radial-gradient(circle at 90% 0%, rgba(59, 130, 246, 0.18), transparent 22%),
                linear-gradient(180deg, #eff6ff 0%, #f8fafc 42%, #edf4ff 100%);
        }

        .testimonial-landing::before,
        .testimonial-landing::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            filter: blur(0);
            pointer-events: none;
        }

        .testimonial-landing::before {
            width: 280px;
            height: 280px;
            top: 56px;
            left: -100px;
            background: radial-gradient(circle, rgba(96, 165, 250, 0.18), transparent 70%);
        }

        .testimonial-landing::after {
            width: 340px;
            height: 340px;
            right: -120px;
            bottom: 50px;
            background: radial-gradient(circle, rgba(45, 212, 191, 0.14), transparent 72%);
        }

        .testimonial-hero {
            position: relative;
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr);
            gap: 24px;
            padding: 34px;
            margin-bottom: 28px;
            border-radius: 30px;
            background: rgba(255, 255, 255, 0.88);
            border: 1px solid rgba(191, 219, 254, 0.95);
            box-shadow: 0 26px 60px rgba(15, 23, 42, 0.08);
            backdrop-filter: blur(10px);
        }

        .testimonial-hero__content h2 {
            margin: 0 0 12px;
            font-size: clamp(34px, 4.5vw, 52px);
            line-height: 1.08;
            letter-spacing: -0.04em;
            color: #0f172a;
        }

        .testimonial-hero__content p {
            max-width: 700px;
            margin: 0;
            font-size: 18px;
            line-height: 1.75;
            color: #475569;
        }

        .testimonial-hero__badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
            padding: 10px 16px;
            border-radius: 999px;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.12), rgba(14, 165, 233, 0.14));
            color: #1d4ed8;
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 0.02em;
        }

        .testimonial-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            align-self: stretch;
        }

        .testimonial-stat {
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 160px;
            padding: 22px 18px;
            border-radius: 24px;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(241, 245, 249, 0.88));
            border: 1px solid rgba(191, 219, 254, 0.9);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.75);
            transition: transform 220ms ease, box-shadow 220ms ease, border-color 220ms ease;
        }

        .testimonial-stat::after {
            content: "";
            position: absolute;
            inset: auto -40px -55px auto;
            width: 110px;
            height: 110px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.22), transparent 72%);
        }

        .testimonial-stat:hover {
            transform: translateY(-6px);
            border-color: rgba(96, 165, 250, 0.9);
            box-shadow: 0 18px 34px rgba(37, 99, 235, 0.12);
        }

        .testimonial-stat strong {
            position: relative;
            z-index: 1;
            display: block;
            color: #1d4ed8;
            font-size: clamp(32px, 4vw, 42px);
            line-height: 1;
            font-weight: 900;
            letter-spacing: -0.04em;
        }

        .testimonial-stat span {
            position: relative;
            z-index: 1;
            display: block;
            margin-top: 12px;
            color: #475569;
            font-size: 14px;
            line-height: 1.6;
        }

        .testimonial-card {
            position: relative;
            overflow: hidden;
            padding: 28px 26px 24px;
            border-radius: 28px;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.94));
            border: 1px solid rgba(191, 219, 254, 0.9);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.07);
            transition: transform 260ms ease, box-shadow 260ms ease, border-color 260ms ease;
        }

        .testimonial-card:hover {
            transform: translateY(-10px) rotate(-0.4deg);
            border-color: rgba(96, 165, 250, 0.92);
            box-shadow: 0 28px 48px rgba(37, 99, 235, 0.16);
        }

        .testimonial-card:hover .testimonial-card__glow {
            opacity: 1;
            transform: scale(1.06);
        }

        .testimonial-card__glow {
            position: absolute;
            inset: -20% auto auto -8%;
            width: 180px;
            height: 180px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.18), transparent 68%);
            opacity: 0;
            transform: scale(0.88);
            transition: opacity 260ms ease, transform 260ms ease;
            pointer-events: none;
        }

        .testimonial-card__quote {
            margin-bottom: 18px;
            color: #93c5fd;
            font-size: 34px;
            line-height: 1;
        }

        .testimonial-card p {
            position: relative;
            z-index: 1;
            margin-bottom: 22px;
            color: #334155;
            font-size: 17px;
            line-height: 1.85;
        }

        .testimonial-card__author {
            position: relative;
            z-index: 1;
            padding-top: 18px;
            border-top: 1px solid rgba(191, 219, 254, 0.82);
        }

        .testimonial-card__author strong,
        .testimonial-card__author span {
            display: block;
        }

        .testimonial-card__author strong {
            margin-bottom: 4px;
            color: #0f172a;
            font-size: 20px;
        }

        .testimonial-card__author span {
            color: #2563eb;
            font-weight: 600;
        }

        .testimonial-reveal {
            opacity: 0;
            transform: translateY(24px);
            animation: testimonialFadeUp 720ms cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }

        .testimonial-reveal--delay {
            animation-delay: var(--reveal-delay, 120ms);
        }

        html[data-theme="dark"] .testimonial-landing {
            background:
                radial-gradient(circle at 8% 10%, rgba(37, 99, 235, 0.22), transparent 20%),
                radial-gradient(circle at 92% 4%, rgba(14, 165, 233, 0.18), transparent 18%),
                linear-gradient(180deg, #020617 0%, #061120 42%, #081427 100%);
        }

        html[data-theme="dark"] .testimonial-hero {
            background: rgba(10, 18, 34, 0.82);
            border-color: rgba(37, 99, 235, 0.34);
            box-shadow: 0 28px 56px rgba(2, 6, 23, 0.42);
        }

        html[data-theme="dark"] .testimonial-hero__content h2 {
            color: #e2e8f0;
        }

        html[data-theme="dark"] .testimonial-hero__content p {
            color: #94a3b8;
        }

        html[data-theme="dark"] .testimonial-hero__badge {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.22), rgba(14, 165, 233, 0.2));
            color: #bfdbfe;
        }

        html[data-theme="dark"] .testimonial-stat {
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.88), rgba(9, 17, 31, 0.96));
            border-color: rgba(37, 99, 235, 0.3);
            box-shadow: inset 0 1px 0 rgba(148, 163, 184, 0.08);
        }

        html[data-theme="dark"] .testimonial-stat strong,
        html[data-theme="dark"] .testimonial-card__author span {
            color: #60a5fa;
        }

        html[data-theme="dark"] .testimonial-stat span {
            color: #94a3b8;
        }

        html[data-theme="dark"] .testimonial-card {
            background: linear-gradient(180deg, rgba(10, 18, 34, 0.94), rgba(7, 14, 28, 0.98));
            border-color: rgba(37, 99, 235, 0.28);
            box-shadow: 0 20px 44px rgba(2, 6, 23, 0.4);
        }

        html[data-theme="dark"] .testimonial-card p {
            color: #cbd5e1;
        }

        html[data-theme="dark"] .testimonial-card__author {
            border-top-color: rgba(51, 65, 85, 0.95);
        }

        html[data-theme="dark"] .testimonial-card__author strong {
            color: #f8fafc;
        }

        @keyframes testimonialFadeUp {
            from {
                opacity: 0;
                transform: translateY(24px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 991.98px) {
            .testimonial-hero {
                grid-template-columns: 1fr;
            }

            .testimonial-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767.98px) {
            .testimonial-landing {
                padding-top: 2rem !important;
                padding-bottom: 2.5rem !important;
            }

            .testimonial-hero {
                padding: 24px;
                border-radius: 24px;
            }

            .testimonial-stats {
                grid-template-columns: 1fr;
            }

            .testimonial-stat {
                min-height: 132px;
            }

            .testimonial-card {
                padding: 24px 22px 22px;
            }

            .testimonial-card p {
                font-size: 16px;
            }
        }

        @media (max-width: 575.98px) {
            .testimonial-landing::before {
                width: 180px;
                height: 180px;
                left: -70px;
            }

            .testimonial-landing::after {
                width: 220px;
                height: 220px;
                right: -90px;
            }

            .testimonial-hero__content h2 {
                font-size: 2rem;
            }

            .testimonial-hero__content p {
                font-size: 15px;
            }

            .testimonial-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const counters = document.querySelectorAll('.js-testimonial-counter');

            if (!counters.length) {
                return;
            }

            const formatNumber = (value, decimals) => {
                return Number(value).toLocaleString(undefined, {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals,
                });
            };

            const animateCounter = (element) => {
                if (element.dataset.animated === 'true') {
                    return;
                }

                element.dataset.animated = 'true';

                const target = Number(element.dataset.value || 0);
                const decimals = Number(element.dataset.decimals || 0);
                const suffix = element.dataset.suffix || '';
                const finalDisplay = element.dataset.display || `${formatNumber(target, decimals)}${suffix}`;
                const duration = 1300;
                const start = performance.now();

                const step = (now) => {
                    const progress = Math.min((now - start) / duration, 1);
                    const eased = 1 - Math.pow(1 - progress, 3);
                    const current = target * eased;

                    if (progress < 1) {
                        element.textContent = `${formatNumber(current, decimals)}${suffix}`;
                        requestAnimationFrame(step);
                        return;
                    }

                    element.textContent = finalDisplay;
                };

                requestAnimationFrame(step);
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                });
            }, {
                threshold: 0.35,
            });

            counters.forEach((counter) => observer.observe(counter));
        });
    </script>
@endsection
