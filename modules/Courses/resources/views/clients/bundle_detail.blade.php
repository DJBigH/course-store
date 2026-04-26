@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    <section class="bundle-detail-page py-5">
        <div class="container">
            @if (session('msg'))
                <div class="alert alert-{{ session('msgType', 'info') }} border-0 mb-4">
                    {{ session('msg') }}
                </div>
            @endif

            <div class="row g-4 align-items-start">
                <div class="col-lg-8">
                    <div class="bundle-detail-card bundle-detail-card--hero">
                        <span class="bundle-detail-kicker">{{ __('courses::teacher/messages.bundles.public.kicker') }}</span>
                        <h1 class="bundle-detail-title">{{ $bundle->name }}</h1>
                        <p class="bundle-detail-description">
                            {{ $bundle->description ?: __('courses::teacher/messages.bundles.public.default_description') }}
                        </p>

                        <div class="bundle-detail-teacher">
                            <strong>{{ __('courses::clients/common.instructor') }}:</strong>
                            <a href="{{ route('teacher.public.show', ['locale' => app()->getLocale(), 'slug' => $bundle->teacher?->slug_locale ?: $bundle->teacher?->slug]) }}">
                                {{ $bundle->teacher?->name_locale ?: ($bundle->teacher?->name ?? 'Teacher') }}
                            </a>
                        </div>
                    </div>

                    <div class="bundle-detail-card">
                        <div class="bundle-detail-section-head">
                            <h3>{{ __('courses::teacher/messages.bundles.public.included_courses') }}</h3>
                            <span>{{ __('courses::teacher/messages.bundles.labels.course_count', ['count' => $courses->count()]) }}</span>
                        </div>

                        @auth('students')
                            @if ($hasOwnedCourses)
                                <div class="alert alert-info border-0 bundle-detail-adjusted-alert">
                                    @if ($allCoursesOwned)
                                        {{ __('courses::teacher/messages.bundles.flash.all_courses_owned') }}
                                    @else
                                        {{ __('courses::teacher/messages.bundles.public.adjusted_notice', [
                                            'owned' => $ownedCourseIds->count(),
                                            'remaining' => $remainingCourses->count(),
                                        ]) }}
                                    @endif
                                </div>
                            @endif
                        @endauth

                        <div class="bundle-detail-course-list">
                            @foreach ($courses as $course)
                                @php
                                    $coursePrice = $course->sale_price && $course->sale_price > 0 ? $course->sale_price : $course->price;
                                @endphp
                                <article class="bundle-detail-course-item">
                                    <img src="{{ $course->thumbnail }}" alt="{{ $course->name_locale }}" class="bundle-detail-course-thumb">
                                    <div class="bundle-detail-course-body">
                                        <h4>
                                            <a href="{{ route('courses.detail', ['locale' => app()->getLocale(), 'slug' => $course->slug_locale ?: $course->slug]) }}">
                                                {{ $course->name_locale }}
                                            </a>
                                        </h4>
                                        <div class="bundle-detail-course-meta">
                                            <span>{{ getTime($course->durations) }}</span>
                                            <span>{{ __('courses::clients/common.students') }}: {{ number_format($course->students_count ?? 0) }}</span>
                                            @if ($ownedCourseIds->contains($course->id))
                                                <span class="bundle-detail-owned">{{ __('courses::teacher/messages.bundles.public.already_owned') }}</span>
                                            @endif
                                        </div>
                                        <strong>{{ moneyLocale($coursePrice) }}</strong>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="bundle-detail-card bundle-detail-card--sticky">
                        @if ($bundle->thumbnail)
                            <img src="{{ $bundle->thumbnail }}" alt="{{ $bundle->name }}" class="bundle-detail-cover">
                        @endif

                        <div class="bundle-detail-pricing">
                            <div>
                                <small>{{ __('courses::teacher/messages.bundles.public.bundle_price') }}</small>
                                <strong>{{ moneyLocale($bundle->price) }}</strong>
                            </div>
                            @auth('students')
                                <div>
                                    <small>{{ __('courses::teacher/messages.bundles.public.current_price') }}</small>
                                    <strong class="bundle-detail-current-price">{{ moneyLocale($payableAmount) }}</strong>
                                </div>
                            @endauth
                            <div class="bundle-detail-saved">
                                <small>{{ __('courses::teacher/messages.bundles.public.separate_total') }}</small>
                                <span>{{ moneyLocale($sourceTotal) }}</span>
                            </div>
                            @auth('students')
                                @if ($hasOwnedCourses && !$allCoursesOwned)
                                    <div class="bundle-detail-saved">
                                        <small>{{ __('courses::teacher/messages.bundles.public.owned_value') }}</small>
                                        <span>{{ moneyLocale($ownedValue) }}</span>
                                    </div>
                                @endif
                            @endauth

                            @if ($bundle->quantity !== null)
                                <div class="bundle-detail-stock mt-2">
                                    <span class="badge {{ $bundle->quantity > 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} border py-2 px-3 rounded-pill w-100 text-center">
                                        <i class="fa-solid fa-boxes-stacked me-1"></i>
                                        @if ($bundle->quantity > 0)
                                            Còn lại: <strong>{{ $bundle->quantity }}</strong> suất cuối cùng
                                        @else
                                            Rất tiếc, đã hết suất đăng ký combo này
                                        @endif
                                    </span>
                                </div>
                            @endif

                            @if ($bundle->end_at)
                                <div class="bundle-detail-deadline mt-2">
                                    <small class="text-danger fw-bold"><i class="fa-solid fa-hourglass-end me-1"></i> Hạn đăng ký:</small>
                                    <span class="text-danger">{{ $bundle->end_at->format('d/m/Y H:i') }}</span>
                                    @if ($bundle->end_at->isFuture())
                                        <div class="sale-countdown-mini mt-1" data-end="{{ $bundle->end_at->toIso8601String() }}">
                                            <small class="text-muted">Kết thúc sau: <span class="days">0</span>d <span class="hours">0</span>h <span class="minutes">0</span>m</small>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        @php
                            $isExpired = $bundle->end_at && $bundle->end_at->isPast();
                            $isSoldOut = $bundle->quantity !== null && $bundle->quantity <= 0;
                            $isComingSoon = $bundle->is_coming_soon && $bundle->coming_soon_start_at && $bundle->coming_soon_start_at->isFuture();
                            $canBuy = !$isExpired && !$isSoldOut && !$isComingSoon;
                        @endphp

                        @if ($isComingSoon)
                            <div class="coming-soon-wrapper mb-3 text-center p-3 rounded bg-warning-subtle border border-warning">
                                <h6 class="text-warning-emphasis fw-bold mb-2">
                                    <i class="fa-solid fa-clock-rotate-left"></i> {{ __('courses::clients/common.coming_soon') }}
                                </h6>
                                <div class="countdown-timer d-flex justify-content-center gap-2" 
                                     data-time="{{ $bundle->coming_soon_start_at->toIso8601String() }}">
                                    <div class="time-item"><span class="days">00</span><small>D</small></div>
                                    <div class="time-item"><span class="hours">00</span><small>H</small></div>
                                    <div class="time-item"><span class="minutes">00</span><small>M</small></div>
                                    <div class="time-item"><span class="seconds">00</span><small>S</small></div>
                                </div>
                            </div>
                        @endif

                        @if ($allCoursesOwned)
                            <div class="alert alert-warning border-0 mb-3">
                                {{ __('courses::teacher/messages.bundles.flash.all_courses_owned') }}
                            </div>
                        @elseif ($isExpired)
                            <div class="alert alert-danger border-0 mb-3 text-center fw-bold">
                                <i class="fa-solid fa-circle-xmark"></i> Combo này đã hết hạn đăng ký!
                            </div>
                        @elseif ($isSoldOut)
                            <div class="alert alert-danger border-0 mb-3 text-center fw-bold">
                                <i class="fa-solid fa-ban"></i> Đã hết lượt đăng ký cho combo này!
                            </div>
                        @endif

                        @if (!$allCoursesOwned)
                            @auth('students')
                                <form action="{{ route('courses.bundle.create', ['locale' => app()->getLocale()]) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="bundle_id" value="{{ $bundle->id }}">
                                    <button class="btn btn-primary w-100 bundle-detail-buy-btn" {{ !$canBuy ? 'disabled' : '' }}>
                                        @if ($isComingSoon)
                                            Chờ ngày ra mắt
                                        @elseif ($isSoldOut)
                                            Đã hết lượt bán
                                        @elseif ($isExpired)
                                            Đã hết hạn bán
                                        @else
                                            {{ $hasOwnedCourses ? __('courses::teacher/messages.bundles.public.buy_remaining') : __('courses::teacher/messages.bundles.public.buy_now') }}
                                        @endif
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('clients-login', ['locale' => app()->getLocale()]) }}" class="btn btn-primary w-100 bundle-detail-buy-btn" {{ !$canBuy ? 'disabled' : '' }}>
                                    {{ __('courses::teacher/messages.bundles.public.login_to_buy') }}
                                </a>
                            @endauth
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('stylesheets')
    <style>
        .bundle-detail-page { background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%); }
        .bundle-detail-card { background: #fff; border: 1px solid rgba(148, 163, 184, 0.16); border-radius: 24px; padding: 1.5rem; box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08); }
        .bundle-detail-card + .bundle-detail-card { margin-top: 1.25rem; }
        .bundle-detail-card--hero { background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.1), transparent 28%), linear-gradient(180deg, #ffffff 0%, #f8fbff 100%); }
        .bundle-detail-kicker { display: inline-flex; padding: 0.45rem 0.8rem; border-radius: 999px; background: rgba(37, 99, 235, 0.12); color: #1d4ed8; font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; }
        .bundle-detail-title { margin: 1rem 0 0.75rem; font-size: clamp(2rem, 4vw, 3rem); font-weight: 900; color: #0f172a; }
        .bundle-detail-description, .bundle-detail-teacher, .bundle-detail-course-meta { color: #475569; line-height: 1.75; }
        .bundle-detail-section-head { display: flex; justify-content: space-between; gap: 1rem; align-items: center; margin-bottom: 1rem; }
        .bundle-detail-section-head h3 { margin: 0; color: #0f172a; font-weight: 800; }
        .bundle-detail-adjusted-alert { line-height: 1.7; }
        .bundle-detail-course-list { display: grid; gap: 1rem; }
        .bundle-detail-course-item { display: flex; gap: 1rem; align-items: flex-start; padding: 1rem; border-radius: 18px; border: 1px solid #e2e8f0; background: #f8fafc; }
        .bundle-detail-course-thumb { width: 120px; height: 78px; object-fit: cover; border-radius: 16px; flex-shrink: 0; }
        .bundle-detail-course-body { flex: 1; min-width: 0; }
        .bundle-detail-course-body h4 { margin: 0 0 0.4rem; font-size: 1.05rem; }
        .bundle-detail-course-body h4 a { color: #0f172a; text-decoration: none; }
        .bundle-detail-course-body strong { display: inline-block; margin-top: 0.5rem; color: #dc2626; }
        .bundle-detail-course-meta { display: flex; flex-wrap: wrap; gap: 0.75rem; font-size: 0.92rem; }
        .bundle-detail-owned { color: #0f766e; font-weight: 700; }
        .bundle-detail-card--sticky { position: sticky; top: 24px; }
        .bundle-detail-cover { width: 100%; border-radius: 20px; margin-bottom: 1rem; object-fit: cover; }
        .bundle-detail-pricing { display: grid; gap: 0.85rem; margin-bottom: 1rem; }
        .bundle-detail-pricing small, .bundle-detail-saved small { display: block; color: #64748b; }
        .bundle-detail-pricing strong { font-size: 2rem; color: #0f172a; line-height: 1; }
        .bundle-detail-current-price { color: #0f766e !important; }
        .bundle-detail-saved span { font-weight: 700; color: #1d4ed8; }
        .bundle-detail-buy-btn { min-height: 52px; font-weight: 700; }
        @media (max-width: 991.98px) {
            .bundle-detail-card--sticky { position: static; }
        }
        @media (max-width: 575.98px) {
            .bundle-detail-course-item { flex-direction: column; }
            .bundle-detail-course-thumb { width: 100%; height: 180px; }
            .bundle-detail-section-head { flex-direction: column; align-items: flex-start; }
        }
        .countdown-timer .time-item {
            background: #fff;
            padding: 5px;
            border-radius: 8px;
            min-width: 45px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .countdown-timer .time-item span {
            display: block;
            font-size: 1.1rem;
            font-weight: 800;
            color: #d97706;
            line-height: 1;
        }
        .countdown-timer .time-item small {
            font-size: 9px;
            text-transform: uppercase;
            color: #92400e;
            font-weight: 700;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const countdown = () => {
                const timerEl = document.querySelector('.countdown-timer');
                if (!timerEl) return;

                const targetDate = new Date(timerEl.dataset.time).getTime();
                const update = () => {
                    const now = new Date().getTime();
                    const diff = targetDate - now;

                    if (diff <= 0) {
                        window.location.reload();
                        return;
                    }

                    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                    timerEl.querySelector('.days').innerText = String(days).padStart(2, '0');
                    timerEl.querySelector('.hours').innerText = String(hours).padStart(2, '0');
                    timerEl.querySelector('.minutes').innerText = String(minutes).padStart(2, '0');
                    timerEl.querySelector('.seconds').innerText = String(seconds).padStart(2, '0');
                };

                update();
                setInterval(update, 1000);
            };

            const saleCountdownMini = () => {
                const els = document.querySelectorAll('.sale-countdown-mini');
                els.forEach(el => {
                    const targetDate = new Date(el.dataset.end).getTime();
                    const update = () => {
                        const now = new Date().getTime();
                        const diff = targetDate - now;
                        if (diff <= 0) {
                            window.location.reload();
                            return;
                        }
                        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                        el.querySelector('.days').innerText = days;
                        el.querySelector('.hours').innerText = hours;
                        el.querySelector('.minutes').innerText = minutes;
                    };
                    update();
                    setInterval(update, 60000);
                });
            };

            countdown();
            saleCountdownMini();
        });
    </script>
@endsection
