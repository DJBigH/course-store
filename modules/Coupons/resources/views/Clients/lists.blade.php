<div class="row">
    @foreach ($coupons as $item)
        @php
            $used = $item->usages_aaa ?? 0;
            $limited = !empty($item->count);
            $remaining = $limited ? max($item->count - $used, 0) : null;

            $now = \Carbon\Carbon::now();
            $startAt = $item->start_date ? \Carbon\Carbon::parse($item->start_date) : null;
            $endAt = $item->end_date ? \Carbon\Carbon::parse($item->end_date) : null;
            $daysLeft = $endAt ? $now->diffInDays($endAt, false) : null;
        @endphp

        <div class="col-md-6 col-lg-4 mb-3">
            <div
                class="card h-100 shadow-sm
                {{ ($limited && $remaining <= 0) || ($endAt && $daysLeft < 0) ? 'opacity-50' : '' }}">

                <div class="card-body">
                    <h5 class="text-primary">{{ $item->code }}</h5>

                    <p class="mb-1">
                        {{ __('coupons::clients/common.sale') }}:
                        <strong>
                            @if ($item->discount_type === 'percent')
                                {{ $item->discount_value }}%
                            @else
                                {{ money($item->discount_value) }}
                            @endif
                        </strong>
                    </p>

                    @if ($item->courses->isNotEmpty())
                        <p class="small text-danger mb-1">
                            <i class="fas fa-book"></i>
                            {{ __('coupons::clients/common.applicable_to') }}:
                            <strong>
                                {{ __('coupons::clients/common.course') }}
                                {{ $item->courses->pluck('name')->join(', ') }}
                            </strong>
                        </p>
                    @endif


                    <p class="small mb-1">
                        @if ($limited)
                            {{ __('coupons::clients/common.still') }} {{ $remaining }}
                            {{ __('coupons::clients/common.turn') }}
                        @else
                            {{ __('coupons::clients/common.no_limit') }}
                        @endif
                    </p>

                    <p class="small text-muted mb-2">
                        <i class="fas fa-clock"></i>
                        @if ($endAt)
                            {{ __('coupons::clients/common.expiry_date') }}
                            : {{ $startAt->format('d/m/Y') }}->{{ $endAt->format('d/m/Y') }}
                            @if ($daysLeft >= 0)
                                ({{ __('coupons::clients/common.still') }} {{ $daysLeft }} {{ __('coupons::clients/common.date') }})
                            @else
                                ({{ __('coupons::clients/common.end') }})
                            @endif
                        @else
                            {{ __('coupons::clients/common.no_limit_time') }}
                        @endif
                    </p>

                    @if ($endAt && $daysLeft < 0)
                        <span class="badge bg-secondary">{{ __('coupons::clients/common.expired') }}</span>
                    @elseif ($limited && $remaining <= 0)
                        <span class="badge bg-danger">{{ __('coupons::clients/common.time_up') }}</span>
                    @else
                        <span class="badge bg-success">{{ __('coupons::clients/common.still_available') }}</span>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
