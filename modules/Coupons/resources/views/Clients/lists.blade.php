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
                        Giảm:
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
                            Áp dụng cho:
                            <strong>
                                Khóa học {{ $item->courses->pluck('name')->join(', ') }}
                            </strong>
                        </p>
                    @endif


                    <p class="small mb-1">
                        @if ($limited)
                            Còn {{ $remaining }} lượt
                        @else
                            Không giới hạn lượt
                        @endif
                    </p>

                    <p class="small text-muted mb-2">
                        <i class="fas fa-clock"></i>
                        @if ($endAt)
                            HSD: {{ $startAt->format('d/m/Y') }}->{{ $endAt->format('d/m/Y') }}
                            @if ($daysLeft >= 0)
                                (còn {{ $daysLeft }} ngày)
                            @else
                                (đã hết hạn)
                            @endif
                        @else
                            Không giới hạn thời gian
                        @endif
                    </p>

                    @if ($endAt && $daysLeft < 0)
                        <span class="badge bg-secondary">Hết hạn</span>
                    @elseif ($limited && $remaining <= 0)
                        <span class="badge bg-danger">Hết lượt</span>
                    @else
                        <span class="badge bg-success">Còn dùng</span>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
