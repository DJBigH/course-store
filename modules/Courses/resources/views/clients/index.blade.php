@extends('layouts.client')
@section('content')
    @include('part.clients.page_title')
    <section class="all-course" data-pagination-scroll>
        <div class="container" data-pagination-container="courses-index">
            <form method="GET" class="course-index-toolbar js-smooth-filter mt-4" data-filter-block-target="courses-index-list">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-5">
                        <label class="form-label">{{ __('courses::clients/common.search_label') }}</label>
                        <input
                            type="text"
                            name="keyword"
                            value="{{ $searchKeyword ?? '' }}"
                            class="form-control"
                            placeholder="{{ __('courses::clients/common.search_placeholder') }}">
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label">{{ __('courses::clients/common.rating_filter_label') }}</label>
                        <select name="rating_min" class="form-select" onchange="this.dispatchEvent(new Event('submit', {bubbles: true}))">
                            <option value="" @selected(($ratingMin ?? '') === '')>{{ __('courses::clients/common.rating_filter_all') }}</option>
                            <option value="4" @selected(($ratingMin ?? '') === '4')>{{ __('courses::clients/common.rating_filter_4_plus') }}</option>
                            <option value="4.5" @selected(($ratingMin ?? '') === '4.5')>{{ __('courses::clients/common.rating_filter_45_plus') }}</option>
                        </select>
                    </div>
                    <div class="col-lg-4">
                        <label class="form-label">{{ __('courses::clients/common.sort_label') }}</label>
                        <select name="sort" class="form-select" onchange="this.dispatchEvent(new Event('submit', {bubbles: true}))">
                            <option value="latest" @selected(($sort ?? 'latest') === 'latest')>{{ __('courses::clients/common.sort_latest') }}</option>
                            <option value="rating_desc" @selected(($sort ?? 'latest') === 'rating_desc')>{{ __('courses::clients/common.sort_rating_desc') }}</option>
                            <option value="rating_asc" @selected(($sort ?? 'latest') === 'rating_asc')>{{ __('courses::clients/common.sort_rating_asc') }}</option>
                        </select>
                    </div>
                </div>
            </form>

            <div data-filter-block="courses-index-list">
                @if ($courses && $courses->count())
                    <div class="row">
                        @foreach ($courses as $course)
                            @php
                                $thumbnail = $course->thumbnail
                                    ? (\Illuminate\Support\Str::startsWith($course->thumbnail, ['http://', 'https://']) ? $course->thumbnail : asset($course->thumbnail))
                                    : asset('clients/assets/banner-course.png');

                                $teacherImage = teacherAvatarUrl($course->teacher);
                                $teacherBadge = $course->teacher?->primary_badge;

                            @endphp
                            <div class="col-12 col-lg-6 mb-4">
                                <div class="d-flex course">
                                    <div class="banner-course">
                                        <img src="{{ $thumbnail }}" alt="{{ $course->name_locale }}"
                                            onerror="this.onerror=null;this.src='{{ asset('clients/assets/banner-course.png') }}';" />
                                    </div>

                                    <div class="descreption-course">
                                        <div class="descreption-top">
                                            <p><i class="fa-solid fa-clock"></i> {{ getTime($course->durations) }}</p>
                                            <p><i class="fa-solid fa-video"></i> {{ getLessonCount($course)->module }}
                                                {{ __('courses::clients/common.portion') }}/{{ getLessonCount($course)->lessons }}
                                                {{ __('courses::clients/common.poem') }}</p>
                                            <p><i class="fa-solid fa-eye"></i>
                                                {{ $course->view ? number_format($course->view) : 0 }}
                                                {{ __('courses::clients/common.view') }}</p>
                                        </div>

                                        <div class="descreption-meta">
                                            <p>
                                                <i class="fa-solid fa-calendar-check"></i>
                                                {{ __('courses::clients/common.updated_at') }}:
                                                <span>{{ format_date_dmy($course->updated_at) }}</span>
                                            </p>

                                            <p>
                                                <i class="fa-solid fa-users"></i>
                                                {{ number_format($course->students_count ?? 0) }}
                                                {{ __('courses::clients/common.students') }}
                                            </p>

                                            <p class="course-rating-inline">
                                                <i class="fa-solid fa-star"></i>
                                                {{ $course->ratings_count > 0 ? number_format((float) $course->ratings_avg_rating, 1) : '0.0' }}
                                                <span>({{ (int) ($course->ratings_count ?? 0) }})</span>
                                            </p>
                                        </div>

                                        <h5 class="descreption-title">
                                                <a
                                                    href="{{ route('courses.detail', ['locale' => app()->getLocale(), 'slug' => $course->slug_locale]) }}">
                                                    {{ $course->name_locale }}
                                                </a>
                                        </h5>


                                        <div class="descreption-teacher">
                                            <img src="{{ $teacherImage }}" alt="{{ $course->teacher?->name_locale }}"
                                                onerror="this.onerror=null;this.src='{{ asset('resources/assets/teacher.png') }}';" />
                                            <span class="course-teacher-meta">
                                                <strong
                                                    style="font-weight: bold">{{ __('courses::clients/common.instructor') }}:</strong>
                                                {{ $course->teacher?->name_locale }}
                                                @if ($teacherBadge)
                                                    <span class="course-teacher-badge course-teacher-badge--{{ $teacherBadge['tone'] }}">{{ $teacherBadge['label'] }}</span>
                                                @endif
                                            </span>
                                        </div>

                                        <p class="descreption-price">
                                            @if ($course->sale_price)
                                                <span class="sale">{{ moneyLocale($course->price) }}</span>
                                                <span>{{ moneyLocale($course->sale_price) }}</span>
                                            @else
                                                <span>{{ moneyLocale($course->price) }}</span>
                                            @endif
                                        </p>

                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3">
                        {{ $courses->links() }}
                    </div>
                @else
                    <div class="empty-course text-center py-5">
                        {{-- <img src="{{ asset('clients/assets/empty.webm') }}" alt="Không có khóa học" class="mb-4"
                            width="220"> --}}
                        <video src="{{ asset('clients/assets/empty.webm') }}" autoplay loop muted class="mb-4"
                            width="220"></video>

                        <h4 class="fw-bold mb-2">{{ __('courses::clients/common.empty_page_title') }}</h4>

                        <p class="text-muted mb-4">
                            {{ __('courses::clients/common.empty_description') }}
                            <br>
                            {{ __('courses::clients/common.empty_suggestion') }} 🚀
                        </p>

                        <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="btn btn-primary px-4">
                            <i class="fa-solid fa-book-open me-1"></i>
                            {{ __('courses::clients/common.explore_courses') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </section>

@endsection

@section('stylesheets')
    <style>
        .all-course {
            padding: 1px 0 48px;
            background: #f8fafc;
        }

        .course-index-toolbar {
            padding: 1rem;
            border-radius: 14px;
            background: #f8fafc;
            box-shadow: inset 0 0 0 1px #e5e7eb;
        }

        .course-index-toolbar .form-label {
            color: #334155;
            font-weight: 700;
        }

        .course-index-toolbar .form-control,
        .course-index-toolbar .form-select {
            border-color: #cbd5e1;
            background: #ffffff;
            color: #0f172a;
        }

        .course-index-toolbar .form-control::placeholder {
            color: #94a3b8;
        }

        html[data-theme="dark"] .all-course {
            background:
                radial-gradient(circle at top center, rgba(37, 99, 235, 0.12), transparent 26%),
                linear-gradient(180deg, #081120 0%, #0b1527 100%);
        }

        html[data-theme="dark"] .course-index-toolbar {
            background: rgba(15, 23, 42, 0.88);
            box-shadow: inset 0 0 0 1px rgba(96, 165, 250, 0.18);
        }

        html[data-theme="dark"] .course-index-toolbar .form-label {
            color: #dbeafe;
        }

        html[data-theme="dark"] .course-index-toolbar .form-control,
        html[data-theme="dark"] .course-index-toolbar .form-select {
            border-color: rgba(96, 165, 250, 0.22);
            background: rgba(11, 19, 36, 0.92);
            color: #f8fafc;
        }

        html[data-theme="dark"] .course-index-toolbar .form-control::placeholder {
            color: #94a3b8;
        }

        .empty-course {
            background: #f8fafc;
            border-radius: 14px;
            padding: 60px 20px;
            box-shadow: inset 0 0 0 1px #e5e7eb;
        }

        .empty-course video {
            display: inline-block;
            opacity: 0.9;
            border-radius: 12px;
        }

        .empty-course h4 {
            color: #111827;
        }

        .empty-course p {
            font-size: 15px;
        }

        .course-teacher-meta {
            display: inline-flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.45rem;
        }

        .course-teacher-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.22rem 0.55rem;
            border-radius: 999px;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            border: 1px solid transparent;
        }

        .course-teacher-badge--blue { background: rgba(59, 130, 246, 0.12); color: #1d4ed8; border-color: rgba(59, 130, 246, 0.18); }
        .course-teacher-badge--gold { background: rgba(245, 158, 11, 0.14); color: #b45309; border-color: rgba(245, 158, 11, 0.18); }
        .course-teacher-badge--emerald { background: rgba(16, 185, 129, 0.14); color: #047857; border-color: rgba(16, 185, 129, 0.18); }
        .course-teacher-badge--violet { background: rgba(139, 92, 246, 0.14); color: #7c3aed; border-color: rgba(139, 92, 246, 0.18); }
        .course-teacher-badge--rose { background: rgba(244, 63, 94, 0.14); color: #e11d48; border-color: rgba(244, 63, 94, 0.18); }
        .course-teacher-badge--slate { background: rgba(100, 116, 139, 0.14); color: #334155; border-color: rgba(100, 116, 139, 0.18); }

        html[data-theme="dark"] .empty-course {
            background: rgba(15, 23, 42, 0.88);
            box-shadow: inset 0 0 0 1px rgba(96, 165, 250, 0.18);
        }

        html[data-theme="dark"] .empty-course h4 {
            color: #f8fafc;
        }

        html[data-theme="dark"] .empty-course p {
            color: #cbd5e1 !important;
        }

        @media (max-width: 575.98px) {
            .empty-course {
                padding: 40px 16px;
            }

            .empty-course video {
                width: min(100%, 180px);
            }
        }
    </style>
@endsection
