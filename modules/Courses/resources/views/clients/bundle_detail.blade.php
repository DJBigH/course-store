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
                        <span class="bundle-detail-kicker">{{ __('teacher::dashboard.bundles.public.kicker') }}</span>
                        <h1 class="bundle-detail-title">{{ $bundle->name }}</h1>
                        <p class="bundle-detail-description">
                            {{ $bundle->description ?: __('teacher::dashboard.bundles.public.default_description') }}
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
                            <h3>{{ __('teacher::dashboard.bundles.public.included_courses') }}</h3>
                            <span>{{ __('teacher::dashboard.bundles.labels.course_count', ['count' => $courses->count()]) }}</span>
                        </div>

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
                                                <span class="bundle-detail-owned">{{ __('teacher::dashboard.bundles.public.already_owned') }}</span>
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
                                <small>{{ __('teacher::dashboard.bundles.public.bundle_price') }}</small>
                                <strong>{{ moneyLocale($bundle->price) }}</strong>
                            </div>
                            <div class="bundle-detail-saved">
                                <small>{{ __('teacher::dashboard.bundles.public.separate_total') }}</small>
                                <span>{{ moneyLocale($sourceTotal) }}</span>
                            </div>
                        </div>

                        @auth('students')
                            @if ($hasOwnedCourses)
                                <div class="alert alert-warning border-0 mb-3">
                                    {{ __('teacher::dashboard.bundles.flash.purchase_blocked_owned') }}
                                </div>
                            @else
                                <form action="{{ route('courses.bundle.create', ['locale' => app()->getLocale()]) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="bundle_id" value="{{ $bundle->id }}">
                                    <button class="btn btn-primary w-100 bundle-detail-buy-btn">
                                        {{ __('teacher::dashboard.bundles.public.buy_now') }}
                                    </button>
                                </form>
                            @endif
                        @else
                            <a href="{{ route('clients-login', ['locale' => app()->getLocale()]) }}" class="btn btn-primary w-100 bundle-detail-buy-btn">
                                {{ __('teacher::dashboard.bundles.public.login_to_buy') }}
                            </a>
                        @endauth
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
    </style>
@endsection
