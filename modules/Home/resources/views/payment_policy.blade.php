@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    <section class="policy-page py-5">
        <div class="container">
            <div class="policy-hero mb-4">
                <span class="policy-hero__badge">{{ $heroBadge }}</span>
                <h2>{{ $heroHeading }}</h2>
                <p>{{ $heroDescription }}</p>
            </div>

            <div class="policy-content">
                @foreach ($sections as $section)
                    <h3>{{ $section['title'] }}</h3>
                    @foreach ($section['paragraphs'] as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                @endforeach
            </div>
        </div>
    </section>
@endsection

@section('stylesheets')
    <style>
        .policy-page {
            background:
                radial-gradient(circle at top right, rgba(59, 130, 246, 0.08), transparent 18%),
                linear-gradient(180deg, #f8fbff 0%, #eef4fb 100%);
        }

        .policy-hero,
        .policy-content {
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(203, 213, 225, 0.78);
            border-radius: 24px;
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.06);
        }

        .policy-hero {
            padding: 30px;
        }

        .policy-hero__badge {
            display: inline-flex;
            margin-bottom: 14px;
            padding: 8px 14px;
            border-radius: 999px;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.14), rgba(96, 165, 250, 0.2));
            color: #1d4ed8;
            font-size: 14px;
            font-weight: 800;
        }

        .policy-hero h2,
        .policy-content h3 {
            color: #0f172a;
            font-weight: 800;
        }

        .policy-hero h2 {
            margin-bottom: 12px;
            font-size: clamp(30px, 3.8vw, 42px);
            line-height: 1.16;
        }

        .policy-hero p,
        .policy-content p {
            color: #475569;
            line-height: 1.85;
        }

        .policy-content {
            padding: 30px;
        }

        .policy-content h3 + p {
            margin-top: 10px;
        }

        .policy-content h3:not(:first-child) {
            margin-top: 28px;
        }

        html[data-theme="dark"] .policy-page {
            background:
                radial-gradient(circle at top right, rgba(37, 99, 235, 0.16), transparent 18%),
                linear-gradient(180deg, #020617 0%, #06101f 100%);
        }

        html[data-theme="dark"] .policy-hero,
        html[data-theme="dark"] .policy-content {
            background: linear-gradient(180deg, rgba(12, 19, 33, 0.96), rgba(8, 15, 28, 0.98));
            border-color: rgba(37, 99, 235, 0.28);
            box-shadow: 0 20px 40px rgba(2, 6, 23, 0.34);
        }

        html[data-theme="dark"] .policy-hero__badge {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.24), rgba(96, 165, 250, 0.18));
            color: #bfdbfe;
        }

        html[data-theme="dark"] .policy-hero h2,
        html[data-theme="dark"] .policy-content h3 {
            color: #f8fafc;
        }

        html[data-theme="dark"] .policy-hero p,
        html[data-theme="dark"] .policy-content p {
            color: #94a3b8;
        }
    </style>
@endsection
