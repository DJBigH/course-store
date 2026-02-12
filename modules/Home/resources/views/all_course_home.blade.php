<section class="foundation-course">
    <div class="container py-5">

        <h3 class="section-title mb-4 text-danger">
            {{ auth('students')->check()
                ? '🎓 ' . __('home::common.all_course_for_you')
                : '📚 ' . __('home::common.all_course_home') }}

        </h3>

        <div class="row g-4">
            @forelse ($courseAll as $item)
                <div class="col-12 col-lg-6">
                    <div class="course-card d-flex">
                        <div class="course-thumb">
                            <img src="{{ asset($item->thumbnail) }}" alt="">
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
                                            'slug' => $item->slug,
                                        ]) }}">
                                        {{ $item->name }}
                                    </a>
                                </h5>

                                <div class="course-teacher">
                                    <img src="{{ $item->teacher->image }}">
                                    <span>{{ $item->teacher->name }}</span>
                                </div>
                            </div>

                            <div class="course-bottom">
                                <div class="course-price">
                                    <span class="price-old">{{ moneyLocale($item->price) }}</span>
                                    <span class="price-new">{{ moneyLocale($item->sale_price) }}</span>
                                </div>

                                <a href="{{ route('courses.detail', [
                                    'locale' => app()->getLocale(),
                                    'slug' => $item->slug,
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
        </div>

        {{-- NÚT XEM THÊM --}}
        @if ($courseAll instanceof \Illuminate\Pagination\AbstractPaginator && $courseAll->total() > 4)
            <div class="text-center mt-4">
                <a href="{{ route('courses.home') }}" class="btn btn-outline-primary px-4">
                    {{ __('home::common.all_course') }} →
                </a>
            </div>
        @endif

    </div>
</section>

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

        .course-thumb img {
            width: 180px;
            height: 180px;
            object-fit: cover;
        }

        .course-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 14px;
        }

        .btn-view {
            font-size: 13px;
            font-weight: 600;
            color: #2563eb;
            padding: 6px 14px;
            border-radius: 20px;
            border: 1px solid #2563eb;
            text-decoration: none;
            transition: all .25s ease;
        }

        .btn-view:hover {
            background: #2563eb;
            color: #fff;
        }

        /* Hover nổi nút */
        .course-card:hover .btn-view {
            transform: translateX(3px);
        }

        @media (max-width: 768px) {
            .course-card {
                flex-direction: column;
            }

            .course-thumb {
                width: 100%;
            }

            .course-thumb img {
                width: 100%;
                height: 200px;
            }

            .course-bottom {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
@endsection
