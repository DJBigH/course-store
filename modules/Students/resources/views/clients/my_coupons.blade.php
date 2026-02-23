@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')
    <section class="coupon-section py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-3 mb-4">
                    <div class="account-sidebar">
                        @include('students::clients.menu')
                    </div>
                </div>

                <div class="col-lg-9">

                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">
                                🎟 {{ __('students::clients/account.coupons.title') }}
                            </h4>

                            <span class="badge bg-primary">
                                {{ $coupon->count() }} {{ __('students::clients/account.account.coupons_unit') }}
                            </span>
                        </div>

                        <div class="card-body">

                            @forelse ($coupon as $item)
                                @php
                                    $used = $item->usages_count ?? 0;
                                    $limited = !empty($item->count);
                                    $remaining = $limited ? max($item->count - $used, 0) : null;
                                @endphp

                                <div
                                    class="coupon-item d-flex justify-content-between align-items-center mb-3 p-3 border rounded
                                    {{ $limited && $remaining <= 0 ? 'opacity-50' : '' }}">

                                    {{-- Left --}}
                                    <div>
                                        <h5 class="mb-1 text-primary">
                                            {{ $item->code }}
                                        </h5>

                                        <div class="text-danger small">
                                            {{ __('students::clients/account.coupons.discount') }}
                                            @if ($item->discount_type === 'percent')
                                                {{ $item->discount_value }}%
                                            @else
                                                {{ money($item->discount_value) }}
                                            @endif
                                        </div>

                                        <div class="small">
                                            @if ($limited)
                                                {{ __('students::clients/account.coupons.remaining') }} {{ $remaining }} {{ __('students::clients/account.coupons.uses') }}
                                            @else
                                                {{ __('students::clients/account.coupons.no_use_limit') }}
                                            @endif
                                        </div>

                                        @php
                                            $now = \Carbon\Carbon::now();

                                            $startAt = $item->start_date ?? null;
                                            $endAt = $item->end_date ?? null;

                                            $daysLeft = $endAt
                                                ? $now->diffInDays(\Carbon\Carbon::parse($endAt), false)
                                                : null;
                                        @endphp

                                        <div class="small text-muted mt-1">
                                            <i class="fas fa-clock"></i>
                                            @if ($endAt)
                                                {{ __('students::clients/account.coupons.exp') }}:
                                                @if ($startAt)
                                                    {{ \Carbon\Carbon::parse($startAt)->format('d/m/Y') }}
                                                    →
                                                @endif
                                                {{ \Carbon\Carbon::parse($endAt)->format('d/m/Y') }}

                                                @if ($daysLeft >= 0)
                                                    <span class="text-success">
                                                        ({{ __('students::clients/account.coupons.remaining') }} {{ $daysLeft }} {{ __('students::clients/account.coupons.days') }})
                                                    </span>
                                                @else
                                                    <span class="text-danger">
                                                        ({{ __('students::clients/account.coupons.expired') }})
                                                    </span>
                                                @endif
                                            @else
                                                {{ __('students::clients/account.coupons.no_time_limit') }}
                                            @endif
                                        </div>

                                    </div>

                                    {{-- Right --}}
                                    <div class="text-end">
                                        @if ($limited && $remaining <= 0)
                                            <span class="badge bg-danger mb-2">{{ __('students::clients/account.coupons.no_remaining_uses') }}</span>
                                        @else
                                            <span class="badge bg-success mb-2">{{ __('students::clients/account.coupons.available') }}</span>
                                        @endif

                                        <div class="small text-muted">
                                            {{ __('students::clients/account.coupons.issued_at') }}
                                            {{ $item->pivot->created_at->format('d/m/Y') }}
                                        </div>
                                    </div>

                                </div>
                            @empty
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-ticket-alt fa-3x mb-3"></i>
                                    <p>{{ __('students::clients/account.coupons.empty') }}</p>
                                </div>
                            @endforelse

                        </div>
                    </div>
                    <div class="mt-2">
                        {{ $coupon->links('students::clients.pagination.boostrap') }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
