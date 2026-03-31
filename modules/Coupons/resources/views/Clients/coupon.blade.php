@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    <section class="coupon-page py-5">
        <div class="container">

            {{-- ================== 1. MÃ CỦA TÔI ================== --}}
            @auth('students')
                <div class="mb-5" data-pagination-scroll>
                    <h4 class="mb-3">
                        🎟 {{ __('coupons::clients/common.my_coupon') }}
                        <span class="badge bg-primary">{{ method_exists($myCoupons, 'total') ? $myCoupons->total() : $myCoupons->count() }}</span>
                    </h4>

                    <div data-pagination-container="coupons-my">
                        @include('coupons::Clients.lists', ['coupons' => $myCoupons])

                        @if ($myCoupons->isEmpty())
                            <p class="text-muted">{{ __('coupons::clients/common.no_my_coupon') }}</p>
                        @else
                            <div class="mt-3">
                                {{ $myCoupons->links('students::clients.pagination.boostrap') }}
                            </div>
                        @endif
                    </div>
                </div>
            @endauth


            {{-- ================== 2. MÃ THEO KHÓA HỌC ================== --}}
            <div class="mb-5" data-pagination-scroll>
                <h4 class="mb-3">🎓 {{ __('coupons::clients/common.coupon_course') }}
                    <span class="badge bg-primary">{{ method_exists($courseCoupons, 'total') ? $courseCoupons->total() : $courseCoupons->count() }}</span>
                </h4>

                <div data-pagination-container="coupons-course">
                    @include('coupons::Clients.lists', ['coupons' => $courseCoupons])

                    @if ($courseCoupons->isEmpty())
                        <p class="text-muted">{{ __('coupons::clients/common.no_coupon_course') }}</p>
                    @else
                        <div class="mt-3">
                            {{ $courseCoupons->links('students::clients.pagination.boostrap') }}
                        </div>
                    @endif
                </div>
            </div>


            {{-- ================== 3. MÃ CHUNG ================== --}}
            <div data-pagination-scroll>
                <h4 class="mb-3">🌍 {{ __('coupons::clients/common.all_coupon') }}
                    <span class="badge bg-primary">{{ method_exists($publicCoupons, 'total') ? $publicCoupons->total() : $publicCoupons->count() }}</span>
                </h4>

                <div data-pagination-container="coupons-public">
                    @include('coupons::Clients.lists', ['coupons' => $publicCoupons])

                    @if ($publicCoupons->isEmpty())
                        <p class="text-muted">{{ __('coupons::clients/common.no_coupon') }}</p>
                    @else
                        <div class="mt-3">
                            {{ $publicCoupons->links('students::clients.pagination.boostrap') }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </section>
@endsection

@section('stylesheets')
    <style>
        .coupon-page {
            background:
                radial-gradient(circle at top right, rgba(37, 99, 235, 0.08), transparent 24%),
                linear-gradient(180deg, #f8fbff 0%, #f1f5f9 100%);
        }

        .coupon-page h4 {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px !important;
            color: #0f172a;
            font-weight: 700;
        }

        .coupon-page .badge.bg-primary {
            background: linear-gradient(135deg, #2563eb, #3b82f6) !important;
            border-radius: 999px;
            min-width: 34px;
        }

        .coupon-page .card {
            border: 1px solid rgba(203, 213, 225, 0.82);
            border-radius: 18px;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .coupon-page .card:hover {
            transform: translateY(-3px);
            border-color: rgba(96, 165, 250, 0.42);
            box-shadow: 0 16px 36px rgba(37, 99, 235, 0.12);
        }

        .coupon-page .card-body {
            padding: 1.25rem 1.15rem;
        }

        .coupon-page .card h5 {
            margin-bottom: 12px;
            color: #2563eb !important;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .coupon-page .card p {
            color: #334155;
        }

        .coupon-page .card strong {
            color: #0f172a;
        }

        .coupon-page .card .small,
        .coupon-page .card .text-muted {
            color: #64748b !important;
        }

        .coupon-page .card.opacity-50 {
            opacity: 0.72 !important;
        }

        .coupon-page .pagination {
            gap: 8px;
        }

        .coupon-page .page-link {
            border-radius: 14px !important;
            border: 1px solid rgba(203, 213, 225, 0.9);
            color: #334155;
            background: #fff;
            min-width: 40px;
            text-align: center;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
        }

        .coupon-page .page-item.active .page-link {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            border-color: transparent;
            color: #fff;
            box-shadow: 0 12px 28px rgba(37, 99, 235, 0.28);
        }

        .coupon-page .page-link:hover {
            color: #1d4ed8;
            border-color: rgba(96, 165, 250, 0.7);
            background: #eff6ff;
        }

        @media (max-width: 767.98px) {
            .coupon-page {
                padding-top: 2rem !important;
                padding-bottom: 2rem !important;
            }

            .coupon-page h4 {
                align-items: flex-start;
                flex-wrap: wrap;
                font-size: 1.1rem;
            }
        }

        html[data-theme="dark"] .coupon-page {
            background:
                radial-gradient(circle at top right, rgba(37, 99, 235, 0.12), transparent 24%),
                linear-gradient(180deg, #07111f 0%, #0a1628 100%);
        }

        html[data-theme="dark"] .coupon-page h4 {
            color: #f8fafc;
        }

        html[data-theme="dark"] .coupon-page .card {
            border-color: rgba(148, 163, 184, 0.14);
            background: linear-gradient(180deg, #0f1b2d 0%, #132238 100%);
            box-shadow: 0 18px 40px rgba(2, 6, 23, 0.22);
        }

        html[data-theme="dark"] .coupon-page .card:hover {
            border-color: rgba(96, 165, 250, 0.35);
            box-shadow: 0 22px 42px rgba(2, 6, 23, 0.28);
        }

        html[data-theme="dark"] .coupon-page .card h5 {
            color: #bfdbfe !important;
        }

        html[data-theme="dark"] .coupon-page .card p {
            color: #dbeafe;
        }

        html[data-theme="dark"] .coupon-page .card strong {
            color: #f8fafc;
        }

        html[data-theme="dark"] .coupon-page .card .small,
        html[data-theme="dark"] .coupon-page .card .text-muted {
            color: #9fb4cb !important;
        }

        html[data-theme="dark"] .coupon-page .page-link {
            border-color: rgba(148, 163, 184, 0.16);
            color: #dbeafe;
            background: rgba(15, 23, 42, 0.88);
            box-shadow: none;
        }

        html[data-theme="dark"] .coupon-page .page-link:hover {
            color: #fff;
            background: rgba(37, 99, 235, 0.16);
            border-color: rgba(96, 165, 250, 0.32);
        }
    </style>
@endsection
