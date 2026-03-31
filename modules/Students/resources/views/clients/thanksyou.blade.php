@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    <section class="thankyou-page py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-7 col-md-9">
                    <div class="thankyou-content bg-white p-5 text-center shadow rounded-4">

                        <div class="mb-4">
                            <span
                                class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10"
                                style="width: 110px; height: 110px;">
                                {{-- <img src="{{ asset('clients/assets/success.webm') }}" alt="" class="img-fluid" /> --}}
                                <video src="{{ asset('clients/assets/success.webm') }}" autoplay loop muted
                                    class="img-fluid"></video>
                            </span>
                        </div>

                        <h2 class="fw-bold mb-3">
                            {{ __('students::clients/thankyou.title') }} 🎉
                        </h2>

                        <p class="text-muted mb-4">
                            {{ __('students::clients/thankyou.description') }}
                        </p>

                        {{-- Nếu có mã đơn --}}
                        @isset($order)
                            <div class="alert alert-success d-inline-block px-4 py-2 mb-4">
                                <strong>{{ __('students::clients/thankyou.order_code') }}:</strong> {{ $order->code }}
                            </div>
                        @endisset

                        <div class="thankyou-actions d-flex justify-content-center gap-3 mt-4">
                            <a href="{{ route('home', ['locale' => app()->getLocale()]) }}"
                                class="btn btn-primary px-4 thankyou-action-btn">
                                <i class="fa-solid fa-house me-1"></i> {{ __('students::clients/thankyou.home') }}
                            </a>

                            @if (!empty($order))
                                <a href="{{ route('students.account.order-detail', [
                                    'locale' => app()->getLocale(),
                                    'id' => $order->id,
                                ]) }}"
                                    class="btn btn-outline-secondary px-4 thankyou-action-btn">
                                    <i class="fa-solid fa-receipt me-1"></i>
                                    {{ __('students::clients/thankyou.view_order') }}
                                </a>
                            @endif

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('styles')
    <style>
        .thankyou-content {
            animation: fadeUp 0.6s ease;
        }

        .thankyou-actions {
            flex-wrap: wrap;
        }

        .thankyou-action-btn {
            min-height: 50px;
            padding: 0.9rem 1.25rem !important;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-weight: 700;
            text-align: center;
            white-space: nowrap;
        }

        @media (max-width: 575.98px) {
            .thankyou-content {
                padding: 1.5rem !important;
            }

            .thankyou-actions {
                flex-direction: column;
                gap: 12px !important;
            }

            .thankyou-action-btn {
                width: 100%;
                min-height: 48px;
                padding: 0.85rem 1rem !important;
                border-radius: 14px;
                font-size: 0.95rem;
                flex-direction: row;
            }

            .thankyou-action-btn i {
                margin-right: 0 !important;
            }
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endsection
