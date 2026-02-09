@extends('layouts.client')

@section('title', 'Hết hạn thanh toán')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center align-items-center" style="min-height: 70vh">
            <div class="col-md-6 text-center">

                {{-- Image --}}
                <img src="{{ asset('clients/assets/expired.png') }}" alt="Payment Expired" class="img-fluid mb-4"
                    style="max-width: 300px; height: auto;">

                {{-- Title --}}
                <h2 class="fw-semibold mb-3 text-danger">
                    Thời gian thanh toán đã hết
                </h2>

                {{-- Message --}}
                <p class="lead text-muted mb-3">
                    Đơn hàng của bạn đã quá thời gian cho phép thanh toán.<br>
                    Vui lòng thực hiện lại quá trình thanh toán để tiếp tục.
                </p>

                {{-- Note --}}
                <div class="alert alert-warning d-inline-block mb-4">
                    ⏱ Thời gian giữ đơn thường là <strong>{{ config('checkout.checkout_countdown') }} phút</strong>.
                </div>

                {{-- Actions --}}
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="#" class="btn btn-warning px-4">
                        <i class="fas fa-redo"></i> Thanh toán lại
                    </a>

                    <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="btn btn-outline-secondary px-4">
                        <i class="fas fa-home"></i> {{ __('common.home') }}
                    </a>
                </div>

            </div>
        </div>
    </div>
@endsection
