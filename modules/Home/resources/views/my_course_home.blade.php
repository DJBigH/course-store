@auth('students')
    <section class="foundation-course">
        <div class="container py-5">

            <div id="my-course-wrapper" data-pagination-container="home-my-courses" data-pagination-scroll>
                <h3 class="section-title mb-4 text-success">🎓 {{ __('home::common.my_course') }}</h3>
                <div class="row g-4">
                    @if ($myCourse->count())
                        @foreach ($myCourse as $item)
                            @php
                                $thumbnail = $item->thumbnail
                                    ? (\Illuminate\Support\Str::startsWith($item->thumbnail, ['http://', 'https://']) ? $item->thumbnail : asset($item->thumbnail))
                                    : asset('clients/assets/banner-course.png');

                                $teacherImage = $item->teacher?->image
                                    ? (\Illuminate\Support\Str::startsWith($item->teacher->image, ['http://', 'https://']) ? $item->teacher->image : asset($item->teacher->image))
                                    : asset('clients/assets/course-teacher.png');
                            @endphp
                            <div class="col-12 col-lg-6">
                                <div class="course-card d-flex">
                                    <div class="course-thumb">
                                        <img src="{{ $thumbnail }}" alt="{{ $item->name_locale }}"
                                            onerror="this.onerror=null;this.src='{{ asset('clients/assets/banner-course.png') }}';">
                                    </div>

                                    <div class="course-content">
                                        <div class="course-meta">
                                            <span><i class="fa-solid fa-clock"></i> {{ getTime($item->durations) }}</span>
                                            <span>
                                                <i class="fa-solid fa-video"></i>
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
                                                onerror="this.onerror=null;this.src='{{ asset('clients/assets/course-teacher.png') }}';">
                                            <span>{{ $item->teacher->name_locale }}</span>
                                        </div>

                                        <div class="course-rating">
                                            <i class="fa-solid fa-star"></i>
                                            <strong>{{ $item->ratings_count > 0 ? number_format((float) $item->ratings_avg_rating, 1) : '0.0' }}</strong>
                                            <span>({{ (int) ($item->ratings_count ?? 0) }})</span>
                                        </div>

                                        <div class="course-price">
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
                        @endforeach
                    @else
                        <div class="col-12 text-center py-5">
                            <p class="text-muted">{{ __('home::common.no_my_course') }}</p>
                        </div>
                    @endif
                </div>

                <div class="mt-3">
                    {{ $myCourse->links() }}
                </div>
            </div>

        </div>
    </section>
@endauth
@section('stylesheets')
    <style>
        .foundation-course {
            background: #f8fafc;
        }

        .section-title {
            font-weight: 700;
            color: #1f2937;
        }

        .course-card {
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
            transition: all .3s ease;
        }

        .course-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .course-thumb {
            width: 180px;
            flex-shrink: 0;
        }

        .course-thumb img {
            width: 180px;
            height: 180px;
            /* object-fit: cover; */
        }

        .course-content {
            padding: 16px 18px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .course-meta {
            display: flex;
            gap: 12px;
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .course-meta i {
            margin-right: 4px;
            color: #2563eb;
        }

        .course-title a {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
            text-decoration: none;
            line-height: 1.4;
        }

        .course-title a:hover {
            color: #2563eb;
        }

        .course-teacher {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
        }

        .course-teacher img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }

        .course-teacher span {
            font-size: 14px;
            color: #374151;
        }

        .course-price {
            margin-top: 12px;
        }

        .course-rating {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 10px;
            color: #b45309;
            font-size: 14px;
        }

        .course-rating i {
            color: #f59e0b;
        }

        .price-old {
            text-decoration: line-through;
            color: #9ca3af;
            margin-right: 10px;
        }

        .price-new {
            font-size: 18px;
            font-weight: 700;
            color: #dc2626;
        }

        @media (max-width: 768px) {
            .course-card {
                flex-direction: column;
                border-radius: 18px;
            }

            .course-thumb {
                width: 100%;
            }

            .course-thumb img {
                width: 100%;
                height: 220px;
                object-fit: cover;
                object-position: center;
            }
        }

        @media (max-width: 575.98px) {
            .foundation-course .container {
                padding-top: 2rem !important;
                padding-bottom: 2rem !important;
            }

            .section-title {
                font-size: 1.2rem;
                line-height: 1.4;
            }

            .row.g-4 {
                --bs-gutter-y: 1rem;
            }

            .course-card {
                border-radius: 20px;
                box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
            }

            .course-content {
                padding: 14px 14px 16px;
                gap: 12px;
            }

            .course-thumb img {
                height: 188px;
            }

            .course-meta {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-bottom: 0;
                font-size: 11px;
            }

            .course-meta span {
                display: inline-flex;
                align-items: center;
                padding: 6px 10px;
                border-radius: 999px;
                background: #eff6ff;
                color: #475569;
                line-height: 1.3;
            }

            .course-title a {
                font-size: 15px;
                line-height: 1.45;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .course-teacher {
                margin-top: 0;
            }

            .course-teacher span {
                font-size: 13px;
            }

            .course-price {
                width: 100%;
                margin-top: 0;
            }

            .course-price .btn-view {
                width: 100%;
                text-align: center;
                padding: 10px 14px;
                border-radius: 14px;
            }
        }

        html[data-theme="dark"] .foundation-course {
            background: #0a1628;
        }

        html[data-theme="dark"] .foundation-course .section-title,
        html[data-theme="dark"] .foundation-course h3 {
            background: linear-gradient(135deg, #132238 0%, #1d3557 100%);
            color: #e5eef9 !important;
            border: 1px solid rgba(148, 163, 184, 0.16);
            border-radius: 18px;
            box-shadow: 0 16px 36px rgba(2, 6, 23, 0.24);
        }

        html[data-theme="dark"] .foundation-course .course-card {
            background: linear-gradient(180deg, #0f1b2d 0%, #132238 100%);
            border: 1px solid rgba(148, 163, 184, 0.16);
            box-shadow: 0 14px 34px rgba(2, 6, 23, 0.28);
        }

        html[data-theme="dark"] .foundation-course .course-card:hover {
            box-shadow: 0 20px 44px rgba(2, 6, 23, 0.36);
        }

        html[data-theme="dark"] .foundation-course .course-title a {
            color: #e5eef9;
        }

        html[data-theme="dark"] .foundation-course .course-meta,
        html[data-theme="dark"] .foundation-course .course-teacher span,
        html[data-theme="dark"] .foundation-course .text-muted {
            color: #9fb4cb !important;
        }

        html[data-theme="dark"] .foundation-course .course-meta span {
            background: rgba(96, 165, 250, 0.1);
            color: #c7d5e8;
        }

        html[data-theme="dark"] .foundation-course .btn-view {
            color: #93c5fd;
            border-color: rgba(147, 197, 253, 0.38);
            background: rgba(96, 165, 250, 0.08);
        }

        html[data-theme="dark"] .foundation-course .btn-view:hover {
            background: #2563eb;
            color: #eff6ff;
        }
    </style>
@endsection
