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
                            Đặt hàng thành công 🎉
                        </h2>

                        <p class="text-muted mb-4">
                            Cảm ơn bạn đã tin tưởng và đặt hàng.
                            Đơn hàng của bạn đang được xử lý, chúng tôi sẽ gửi email xác nhận trong thời gian sớm nhất.
                        </p>

                        {{-- Nếu có mã đơn --}}
                        @isset($order)
                            <div class="alert alert-success d-inline-block px-4 py-2 mb-4">
                                <strong>Mã đơn hàng:</strong> {{ $order->code }}
                            </div>
                        @endisset

                        <div class="d-flex justify-content-center gap-3 mt-4">
                            <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="btn btn-primary px-4">
                                <i class="fa-solid fa-house me-1"></i> Trang chủ
                            </a>

                            @if (!empty($order))
                                <a href="{{ route('students.account.order-detail', [
                                    'locale' => app()->getLocale(),
                                    'id' => $order->id,
                                ]) }}"
                                    class="btn btn-outline-secondary px-4">
                                    <i class="fa-solid fa-receipt me-1"></i> Xem đơn hàng
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
