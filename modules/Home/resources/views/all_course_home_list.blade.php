@forelse ($courseAll as $item)
    @php
        $thumbnail = $item->thumbnail
            ? (\Illuminate\Support\Str::startsWith($item->thumbnail, ['http://', 'https://']) ? $item->thumbnail : asset($item->thumbnail))
            : asset('clients/assets/banner-course.png');

        $teacherImage = teacherAvatarUrl($item->teacher);
        $isFlashSale = $item->isOnFlashSale;
        $isSaleActive = $item->sale_price_locale > 0 && (!$item->end_at || $item->end_at->isFuture());
    @endphp
    <div class="col-12 col-lg-6">
        <div class="course-card d-flex">
            <div class="course-thumb position-relative">
                <img src="{{ $thumbnail }}" alt="{{ $item->name_locale }}"
                    onerror="this.onerror=null;this.src='{{ asset('clients/assets/banner-course.png') }}';">
                @if ($isFlashSale)
                    <div class="flash-sale-badge">
                        <i class="fa-solid fa-bolt me-1"></i> FLASH SALE
                    </div>
                @endif
            </div>

            <div class="course-content">
                <div>
                    <div class="course-meta">
                        <span><i class="fa-solid fa-clock"></i> {{ getTime($item->durations) }}</span>
                        <span><i class="fa-solid fa-video"></i>
                            {{ getLessonCount($item)->module }} {{ __('home::common.portion') }} /
                            {{ getLessonCount($item)->lessons }} {{ __('home::common.lesson') }}
                        </span>
                        <span><i class="fa-solid fa-eye"></i>
                            {{ number_format($item->view ?? 0) }} {{ __('home::common.view') }}
                        </span>
                    </div>

                    <h5 class="course-title">
                        <a
                            href="{{ route('courses.detail', [
                                'locale' => app()->getLocale(),
                                'slug' => $item->slug_locale,
                            ]) }}">
                            {{ $item->name_locale }}
                        </a>
                    </h5>

                    <div class="course-teacher">
                        <img src="{{ $teacherImage }}" alt="{{ $item->teacher?->name_locale }}"
                            onerror="this.onerror=null;this.src='{{ asset('resources/assets/teacher.png') }}';">
                        <span>{{ $item->teacher->name_locale }}</span>
                    </div>

                    <div class="course-rating">
                        <i class="fa-solid fa-star"></i>
                        <strong>{{ $item->ratings_count > 0 ? number_format((float) $item->ratings_avg_rating, 1) : '0.0' }}</strong>
                        <span>({{ (int) ($item->ratings_count ?? 0) }})</span>
                    </div>

                    @if ($isFlashSale)
                        <div class="course-flash-sale-timer mt-2 mb-2">
                            <div class="countdown-timer d-flex align-items-center gap-1" data-time="{{ $item->end_at->toIso8601String() }}">
                                <div class="timer-label me-1"><i class="fa-solid fa-clock-rotate-left"></i></div>
                                <div class="time-box"><span class="days">00</span></div>
                                <div class="time-sep">:</div>
                                <div class="time-box"><span class="hours">00</span></div>
                                <div class="time-sep">:</div>
                                <div class="time-box"><span class="minutes">00</span></div>
                                <div class="time-sep">:</div>
                                <div class="time-box"><span class="seconds">00</span></div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="course-bottom">
                    <div class="course-price">
                        @if ($isSaleActive)
                            <span class="price-old me-2">{{ moneyLocale($item->price_locale) }}</span>
                            <span class="price-new">{{ moneyLocale($item->sale_price_locale) }}</span>
                        @else
                            <span class="price-new">{{ moneyLocale($item->price_locale) }}</span>
                        @endif
                    </div>

                    <a href="{{ route('courses.detail', [
                        'locale' => app()->getLocale(),
                        'slug' => $item->slug_locale,
                    ]) }}"
                        class="btn-view">
                        {{ __('home::common.detail') }} →
                    </a>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="col-12 text-center py-5">
        <p>{{ __('home::common.no_course') }}</p>
    </div>
@endforelse

<style>
    .flash-sale-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        background: linear-gradient(45deg, #f59e0b, #ef4444);
        color: white;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);
        z-index: 10;
        animation: pulse-red 2s infinite;
    }

    @keyframes pulse-red {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
        70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }

    .course-flash-sale-timer {
        background: rgba(245, 158, 11, 0.1);
        border-radius: 8px;
        padding: 6px 10px;
        display: inline-block;
        border: 1px solid rgba(245, 158, 11, 0.2);
    }

    .time-box {
        background: #f59e0b;
        color: white;
        padding: 2px 6px;
        border-radius: 4px;
        font-weight: 700;
        font-size: 0.85rem;
        min-width: 28px;
        text-align: center;
    }

    .time-sep {
        color: #f59e0b;
        font-weight: 700;
    }

    .timer-label {
        font-size: 0.75rem;
        color: #b45309;
        font-weight: 600;
    }
    
    html[data-theme="dark"] .course-flash-sale-timer {
        background: rgba(245, 158, 11, 0.05);
    }
    html[data-theme="dark"] .timer-label {
        color: #fbbf24;
    }
</style>
