@extends('layouts.client')

@section('content')
    <section class="teacher-profile-premium py-5">
        <div class="container">
            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/">{{ __('teacher::public.home') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('teacher.portal.index', ['locale' => app()->getLocale()]) }}">{{ __('teacher::public.teachers') }}</a></li>
                    <li class="breadcrumb-item active">{{ $teacher->name_locale }}</li>
                </ol>
            </nav>

            <div class="row g-4">
                {{-- Left Side: Teacher Main Info --}}
                <div class="col-lg-8">
                    {{-- Hero Section --}}
                    <div class="profile-hero-card p-4 p-md-5 rounded-5 shadow-lg border-0 mb-4 overflow-hidden position-relative">
                        <div class="hero-blur-bg"></div>
                        <div class="row align-items-center position-relative">
                            <div class="col-md-auto text-center text-md-start mb-4 mb-md-0">
                                <div class="avatar-wrapper shadow-premium">
                                    <img src="{{ teacherAvatarUrl($teacher) }}" alt="{{ $teacher->name_locale }}" class="avatar-img rounded-circle">
                                    @if($teacher->is_verified_badge)
                                        <div class="verified-tick" title="Verified Teacher">
                                            <i class="fas fa-check"></i>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md">
                                <div class="d-flex flex-wrap gap-2 mb-3 justify-content-center justify-content-md-start">
                                    @foreach ($teacher->badge_labels as $badge)
                                        <span class="badge-premium badge-{{ $badge['key'] }}" 
                                              style="@if($badge['tone'] === 'custom') background-color: {{ $badge['color_bg'] }}; color: {{ $badge['color_text'] }}; @endif">
                                            <i class="{{ $badge['icon'] ?? 'fas fa-award' }} me-1"></i>
                                            {{ $badge['label'] }}
                                        </span>
                                    @endforeach
                                </div>
                                <h1 class="teacher-name h2 fw-bold mb-2 text-center text-md-start">{{ $teacher->name_locale }}</h1>
                                <p class="teacher-headline text-muted mb-4 text-center text-md-start">{{ $teacher->headline ?? __('teacher::public.default_headline') }}</p>
                                
                                <div class="stats-grid d-flex flex-wrap gap-4 justify-content-center justify-content-md-start">
                                    <div class="stat-item">
                                        <span class="stat-value">{{ number_format($totalStudents) }}</span>
                                        <span class="stat-label">{{ __('teacher::public.total_students') }}</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-value">{{ $totalRatings }}</span>
                                        <span class="stat-label">{{ __('teacher::public.reviews_count') }}</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-value">{{ $courses->count() }}</span>
                                        <span class="stat-label">{{ __('teacher::public.courses_count') }}</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-value">{{ $totalLessons }}</span>
                                        <span class="stat-label">{{ __('teacher::public.total_lessons') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- About Section --}}
                    <div class="content-card p-4 p-md-5 rounded-5 shadow-sm border mb-4">
                        <h3 class="section-title h4 fw-bold mb-4">
                            <i class="fas fa-user-tie text-primary me-2"></i> {{ __('teacher::public.about_title') }}
                        </h3>
                        <div class="bio-content rich-text">
                            {!! $teacher->description_locale !!}
                        </div>
                    </div>

                    {{-- Courses Section --}}
                    <div class="content-card p-4 p-md-5 rounded-5 shadow-sm border mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <h3 class="section-title h4 fw-bold mb-0">
                                <i class="fas fa-graduation-cap text-primary me-2"></i> {{ __('teacher::public.courses_by_teacher') }}
                            </h3>
                        </div>
                        
                        <div class="courses-list">
                            @forelse ($courses as $course)
                                <a href="{{ route('courses.detail', ['locale' => app()->getLocale(), 'slug' => $course->slug_locale]) }}" class="course-horizontal-card mb-3 text-decoration-none">
                                    <div class="row g-0 align-items-center">
                                        <div class="col-sm-4 col-md-3">
                                            <div class="course-thumb-container">
                                                <img src="{{ $course->thumbnail }}" alt="{{ $course->name_locale }}" class="course-thumb">
                                                @php
                                                    $price = $course->price_locale;
                                                    $salePrice = $course->sale_price_locale;
                                                    $hasRealSale = $salePrice > 0 && $salePrice < $price;
                                                @endphp
                                                @if($hasRealSale)
                                                    <div class="course-sale-badge">{{ __('teacher::public.on_sale') }}</div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-sm-8 col-md-9">
                                            <div class="p-3">
                                                <h4 class="course-name h6 fw-bold mb-2">{{ $course->name_locale }}</h4>
                                                <div class="course-meta d-flex flex-wrap gap-3 mb-2 small text-muted">
                                                    <span><i class="far fa-play-circle me-1"></i> {{ $course->lessons_count }} {{ __('teacher::public.lessons') }}</span>
                                                    <span><i class="far fa-user me-1"></i> {{ number_format($course->students_count) }} {{ __('teacher::public.students') }}</span>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-2">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="course-stars text-warning small">
                                                            @php $rating = (float) $course->ratings_avg_rating; @endphp
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <i class="{{ $i <= $rating ? 'fas' : ($i - 0.5 <= $rating ? 'fas fa-star-half-alt' : 'far') }} fa-star"></i>
                                                            @endfor
                                                        </div>
                                                        <span class="fw-bold small text-dark">{{ number_format($rating, 1) }}</span>
                                                        <span class="small text-muted">({{ $course->ratings_count }})</span>
                                                    </div>

                                                    <div class="course-pricing text-end">
                                                        @php
                                                            $price = $course->price_locale;
                                                            $salePrice = $course->sale_price_locale;
                                                            $hasSale = $salePrice > 0 && $salePrice < $price;
                                                            $displayPrice = $hasSale ? $salePrice : $price;
                                                        @endphp
                                                        
                                                        @if($displayPrice > 0)
                                                            @if($hasSale)
                                                                <span class="text-muted small text-decoration-line-through me-2">{{ number_format($price, 0) }}{{ $course->currency_symbol }}</span>
                                                            @endif
                                                            <span class="fw-black text-primary fs-5">{{ number_format($displayPrice, 0) }}{{ $course->currency_symbol }}</span>
                                                        @else
                                                            <span class="badge bg-success-soft text-success fw-bold">{{ __('teacher::public.free') }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="text-center py-5">
                                    <img src="/assets/img/empty-courses.svg" alt="No courses" style="width: 120px;" class="mb-3 opacity-50">
                                    <p class="text-muted">{{ __('teacher::public.no_courses_found') }}</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Right Side: Stats & Rating Interaction --}}
                <div class="col-lg-4">
                    <div class="sticky-sidebar">
                        {{-- Expert Highlights --}}
                        <div class="content-card p-4 rounded-5 shadow-sm border mb-4 bg-gradient-premium text-white overflow-hidden position-relative">
                            <div class="decoration-circle"></div>
                            <h4 class="h5 fw-bold mb-3 position-relative">{{ __('teacher::public.expert_highlights') }}</h4>
                            <ul class="list-unstyled mb-0 position-relative">
                                <li class="mb-3 d-flex gap-3">
                                    <div class="highlight-icon"><i class="fas fa-clock"></i></div>
                                    <div>
                                        <div class="fw-bold">{{ $teacher->exp }} {{ __('teacher::public.years') }}</div>
                                        <div class="small opacity-75">{{ __('teacher::public.professional_exp') }}</div>
                                    </div>
                                </li>
                                <li class="mb-3 d-flex gap-3">
                                    <div class="highlight-icon"><i class="fas fa-certificate"></i></div>
                                    <div>
                                        <div class="fw-bold">{{ __('teacher::public.verified_expert') }}</div>
                                        <div class="small opacity-75">{{ __('teacher::public.verified_desc') }}</div>
                                    </div>
                                </li>
                                <li class="d-flex gap-3">
                                    <div class="highlight-icon"><i class="fas fa-users"></i></div>
                                    <div>
                                        <div class="fw-bold">{{ number_format($totalStudents) }}+ {{ __('teacher::public.active_students') }}</div>
                                        <div class="small opacity-75">{{ __('teacher::public.student_community') }}</div>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        {{-- Rating Summary Card --}}
                        <div class="content-card p-4 rounded-5 shadow-sm border mb-4">
                            <h4 class="h5 fw-bold mb-4">{{ __('teacher::public.teacher_rating') }}</h4>
                            <div class="text-center mb-4">
                                <div class="display-1 fw-black text-primary mb-2">{{ number_format($teacher->ratings_avg_rating, 1) }}</div>
                                <div class="rating-stars mb-2">
                                    @php $avg = (float) $teacher->ratings_avg_rating; @endphp
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="{{ $i <= $avg ? 'fas' : ($i - 0.5 <= $avg ? 'fas fa-star-half-alt' : 'far') }} fa-star text-warning fa-lg"></i>
                                    @endfor
                                </div>
                                <div class="small text-muted">{{ __('teacher::public.total_reviews', ['count' => $totalRatings]) }}</div>
                            </div>

                            <div class="star-bars mb-4">
                                @foreach($starDistribution as $star => $data)
                                    <div class="star-bar-item d-flex align-items-center gap-2 mb-2">
                                        <div class="star-label small fw-bold" style="width: 20px;">{{ $star }}</div>
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar bg-warning" style="width: {{ $data['percent'] }}%"></div>
                                        </div>
                                        <div class="star-percent small text-muted" style="width: 35px;">{{ round($data['percent']) }}%</div>
                                    </div>
                                @endforeach
                            </div>

                            @if($canRateTeacher)
                                <div class="separator-text mb-4"><span>{{ __('teacher::public.rate_this_teacher') }}</span></div>
                                <div id="teacher-rating-wrap">
                                    @include('teacher::clients.partials.rating_panel', [
                                        'teacher' => $teacher,
                                        'canRateTeacher' => $canRateTeacher,
                                        'viewerTeacherRating' => $viewerTeacherRating,
                                    ])
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('stylesheets')
    <style>
        :root {
            --premium-primary: #4f46e5;
            --premium-secondary: #0ea5e9;
            --premium-accent: #f59e0b;
            --card-bg: #ffffff;
            --card-border: rgba(0, 0, 0, 0.06);
            --text-main: #1e293b;
            --text-muted: #64748b;
            --premium-grad: linear-gradient(135deg, #4f46e5, #0ea5e9);
        }

        html[data-theme="dark"] {
            --card-bg: #1e293b;
            --card-border: rgba(255, 255, 255, 0.1);
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
        }

        .teacher-profile-premium {
            background-color: var(--card-bg);
            color: var(--text-main);
            transition: all 0.3s ease;
        }

        .content-card {
            background-color: var(--card-bg);
            border-color: var(--card-border) !important;
        }

        .profile-hero-card {
            background: #f8fafc;
            border: 1px solid var(--card-border) !important;
        }

        html[data-theme="dark"] .profile-hero-card {
            background: #0f172a;
        }

        .hero-blur-bg {
            position: absolute;
            top: -50%;
            right: -20%;
            width: 400px;
            height: 400px;
            background: var(--premium-primary);
            filter: blur(120px);
            opacity: 0.08;
            z-index: 1;
        }

        .avatar-wrapper {
            position: relative;
            display: inline-block;
            padding: 8px;
            background: #fff;
            border-radius: 50%;
        }

        html[data-theme="dark"] .avatar-wrapper {
            background: #334155;
        }

        .avatar-img {
            width: 160px;
            height: 160px;
            object-fit: cover;
            border: 4px solid var(--card-bg);
        }

        .verified-tick {
            position: absolute;
            bottom: 12px;
            right: 12px;
            background: #22c55e;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #fff;
            font-size: 14px;
        }

        .badge-premium {
            display: inline-flex;
            align-items: center;
            padding: 6px 14px;
            border-radius: 99px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .badge-verified { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
        .badge-premium { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .badge-top_seller { background: rgba(79, 70, 229, 0.1); color: #4f46e5; }

        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--premium-primary);
        }

        .stat-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 600;
            color: var(--text-muted);
            letter-spacing: 0.05em;
        }

        .course-horizontal-card {
            display: block;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .course-horizontal-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.06);
            border-color: var(--premium-primary);
        }

        .course-thumb-container {
            position: relative;
            padding-top: 75%;
            overflow: hidden;
        }

        .course-thumb {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .course-sale-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #ef4444;
            color: white;
            font-size: 0.65rem;
            font-weight: 800;
            padding: 4px 8px;
            border-radius: 6px;
            z-index: 2;
        }

        .bg-gradient-premium {
            background: var(--premium-grad);
        }

        .highlight-icon {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .decoration-circle {
            position: absolute;
            top: -20px;
            right: -20px;
            width: 100px;
            height: 100px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }

        .sticky-sidebar {
            position: sticky;
            top: 100px;
        }

        .rich-text {
            line-height: 1.8;
            font-size: 1.05rem;
        }

        .separator-text {
            display: flex;
            align-items: center;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .separator-text::before,
        .separator-text::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid var(--card-border);
        }

        .separator-text span {
            padding: 0 15px;
        }

        .progress {
            background-color: rgba(0,0,0,0.05);
            border-radius: 99px;
        }

        html[data-theme="dark"] .progress {
            background-color: rgba(255,255,255,0.1);
        }

        .fw-black {
            font-weight: 900;
        }

        .bg-success-soft {
            background-color: rgba(34, 197, 94, 0.1);
        }

        .teacher-public-rating__picker {
            position: relative;
            user-select: none;
            padding: 10px 0;
        }

        .teacher-public-rating__track {
            position: relative;
            display: inline-block;
            cursor: pointer;
            font-size: 2rem;
            line-height: 1;
        }

        .teacher-public-rating__stars-base {
            color: #e2e8f0;
            display: flex;
            gap: 4px;
        }

        .teacher-public-rating__stars-fill {
            position: absolute;
            top: 0;
            left: 0;
            white-space: nowrap;
            overflow: hidden;
            color: #f59e0b;
            display: flex;
            gap: 4px;
            transition: width 0.1s ease;
            pointer-events: none;
        }

        .teacher-public-rating__hotspots {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
        }

        .teacher-public-rating__hotspot {
            flex: 1;
            background: transparent;
            border: none;
            padding: 0;
            margin: 0;
            outline: none;
            cursor: pointer;
        }

        html[data-theme="dark"] .teacher-public-rating__stars-base {
            color: #334155;
        }

        @media (max-width: 767.98px) {
            .avatar-img {
                width: 120px;
                height: 120px;
            }
            .stat-value {
                font-size: 1.25rem;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const ratingWrap = document.getElementById('teacher-rating-wrap');
            if (!ratingWrap) return;

            // Rating Star Picker Interaction
            const initRatingPicker = (container) => {
                const form = container.querySelector('[data-teacher-rating-form]');
                if (!form) return;

                const track = container.querySelector('[data-rating-track]');
                const fill = container.querySelector('[data-rating-fill]');
                const input = container.querySelector('[data-rating-input]');
                const label = container.querySelector('[data-rating-current-label]');
                const options = container.querySelectorAll('[data-rating-option]');
                const submitBtn = form.querySelector('button[type="submit"]');

                let selectedRating = null;

                options.forEach(opt => {
                    opt.addEventListener('mouseenter', () => updateStars(parseFloat(opt.dataset.value)));
                    opt.addEventListener('click', () => {
                        selectedRating = parseFloat(opt.dataset.value);
                        input.value = selectedRating;
                        updateStars(selectedRating);
                        if (label) {
                            label.textContent = `{{ __('teacher::public.rating_selected_label') }} ${selectedRating} sao`;
                            label.classList.remove('text-muted');
                            label.classList.add('text-primary', 'fw-bold');
                        }
                    });
                });

                track.addEventListener('mouseleave', () => updateStars(selectedRating || 0));

                function updateStars(val) {
                    fill.style.width = (val / 5 * 100) + '%';
                }

                form.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    if (!selectedRating) {
                        alert('{{ __('teacher::public.rating_hint') }}');
                        return;
                    }

                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>{{ __('teacher::public.rating_submit') }}';

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ rating: selectedRating }),
                        });

                        const result = await response.json();
                        if (!response.ok || !result.success) {
                            alert(result.message || 'Unable to save rating.');
                            submitBtn.disabled = false;
                            submitBtn.textContent = '{{ __('teacher::public.rating_submit') }}';
                            return;
                        }

                        ratingWrap.innerHTML = result.html;
                        // Trigger a reload to refresh global averages and stats
                        setTimeout(() => window.location.reload(), 1000);
                    } catch (error) {
                        console.error('Rating Error:', error);
                        alert('An error occurred while saving your rating.');
                        submitBtn.disabled = false;
                    }
                });
            };

            initRatingPicker(ratingWrap);
        });
    </script>
@endsection

