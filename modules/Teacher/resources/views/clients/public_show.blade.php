@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    <section class="teacher-public-page">
        <div class="container">
            <div class="teacher-public-shell">
                <div class="teacher-public-hero">
                    <div class="teacher-public-hero__main">
                        <div class="teacher-public-hero__avatar">
                            <img src="{{ teacherAvatarUrl($teacher) }}" alt="{{ $teacher->name_locale }}">
                        </div>
                        <div>
                            <span class="teacher-public-hero__eyebrow">{{ __('teacher::public.hero_eyebrow') }}</span>
                            <h2 class="teacher-public-hero__name">{{ $teacher->name_locale }}</h2>
                            <div class="teacher-public-hero__meta">
                                <span><i class="fa-solid fa-briefcase me-2"></i>{{ $teacher->exp }} {{ __('teacher::public.experience_years') }}</span>
                                <span><i class="fa-solid fa-book-open me-2"></i>{{ __('teacher::public.course_count', ['count' => $courses->count()]) }}</span>
                            </div>
                        </div>
                    </div>

                    <div id="teacher-rating-wrap">
                        @include('teacher::clients.partials.rating_panel', [
                            'teacher' => $teacher,
                            'canRateTeacher' => $canRateTeacher,
                            'viewerTeacherRating' => $viewerTeacherRating,
                        ])
                    </div>
                </div>

                <div class="teacher-public-card mt-4">
                    <h3>{{ __('teacher::public.about_title') }}</h3>
                    <div class="teacher-public-card__content">
                        {!! $teacher->description_locale !!}
                    </div>
                </div>

                <div class="teacher-public-card mt-4">
                    <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center mb-3">
                        <div>
                            <h3 class="mb-1">{{ __('teacher::public.course_section_title') }}</h3>
                            <p class="mb-0 text-muted">{{ __('teacher::public.course_section_description') }}</p>
                        </div>
                    </div>

                    <div class="row g-3">
                        @forelse ($courses as $course)
                            <div class="col-md-6">
                                <article class="teacher-public-course">
                                    <img src="{{ $course->thumbnail }}" alt="{{ $course->name_locale }}" class="teacher-public-course__thumb">
                                    <div class="teacher-public-course__body">
                                        <h4>{{ $course->name_locale }}</h4>
                                        <div class="teacher-public-course__rating">
                                            <i class="fa-solid fa-star"></i>
                                            <strong>{{ $course->ratings_count > 0 ? number_format((float) $course->ratings_avg_rating, 1) : '0.0' }}</strong>
                                            <span>{{ __('teacher::public.rating_count', ['count' => (int) $course->ratings_count]) }}</span>
                                        </div>
                                        <a href="{{ route('courses.detail', ['locale' => app()->getLocale(), 'slug' => $course->slug_locale]) }}" class="btn btn-outline-primary btn-sm mt-3">
                                            {{ __('teacher::public.view_course') }}
                                        </a>
                                    </div>
                                </article>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info mb-0">{{ __('teacher::public.no_courses') }}</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('stylesheets')
    <style>
        .teacher-public-page {
            padding-bottom: 48px;
        }
        .teacher-public-shell {
            display: grid;
            gap: 1.25rem;
        }
        .teacher-public-hero,
        .teacher-public-card {
            padding: 1.5rem;
            border-radius: 24px;
            border: 1px solid rgba(148, 163, 184, 0.18);
            background: rgba(255, 255, 255, 0.92);
            box-shadow: 0 20px 42px rgba(15, 23, 42, 0.08);
        }
        .teacher-public-hero {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(320px, 0.8fr);
            gap: 1.25rem;
            align-items: start;
        }
        .teacher-public-hero__main {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        .teacher-public-hero__avatar img {
            width: 104px;
            height: 104px;
            border-radius: 28px;
            object-fit: cover;
            box-shadow: 0 16px 34px rgba(37, 99, 235, 0.18);
        }
        .teacher-public-hero__eyebrow,
        .teacher-public-rating__eyebrow {
            display: inline-flex;
            align-items: center;
            padding: 0.42rem 0.78rem;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.1);
            color: #1d4ed8;
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .teacher-public-hero__name {
            margin: 0.85rem 0 0.55rem;
            font-size: clamp(2rem, 3vw, 2.7rem);
            font-weight: 900;
            color: #0f172a;
        }
        .teacher-public-hero__meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            color: #475569;
        }
        .teacher-public-rating__summary {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: start;
        }
        .teacher-public-rating__stars,
        .teacher-public-course__rating {
            color: #f59e0b;
            display: flex;
            align-items: center;
            gap: 0.35rem;
            flex-wrap: wrap;
        }
        .teacher-public-rating__score {
            text-align: right;
        }
        .teacher-public-rating__score strong {
            display: block;
            font-size: 1.8rem;
            color: #0f172a;
            line-height: 1;
        }
        .teacher-public-rating__picker {
            display: block;
        }
        .teacher-public-rating__track {
            position: relative;
            width: min(100%, 270px);
            padding: 0.9rem 1rem;
            border: 1px solid rgba(245, 158, 11, 0.22);
            background: linear-gradient(180deg, #ffffff, #fff7ed);
            border-radius: 18px;
            box-shadow: 0 10px 24px rgba(245, 158, 11, 0.1);
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease, background 0.18s ease;
        }
        .teacher-public-rating__track:hover,
        .teacher-public-rating__track.is-preview {
            transform: translateY(-2px) scale(1.03);
            border-color: rgba(245, 158, 11, 0.45);
            background: linear-gradient(180deg, #fff7ed, #ffedd5);
            box-shadow: 0 14px 24px rgba(245, 158, 11, 0.16);
        }
        .teacher-public-rating__track.is-active {
            background: linear-gradient(135deg, #f59e0b, #f97316);
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: 0 16px 28px rgba(249, 115, 22, 0.28);
        }
        .teacher-public-rating__stars-base,
        .teacher-public-rating__stars-fill {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 1.75rem;
            line-height: 1;
            white-space: nowrap;
        }
        .teacher-public-rating__stars-base {
            color: #cbd5e1;
        }
        .teacher-public-rating__stars-fill {
            position: absolute;
            inset: 0.9rem auto auto 1rem;
            overflow: hidden;
            color: #f59e0b;
            pointer-events: none;
            transition: width 0.16s ease;
        }
        .teacher-public-rating__track.is-active .teacher-public-rating__stars-base {
            color: rgba(255, 255, 255, 0.32);
        }
        .teacher-public-rating__track.is-active .teacher-public-rating__stars-fill {
            color: #fff8e1;
        }
        .teacher-public-rating__hotspots {
            position: absolute;
            inset: 0;
            display: grid;
            grid-template-columns: repeat(10, 1fr);
            z-index: 2;
        }
        .teacher-public-rating__hotspot {
            border: 0;
            background: transparent;
            padding: 0;
            margin: 0;
            cursor: pointer;
        }
        .teacher-public-rating__hotspot:focus-visible {
            outline: 2px solid rgba(249, 115, 22, 0.6);
            outline-offset: -3px;
        }
        .teacher-public-card h3 {
            color: #0f172a;
            font-size: 1.4rem;
            font-weight: 800;
            margin-bottom: 0.9rem;
        }
        .teacher-public-card__content {
            color: #334155;
            line-height: 1.75;
        }
        .teacher-public-course {
            display: flex;
            gap: 1rem;
            border: 1px solid rgba(148, 163, 184, 0.14);
            border-radius: 20px;
            background: #f8fafc;
            padding: 1rem;
            height: 100%;
        }
        .teacher-public-course__thumb {
            width: 120px;
            height: 90px;
            object-fit: cover;
            border-radius: 16px;
            flex: 0 0 120px;
        }
        .teacher-public-course__body h4 {
            font-size: 1.05rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 0.55rem;
        }
        @media (max-width: 991.98px) {
            .teacher-public-hero {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 575.98px) {
            .teacher-public-hero__main,
            .teacher-public-course {
                flex-direction: column;
            }
            .teacher-public-course__thumb {
                width: 100%;
                height: 180px;
                flex-basis: auto;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const ratingWrap = document.getElementById('teacher-rating-wrap');
            if (!ratingWrap) {
                return;
            }

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const updateRatingVisual = (form, value, state = 'idle') => {
                if (!form) {
                    return;
                }

                const track = form.querySelector('[data-rating-track]');
                const fill = form.querySelector('[data-rating-fill]');
                const numericValue = Math.max(0, Math.min(Number(value || 0), 5));

                if (fill) {
                    fill.style.width = `${numericValue * 20}%`;
                }

                if (track) {
                    track.classList.toggle('is-preview', state === 'preview' && numericValue > 0);
                    track.classList.toggle('is-active', numericValue > 0);
                }
            };

            ratingWrap.addEventListener('click', (event) => {
                const option = event.target.closest('[data-rating-option]');
                if (!option) {
                    return;
                }

                const form = option.closest('form');
                if (!form) {
                    return;
                }

                form.querySelector('[data-rating-input]').value = option.dataset.value;
                updateRatingVisual(form, option.dataset.value, 'selected');

                const label = form.querySelector('[data-rating-current-label]');
                if (label) {
                    label.textContent = @js(__('teacher::public.rating_selected_label')) + ' ' + option.dataset.value;
                }
            });

            ratingWrap.addEventListener('mouseover', (event) => {
                const option = event.target.closest('[data-rating-option]');
                if (!option) {
                    return;
                }

                const form = option.closest('form');
                if (!form) {
                    return;
                }

                updateRatingVisual(form, option.dataset.value, 'preview');

                const label = form.querySelector('[data-rating-current-label]');
                if (label) {
                    label.textContent = @js(__('teacher::public.rating_selected_label')) + ' ' + option.dataset.value;
                }
            });

            ratingWrap.addEventListener('mouseout', (event) => {
                const form = event.target.closest('[data-teacher-rating-form]');
                if (!form) {
                    return;
                }

                if (event.relatedTarget && form.contains(event.relatedTarget)) {
                    return;
                }

                const selectedValue = form.querySelector('[data-rating-input]')?.value || '';
                updateRatingVisual(form, selectedValue, selectedValue ? 'selected' : 'idle');

                const label = form.querySelector('[data-rating-current-label]');
                if (label) {
                    label.textContent = selectedValue
                        ? @js(__('teacher::public.rating_selected_label')) + ' ' + selectedValue
                        : @js(__('teacher::public.rating_hint'));
                }
            });

            ratingWrap.addEventListener('focusin', (event) => {
                const option = event.target.closest('[data-rating-option]');
                if (!option) {
                    return;
                }

                const form = option.closest('form');
                if (!form) {
                    return;
                }

                updateRatingVisual(form, option.dataset.value, 'preview');
            });

            ratingWrap.addEventListener('focusout', (event) => {
                const form = event.target.closest('[data-teacher-rating-form]');
                if (!form) {
                    return;
                }

                if (event.relatedTarget && form.contains(event.relatedTarget)) {
                    return;
                }

                const selectedValue = form.querySelector('[data-rating-input]')?.value || '';
                updateRatingVisual(form, selectedValue, selectedValue ? 'selected' : 'idle');
            });

            ratingWrap.addEventListener('submit', async (event) => {
                const form = event.target.closest('[data-teacher-rating-form]');
                if (!form) {
                    return;
                }

                event.preventDefault();

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: new FormData(form),
                    });

                    const result = await response.json();
                    if (!response.ok || !result.success) {
                        alert(result.message || 'Unable to save rating.');
                        return;
                    }

                    ratingWrap.innerHTML = result.html;
                } catch (error) {
                    alert('Unable to save rating.');
                }
            });
        });
    </script>
@endsection
