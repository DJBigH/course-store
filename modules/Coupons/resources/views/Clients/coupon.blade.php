@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    <section class="coupon-page py-5">
        <div class="container">

            {{-- ================== 1. MÃ CỦA TÔI ================== --}}
            @auth('students')
                <div class="mb-5">
                    <h4 class="mb-3">
                        🎟 Mã khuyến mãi của tôi
                        <span class="badge bg-primary">{{ $myCoupons->count() }}</span>
                    </h4>

                    @include('coupons::Clients.lists', ['coupons' => $myCoupons])

                    @if ($myCoupons->isEmpty())
                        <p class="text-muted">Bạn chưa có mã khuyến mãi riêng nào.</p>
                    @else
                        <div class="mt-3">
                            {{ $myCoupons->links('students::clients.pagination.boostrap') }}
                        </div>
                    @endif
                </div>
            @endauth


            {{-- ================== 2. MÃ THEO KHÓA HỌC ================== --}}
            <div class="mb-5">
                <h4 class="mb-3">🎓 Mã khuyến mãi theo khóa học
                    <span class="badge bg-primary">{{ $courseCoupons->count() }}</span>
                </h4>

                @include('coupons::Clients.lists', ['coupons' => $courseCoupons])

                @if ($courseCoupons->isEmpty())
                    <p class="text-muted">Hiện chưa có mã cho khóa học.</p>
                @else
                    <div class="mt-3">
                        {{ $courseCoupons->links('students::clients.pagination.boostrap') }}
                    </div>
                @endif
            </div>


            {{-- ================== 3. MÃ CHUNG ================== --}}
            <div>
                <h4 class="mb-3">🌍 Mã khuyến mãi chung
                    <span class="badge bg-primary">{{ $publicCoupons->count() }}</span>
                </h4>

                @include('coupons::Clients.lists', ['coupons' => $publicCoupons])

                @if ($publicCoupons->isEmpty())
                    <p class="text-muted">Không có mã khuyến mãi chung.</p>
                @else
                    <div class="mt-3">
                        {{ $publicCoupons->links('students::clients.pagination.boostrap') }}
                    </div>
                @endif
            </div>

        </div>
    </section>
@endsection
