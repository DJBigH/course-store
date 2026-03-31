@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    <section class="faq-landing py-5">
        <div class="container">
            <div class="faq-hero mb-4">
                <span>{{ $heroBadge }}</span>
                <h2>{{ $heroHeading }}</h2>
                <p>{{ $heroDescription }}</p>
            </div>

            <div class="faq-stack">
                @foreach ($faqs as $index => $item)
                    @php
                        $faqId = 'faq-item-' . $index;
                    @endphp
                    <article class="faq-item {{ $loop->first ? 'is-open' : '' }}">
                        <div class="faq-item__number">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</div>
                        <div class="faq-item__content">
                            <button
                                type="button"
                                class="faq-item__trigger"
                                id="{{ $faqId }}-trigger"
                                aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                                aria-controls="{{ $faqId }}-answer"
                            >
                                <span>{{ $item['question'] }}</span>
                                <span class="faq-item__icon" aria-hidden="true"></span>
                            </button>

                            <div class="faq-item__answer" id="{{ $faqId }}-answer" role="region"
                                aria-labelledby="{{ $faqId }}-trigger" @if (!$loop->first) hidden @endif>
                                <p>{{ $item['answer'] }}</p>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endsection

@section('stylesheets')
    <style>
        .faq-landing {
            background:
                radial-gradient(circle at top right, rgba(59, 130, 246, 0.08), transparent 18%),
                linear-gradient(180deg, #f8fbff 0%, #eef4fb 100%);
        }

        .faq-hero {
            padding: 30px;
            border-radius: 28px;
            background: linear-gradient(135deg, #111c36, #243b82);
            color: #fff;
            box-shadow: 0 22px 48px rgba(15, 23, 42, 0.22);
        }

        .faq-hero span {
            display: inline-flex;
            margin-bottom: 14px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            font-size: 14px;
            font-weight: 700;
        }

        .faq-hero h2 {
            margin-bottom: 12px;
            font-size: clamp(28px, 3.2vw, 40px);
            line-height: 1.16;
            letter-spacing: -0.03em;
        }

        .faq-hero p {
            max-width: 920px;
            margin-bottom: 0;
            font-size: 16px;
            line-height: 1.75;
            color: rgba(255, 255, 255, 0.88);
        }

        .faq-stack {
            display: grid;
            gap: 18px;
        }

        .faq-item {
            display: grid;
            grid-template-columns: 64px 1fr;
            gap: 16px;
            align-items: start;
            padding: 18px 20px;
            border-radius: 24px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(248, 250, 252, 0.96));
            border: 1px solid rgba(203, 213, 225, 0.88);
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.05);
            transition: transform 220ms ease, box-shadow 220ms ease, border-color 220ms ease;
        }

        .faq-item:hover {
            transform: translateY(-3px);
            border-color: rgba(96, 165, 250, 0.9);
            box-shadow: 0 18px 34px rgba(37, 99, 235, 0.08);
        }

        .faq-item.is-open {
            border-color: rgba(96, 165, 250, 0.95);
            background: linear-gradient(180deg, rgba(239, 246, 255, 0.98), rgba(248, 250, 252, 0.98));
            box-shadow: 0 18px 38px rgba(37, 99, 235, 0.1);
        }

        .faq-item__number {
            width: 64px;
            height: 64px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 20px;
            background: linear-gradient(180deg, #dbeafe, #bfdbfe);
            color: #1d4ed8;
            font-size: 22px;
            font-weight: 800;
        }

        .faq-item__content {
            min-width: 0;
        }

        .faq-item__trigger {
            width: 100%;
            display: flex;
            align-items: start;
            justify-content: space-between;
            gap: 18px;
            padding: 0;
            border: 0;
            background: transparent;
            text-align: left;
            cursor: pointer;
        }

        .faq-item__trigger span {
            color: #0f172a;
            font-size: 21px;
            line-height: 1.45;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .faq-item__icon {
            position: relative;
            flex: 0 0 34px;
            width: 34px;
            height: 34px;
            margin-top: 1px;
            border-radius: 11px;
            background: rgba(37, 99, 235, 0.1);
            transition: background-color 220ms ease, transform 220ms ease;
        }

        .faq-item__icon::before,
        .faq-item__icon::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 12px;
            height: 2px;
            border-radius: 999px;
            background: #2563eb;
            transform: translate(-50%, -50%);
            transition: transform 220ms ease, opacity 220ms ease, background-color 220ms ease;
        }

        .faq-item__icon::after {
            transform: translate(-50%, -50%) rotate(90deg);
        }

        .faq-item.is-open .faq-item__icon {
            background: rgba(37, 99, 235, 0.14);
        }

        .faq-item.is-open .faq-item__icon::after {
            opacity: 0;
            transform: translate(-50%, -50%) rotate(90deg) scaleX(0.3);
        }

        .faq-item__answer {
            display: grid;
            grid-template-rows: 0fr;
            opacity: 0;
            transition: grid-template-rows 280ms ease, opacity 220ms ease, padding-top 220ms ease;
        }

        .faq-item.is-open .faq-item__answer {
            grid-template-rows: 1fr;
            opacity: 1;
            padding-top: 16px;
        }

        .faq-item__answer > * {
            overflow: hidden;
        }

        .faq-item__answer p {
            max-width: 980px;
            margin-bottom: 0;
            color: #475569;
            font-size: 15px;
            line-height: 1.85;
        }

        html[data-theme="dark"] .faq-landing {
            background:
                radial-gradient(circle at top right, rgba(59, 130, 246, 0.14), transparent 18%),
                linear-gradient(180deg, #020617 0%, #071222 100%);
        }

        html[data-theme="dark"] .faq-hero {
            background: linear-gradient(135deg, #0b1630, #1b2f66);
            box-shadow: 0 24px 50px rgba(2, 6, 23, 0.36);
        }

        html[data-theme="dark"] .faq-item {
            background: linear-gradient(180deg, rgba(11, 19, 33, 0.96), rgba(7, 14, 28, 0.98));
            border-color: rgba(37, 99, 235, 0.24);
            box-shadow: 0 16px 34px rgba(2, 6, 23, 0.34);
        }

        html[data-theme="dark"] .faq-item:hover,
        html[data-theme="dark"] .faq-item.is-open {
            border-color: rgba(96, 165, 250, 0.54);
            background: linear-gradient(180deg, rgba(14, 25, 45, 0.98), rgba(9, 18, 34, 1));
            box-shadow: 0 20px 40px rgba(2, 6, 23, 0.4);
        }

        html[data-theme="dark"] .faq-item__number {
            background: linear-gradient(180deg, rgba(37, 99, 235, 0.24), rgba(96, 165, 250, 0.18));
            color: #93c5fd;
        }

        html[data-theme="dark"] .faq-item__trigger span {
            color: #e2e8f0;
        }

        html[data-theme="dark"] .faq-item__icon {
            background: rgba(37, 99, 235, 0.18);
        }

        html[data-theme="dark"] .faq-item__icon::before,
        html[data-theme="dark"] .faq-item__icon::after {
            background: #93c5fd;
        }

        html[data-theme="dark"] .faq-item__answer p {
            color: #94a3b8;
        }

        @media (max-width: 767.98px) {
            .faq-hero {
                padding: 24px;
                border-radius: 24px;
            }

            .faq-hero h2 {
                font-size: 28px;
            }

            .faq-item {
                grid-template-columns: 1fr;
                padding: 20px;
            }

            .faq-item__number {
                width: 58px;
                height: 58px;
                border-radius: 18px;
                font-size: 21px;
            }

            .faq-item__trigger span {
                font-size: 19px;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.faq-item__trigger').forEach(function (trigger) {
                trigger.addEventListener('click', function () {
                    const item = trigger.closest('.faq-item');
                    const answer = item.querySelector('.faq-item__answer');
                    const isOpen = item.classList.contains('is-open');

                    document.querySelectorAll('.faq-item').forEach(function (faqItem) {
                        faqItem.classList.remove('is-open');
                        faqItem.querySelector('.faq-item__trigger')?.setAttribute('aria-expanded', 'false');
                        const faqAnswer = faqItem.querySelector('.faq-item__answer');

                        if (faqAnswer) {
                            faqAnswer.hidden = true;
                        }
                    });

                    if (!isOpen) {
                        item.classList.add('is-open');
                        trigger.setAttribute('aria-expanded', 'true');
                        answer.hidden = false;
                    }
                });
            });
        });
    </script>
@endsection
