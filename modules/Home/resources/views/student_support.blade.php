@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    <section class="support-landing py-5">
        <div class="container">
            <div class="support-hero mb-4">
                <span class="support-hero__badge">{{ $heroBadge }}</span>
                <h2>{{ $heroHeading }}</h2>
                <p>{{ $heroDescription }}</p>
            </div>

            <div class="row g-4 mb-4">
                @foreach ($supportChannels as $item)
                    <div class="col-12 col-lg-4">
                        <article class="support-card h-100">
                            <h3>{{ $item['title'] }}</h3>
                            <p>{{ $item['description'] }}</p>
                            <span>{{ $item['meta'] }}</span>
                        </article>
                    </div>
                @endforeach
            </div>

            <div class="row g-4">
                <div class="col-12 col-lg-5">
                    <div class="support-panel h-100">
                        <h3>{{ $contactPanelTitle }}</h3>
                        @foreach ($contactCards as $item)
                            <div class="support-contact">
                                <strong>{{ $item['label'] }}</strong>
                                <div>
                                    @if (($item['type'] ?? null) === 'facebook')
                                        <a href="{{ setting_url('facebook') }}" {!! setting_target('facebook') !!}>
                                            {{ $item['value'] }}
                                        </a>
                                    @else
                                        {{ $item['value'] }}
                                    @endif
                                </div>
                                <p>{{ $item['note'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-12 col-lg-7">
                    <div class="support-panel h-100">
                        <h3>{{ $flowPanelTitle }}</h3>
                        <div class="support-flow">
                            @foreach ($supportFlow as $index => $step)
                                <div class="support-step">
                                    <span>{{ $index + 1 }}</span>
                                    <p>{{ $step }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('stylesheets')
    <style>
        .support-landing {
            background:
                radial-gradient(circle at top right, rgba(37, 99, 235, 0.08), transparent 22%),
                linear-gradient(180deg, #f8fbff 0%, #eef4fb 100%);
        }

        .support-hero,
        .support-card,
        .support-panel {
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(203, 213, 225, 0.78);
            border-radius: 24px;
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.06);
        }

        .support-hero {
            padding: 32px;
        }

        .support-hero__badge {
            display: inline-flex;
            align-items: center;
            padding: 9px 15px;
            border-radius: 999px;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.14), rgba(96, 165, 250, 0.2));
            color: #1d4ed8;
            font-size: 14px;
            font-weight: 800;
            margin-bottom: 16px;
        }

        .support-hero h2,
        .support-card h3,
        .support-panel h3,
        .support-contact strong {
            color: #0f172a;
            font-weight: 800;
        }

        .support-hero h2 {
            margin-bottom: 12px;
            font-size: clamp(32px, 4vw, 44px);
            line-height: 1.15;
            letter-spacing: -0.03em;
        }

        .support-hero p,
        .support-card p,
        .support-panel p,
        .support-contact div {
            color: #475569;
            margin-bottom: 0;
        }

        .support-hero p {
            max-width: 900px;
            font-size: 18px;
            line-height: 1.75;
        }

        .support-card {
            padding: 24px;
            transition: transform 220ms ease, box-shadow 220ms ease, border-color 220ms ease;
        }

        .support-card:hover,
        .support-panel:hover {
            transform: translateY(-4px);
            border-color: rgba(96, 165, 250, 0.8);
            box-shadow: 0 22px 42px rgba(37, 99, 235, 0.08);
        }

        .support-card h3 {
            margin-bottom: 12px;
            font-size: 28px;
            line-height: 1.25;
            letter-spacing: -0.02em;
        }

        .support-card p {
            font-size: 17px;
            line-height: 1.8;
        }

        .support-card span {
            display: inline-block;
            margin-top: 18px;
            color: #1d4ed8;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.7;
        }

        .support-panel {
            padding: 28px;
            transition: transform 220ms ease, box-shadow 220ms ease, border-color 220ms ease;
        }

        .support-panel h3 {
            margin-bottom: 18px;
            font-size: 26px;
            line-height: 1.25;
            letter-spacing: -0.02em;
        }

        .support-contact + .support-contact {
            margin-top: 18px;
            padding-top: 18px;
            border-top: 1px solid rgba(203, 213, 225, 0.78);
        }

        .support-contact strong {
            display: block;
            margin-bottom: 6px;
            font-size: 22px;
        }

        .support-contact div {
            font-size: 22px;
            font-weight: 700;
            color: #1d4ed8;
        }

        .support-contact a {
            color: inherit;
            text-decoration: none;
            transition: color 180ms ease, opacity 180ms ease;
        }

        .support-contact a:hover {
            color: #2563eb;
            opacity: 0.92;
        }

        .support-contact p {
            margin-top: 6px;
            line-height: 1.75;
        }

        .support-flow {
            display: grid;
            gap: 14px;
        }

        .support-step {
            display: grid;
            grid-template-columns: 48px 1fr;
            gap: 14px;
            align-items: start;
            padding: 18px;
            border-radius: 18px;
            background: #f8fbff;
            border: 1px solid rgba(219, 234, 254, 0.95);
        }

        .support-step span {
            width: 48px;
            height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: #fff;
            font-weight: 800;
            font-size: 18px;
        }

        .support-step p {
            font-size: 17px;
            line-height: 1.75;
        }

        html[data-theme="dark"] .support-landing {
            background:
                radial-gradient(circle at top right, rgba(37, 99, 235, 0.16), transparent 22%),
                linear-gradient(180deg, #020617 0%, #06101f 100%);
        }

        html[data-theme="dark"] .support-hero,
        html[data-theme="dark"] .support-card,
        html[data-theme="dark"] .support-panel {
            background: linear-gradient(180deg, rgba(12, 19, 33, 0.96), rgba(8, 15, 28, 0.98));
            border-color: rgba(37, 99, 235, 0.28);
            box-shadow: 0 20px 40px rgba(2, 6, 23, 0.34);
        }

        html[data-theme="dark"] .support-hero__badge {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.24), rgba(96, 165, 250, 0.18));
            color: #bfdbfe;
        }

        html[data-theme="dark"] .support-hero h2,
        html[data-theme="dark"] .support-card h3,
        html[data-theme="dark"] .support-panel h3,
        html[data-theme="dark"] .support-contact strong {
            color: #f8fafc;
        }

        html[data-theme="dark"] .support-hero p,
        html[data-theme="dark"] .support-card p,
        html[data-theme="dark"] .support-panel p {
            color: #94a3b8;
        }

        html[data-theme="dark"] .support-card span,
        html[data-theme="dark"] .support-contact div {
            color: #60a5fa;
        }

        html[data-theme="dark"] .support-contact a:hover {
            color: #93c5fd;
        }

        html[data-theme="dark"] .support-contact + .support-contact {
            border-top-color: rgba(51, 65, 85, 0.85);
        }

        html[data-theme="dark"] .support-step {
            background: rgba(15, 23, 42, 0.82);
            border-color: rgba(37, 99, 235, 0.24);
        }

        @media (max-width: 767.98px) {
            .support-hero {
                padding: 24px;
            }

            .support-hero h2 {
                font-size: 30px;
            }

            .support-card,
            .support-panel {
                padding: 22px;
            }

            .support-card h3,
            .support-panel h3 {
                font-size: 24px;
            }
        }
    </style>
@endsection
