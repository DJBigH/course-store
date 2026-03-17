@auth('students')
    <section class="foundation-course">
        <div class="container py-5">

            <div id="my-course-wrapper">
                <h3 class="section-title mb-4 text-success">🎓 {{ __('home::common.my_course') }}</h3>
                <div class="row g-4">
                    @if ($myCourse->count())
                        @foreach ($myCourse as $item)
                            <div class="col-12 col-lg-6">
                                <div class="course-card d-flex">
                                    <div class="course-thumb">
                                        <img src="{{ asset($item->thumbnail) }}" alt="">
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
                                            <img src="{{ $item->teacher->image }}" alt="">
                                            <span>{{ $item->teacher->name_locale }}</span>
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
