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
