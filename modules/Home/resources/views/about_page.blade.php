@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    <section class="about-page py-5">
        <div class="container">
            <div class="about-page__hero about-page__reveal mb-4">
                <div class="row g-4 align-items-center">
                    <div class="col-12 col-lg-7">
                        <span class="about-page__badge">{{ $heroBadge }}</span>
                        <h2>{{ $heroHeading }}</h2>
                        <p>{{ $heroDescription }}</p>
                    </div>
                    <div class="col-12 col-lg-5">
                        <div class="about-page__stats">
                            @foreach ($highlights as $item)
                                <article class="about-page__stat">
                                    <strong
                                        @if (isset($item['count'])) class="js-count-up" data-count="{{ $item['count'] }}" data-suffix="{{ $item['suffix'] ?? '' }}" @endif
                                    >{{ $item['value'] }}</strong>
                                    <span>{{ $item['label'] }}</span>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12 col-lg-7">
                    <div class="about-page__panel about-page__reveal">
                        <h3>{{ $commitmentsTitle }}</h3>
                        <div class="about-page__commitments">
                            @foreach ($commitments as $item)
                                <article class="about-page__commitment">
                                    <div class="about-page__commitment-index">{{ str_pad((string) ($loop->iteration), 2, '0', STR_PAD_LEFT) }}</div>
                                    <div>
                                        <h4>{{ $item['title'] }}</h4>
                                        <p>{{ $item['description'] }}</p>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-5">
                    <div class="about-page__panel about-page__channels about-page__reveal">
                        <h3>{{ __('common.contact') }}</h3>
                        <div class="about-page__channel-list">
                            <a href="{{ setting_url('facebook') }}" {!! setting_target('facebook') !!} class="about-page__channel">
                                <i class="fab fa-facebook-f"></i>
                                <span>Facebook</span>
                            </a>
                            <a href="{{ setting_url('instagram') }}" {!! setting_target('instagram') !!} class="about-page__channel">
                                <i class="fab fa-instagram"></i>
                                <span>Instagram</span>
                            </a>
                            <a href="{{ setting_url('youtube') }}" {!! setting_target('youtube') !!} class="about-page__channel">
                                <i class="fab fa-youtube"></i>
                                <span>YouTube</span>
                            </a>
                            <a href="{{ setting_url('tiktok') }}" {!! setting_target('tiktok') !!} class="about-page__channel">
                                <i class="fab fa-tiktok"></i>
                                <span>TikTok</span>
                            </a>
                        </div>
                        <div class="about-page__contact-meta">
                            <p><i class="fa-solid fa-envelope"></i>{{ setting('email', 'bigkudemy@gmail.com') }}</p>
                            <p><i class="fa-solid fa-mobile-screen"></i>{{ setting('phone', '0123456789') }}</p>
                            <p><i class="fa-solid fa-location-dot"></i>{{ setting('address', 'Hà Nội, Việt Nam') }}</p>
                        </div>
                        <div class="about-page__support-note">
                            <h4>{{ $contactNoteTitle }}</h4>
                            <p>{{ $contactNoteDescription }}</p>
                            <span>{{ $contactNoteMeta }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('stylesheets')
    <style>
        .about-page {
            background:
                radial-gradient(circle at top left, rgba(59, 130, 246, 0.08), transparent 18%),
                linear-gradient(180deg, #f8fbff 0%, #eef4fb 100%);
        }

        .about-page__hero,
        .about-page__panel {
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(203, 213, 225, 0.78);
            border-radius: 24px;
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.06);
        }

        .about-page__hero,
        .about-page__panel {
            padding: 30px;
        }

        .about-page__reveal {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity 0.7s ease, transform 0.7s ease;
        }

        .about-page__reveal.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .about-page__badge {
            display: inline-flex;
            margin-bottom: 14px;
            padding: 8px 14px;
            border-radius: 999px;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.14), rgba(96, 165, 250, 0.2));
            color: #1d4ed8;
            font-size: 14px;
            font-weight: 800;
        }

        .about-page__hero h2,
        .about-page__panel h3,
        .about-page__commitment h4 {
            color: #0f172a;
            font-weight: 800;
        }

        .about-page__hero h2 {
            margin-bottom: 12px;
            font-size: clamp(30px, 3.8vw, 42px);
            line-height: 1.16;
        }

        .about-page__hero p,
        .about-page__commitment p,
        .about-page__contact-meta p,
        .about-page__stat span {
            color: #475569;
            line-height: 1.85;
        }

        .about-page__stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .about-page__stat {
            padding: 20px;
            border-radius: 20px;
            background: linear-gradient(180deg, rgba(59, 130, 246, 0.08), rgba(14, 165, 233, 0.06));
            border: 1px solid rgba(147, 197, 253, 0.35);
        }

        .about-page__stat strong {
            display: block;
            margin-bottom: 8px;
            color: #0f172a;
            font-size: 1.9rem;
            font-weight: 800;
        }

        .about-page__commitments {
            display: grid;
            gap: 18px;
            margin-top: 18px;
        }

        .about-page__commitment {
            display: grid;
            grid-template-columns: 62px minmax(0, 1fr);
            gap: 16px;
            align-items: start;
            padding: 18px;
            border-radius: 20px;
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.96), rgba(241, 245, 249, 0.96));
            border: 1px solid rgba(203, 213, 225, 0.7);
        }

        .about-page__commitment-index {
            display: grid;
            place-items: center;
            width: 62px;
            height: 62px;
            border-radius: 18px;
            background: linear-gradient(135deg, #2563eb, #60a5fa);
            color: #fff;
            font-weight: 800;
            font-size: 1.3rem;
        }

        .about-page__commitment h4 {
            margin-bottom: 8px;
            font-size: 1.15rem;
        }

        .about-page__channels h3 {
            margin-bottom: 18px;
        }

        .about-page__channel-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }

        .about-page__channel {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 16px;
            border-radius: 16px;
            background: rgba(248, 250, 252, 0.96);
            border: 1px solid rgba(203, 213, 225, 0.75);
            color: #16324f;
            font-weight: 700;
            transition: transform 0.25s ease, border-color 0.25s ease, color 0.25s ease;
        }

        .about-page__channel:hover {
            color: #0f62fe;
            text-decoration: none;
            transform: translateY(-2px);
            border-color: rgba(59, 130, 246, 0.45);
        }

        .about-page__contact-meta {
            display: grid;
            gap: 10px;
            margin-bottom: 18px;
        }

        .about-page__contact-meta p {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
        }

        .about-page__contact-meta i,
        .about-page__channel i {
            color: #2563eb;
        }

        .about-page__support-note {
            padding: 18px 18px 16px;
            border-radius: 18px;
            background: linear-gradient(180deg, rgba(37, 99, 235, 0.08), rgba(96, 165, 250, 0.05));
            border: 1px solid rgba(147, 197, 253, 0.24);
        }

        .about-page__support-note h4 {
            margin-bottom: 8px;
            color: #0f172a;
            font-size: 1rem;
            font-weight: 800;
        }

        .about-page__support-note p {
            margin-bottom: 8px;
            color: #475569;
            line-height: 1.72;
        }

        .about-page__support-note span {
            display: inline-flex;
            color: #1d4ed8;
            font-size: 0.92rem;
            font-weight: 700;
            line-height: 1.6;
        }

        html[data-theme="dark"] .about-page {
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.16), transparent 18%),
                linear-gradient(180deg, #020617 0%, #06101f 100%);
        }

        html[data-theme="dark"] .about-page__hero,
        html[data-theme="dark"] .about-page__panel {
            background: linear-gradient(180deg, rgba(12, 19, 33, 0.96), rgba(8, 15, 28, 0.98));
            border-color: rgba(37, 99, 235, 0.28);
            box-shadow: 0 20px 40px rgba(2, 6, 23, 0.34);
        }

        html[data-theme="dark"] .about-page__badge {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.24), rgba(96, 165, 250, 0.18));
            color: #bfdbfe;
        }

        html[data-theme="dark"] .about-page__hero h2,
        html[data-theme="dark"] .about-page__panel h3,
        html[data-theme="dark"] .about-page__commitment h4,
        html[data-theme="dark"] .about-page__stat strong,
        html[data-theme="dark"] .about-page__channel {
            color: #f8fafc;
        }

        html[data-theme="dark"] .about-page__hero p,
        html[data-theme="dark"] .about-page__commitment p,
        html[data-theme="dark"] .about-page__contact-meta p,
        html[data-theme="dark"] .about-page__stat span,
        html[data-theme="dark"] .about-page__support-note p {
            color: #94a3b8;
        }

        html[data-theme="dark"] .about-page__stat {
            background: linear-gradient(180deg, rgba(37, 99, 235, 0.18), rgba(14, 165, 233, 0.12));
            border-color: rgba(96, 165, 250, 0.2);
        }

        html[data-theme="dark"] .about-page__commitment,
        html[data-theme="dark"] .about-page__channel {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(148, 163, 184, 0.12);
        }

        html[data-theme="dark"] .about-page__support-note {
            background: linear-gradient(180deg, rgba(37, 99, 235, 0.14), rgba(255, 255, 255, 0.03));
            border-color: rgba(96, 165, 250, 0.18);
        }

        html[data-theme="dark"] .about-page__support-note h4 {
            color: #f8fafc;
        }

        html[data-theme="dark"] .about-page__support-note span {
            color: #bfdbfe;
        }

        html[data-theme="dark"] .about-page__channel:hover {
            color: #eff6ff;
            border-color: rgba(96, 165, 250, 0.38);
        }

        @media (max-width: 767.98px) {
            .about-page__hero,
            .about-page__panel {
                padding: 22px;
            }

            .about-page__stats,
            .about-page__channel-list {
                grid-template-columns: 1fr;
            }

            .about-page__commitment {
                grid-template-columns: 52px minmax(0, 1fr);
                gap: 14px;
            }

            .about-page__commitment-index {
                width: 52px;
                height: 52px;
                font-size: 1.05rem;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const counters = document.querySelectorAll('.js-count-up');
            const revealItems = document.querySelectorAll('.about-page__reveal');

            const formatNumber = (value) => new Intl.NumberFormat().format(value);

            const animateCounter = (element) => {
                if (element.dataset.animated === 'true') {
                    return;
                }

                element.dataset.animated = 'true';

                const target = parseInt(element.dataset.count || '0', 10);
                const suffix = element.dataset.suffix || '';
                const duration = 1400;
                const startTime = performance.now();

                const update = (currentTime) => {
                    const progress = Math.min((currentTime - startTime) / duration, 1);
                    const eased = 1 - Math.pow(1 - progress, 3);
                    const currentValue = Math.round(target * eased);

                    element.textContent = `${formatNumber(currentValue)}${suffix}`;

                    if (progress < 1) {
                        requestAnimationFrame(update);
                    }
                };

                requestAnimationFrame(update);
            };

            const counterObserver = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        animateCounter(entry.target);
                        counterObserver.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.35
            });

            counters.forEach((counter) => counterObserver.observe(counter));

            const revealObserver = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        revealObserver.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.12
            });

            revealItems.forEach((item) => revealObserver.observe(item));
        });
    </script>
@endsection
