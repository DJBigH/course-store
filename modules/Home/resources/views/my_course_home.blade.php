@auth('students')
    <section class="foundation-course">
        <div class="container py-5">
            <h3 class="section-title mb-4 text-success">🎓 Khóa học của bạn</h3>

            <div class="row g-4">
                @if (!empty($myCourse))
                    @foreach ($myCourse as $item)
                        <div class="col-12 col-lg-6">
                            <div class="course-card d-flex">
                                <div class="course-thumb">
                                    <a href="{{ $item->teacher->image }}"><img src="{{ asset($item->thumbnail) }}"
                                            alt="Course banner"></a>
                                </div>

                                <div class="course-content">
                                    <div class="course-meta">
                                        <span><i class="fa-solid fa-clock"></i> {{ getTime($item->durations) }}</span>
                                        <span><i class="fa-solid fa-video"></i> {{ getLessonCount($item)->module }}
                                            phần/{{ getLessonCount($item)->lessons }} bài</span>
                                        <span><i class="fa-solid fa-eye"></i>
                                            {{ $item->view ? number_format($item->view) : 0 }} lượt xem</span>
                                    </div>

                                    <h5 class="course-title">
                                        <a href="{{ route('courses.detail', $item->slug) }}">
                                            {{ $item->name }}
                                        </a>
                                    </h5>

                                    <div class="course-teacher">
                                        <img src="{{ $item->teacher->image }}" alt="">
                                        <span>{{ $item->teacher->name }}</span>
                                    </div>

                                    <div class="course-price">
                                        <span class="price-old">{{ money($item->price) }}</span>
                                        <span class="price-new">{{ money($item->sale_price) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="col-12">
                        <div class="empty-course text-center p-5">
                            <img src="/clients/assets/empty-course.png" alt="No course" class="mb-3" width="160">
                            <h5 class="fw-bold">Bạn chưa có khóa học nào</h5>
                            <p class="text-muted mb-3">
                                Hãy đăng ký khóa học đầu tiên để bắt đầu hành trình học tập của bạn 🚀
                            </p>
                            <a href="{{ route('courses.index') }}" class="btn btn-primary px-4">
                                Khám phá khóa học
                            </a>
                        </div>
                    </div>
                @endif
            </div>
            <div class="mt-2">
                {{ $myCourse->links() }}
            </div>
        </div>
    </section>
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
    </style>
@endsection
