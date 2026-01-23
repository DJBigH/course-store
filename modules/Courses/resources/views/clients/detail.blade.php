@extends('layouts.client')
@section('content')
    @include('part.clients.page_title')
    <section class="course-detal">
        <div class="container">
            <div class="row relative">
                <div class="col-12 col-lg-9">
                    <div class="submenu">
                        <ul>
                            <li>
                                <a href="#information">
                                    <i class="fa-solid fa-file"></i> Thông tin chung
                                </a>
                            </li>
                            <li>
                                <a href="#curriculum">
                                    <i class="fa-solid fa-book"></i>
                                    Giáo trình
                                </a>
                            </li>
                            <li>
                                <a href="#author">
                                    <i class="fa-solid fa-user"></i>
                                    Giảng viên
                                </a>
                            </li>
                            <li>
                                <a href="#evaluate">
                                    <i class="fa-solid fa-comment"></i>
                                    Đánh giá
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="course-descreption instructor-box" id="information">
                        <div class="course-content">
                            {!! $course->detail !!}
                        </div>
                    </div>

                    <div class="accordion instructor-box" id="curriculum">
                        <div class="accordion-top px-2">
                            <p>
                                <i class="fa-solid fa-book me-1"></i>
                                Gồm: {{ getLessonCount($course)->module }} phần - {{ getLessonCount($course)->lessons }} bài
                                giảng
                            </p>
                            <p>
                                <i class="fa-solid fa-clock me-1"></i>
                                Thời lượng {{ getTime($course->durations) }}
                            </p>
                        </div>
                        @include('courses::clients.lesson')
                    </div>

                    @if ($course->teacher)
                        <div class="course-video instructor-box mb-4" id="author">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 instructor-avatar">
                                    <img src="{{ $course->teacher->image }}" alt="{{ $course->teacher->name }}"
                                        class="rounded-circle">
                                </div>

                                <div class="flex-grow-1 ms-3">
                                    <p class="text-muted mb-1 small">Giảng viên</p>

                                    <h5 class="instructor-name mb-1 fw-semibold">
                                        <a href="/giang-vien/{{ $course->teacher->slug }}"
                                            class="text-decoration-none text-dark hover-primary">
                                            {{ $course->teacher->name }}
                                        </a>
                                    </h5>

                                    <div class="d-flex align-items-center gap-2 text-muted">
                                        <i class="bi bi-mortarboard"></i>
                                        <span>{{ $course->teacher->exp }} năm kinh nghiệm</span>
                                    </div>
                                </div>

                            </div>

                            <hr>

                            <div class="course-content-infor instructor-desc">
                                {!! $course->teacher->description !!}
                            </div>
                        </div>
                    @endif


                    <div class="course-video mb-4 instructor-box" id="evaluate">
                        <h2 class="fs-4">Học viên đánh giá</h2>
                    </div>
                </div>
                <div class="col-12 col-lg-3">
                    <div class="course-profile shadow-sm rounded mb-4">
                        <!-- Thumbnail -->
                        <div class="course-thumb">
                            <img src="{{ $course->thumbnail }}" alt="{{ $course->name }}">
                        </div>

                        <!-- Content -->
                        <div class="course-info p-3">
                            <!-- Price -->
                            <div class="course-price mb-3">
                                <i class="fa-solid fa-tag text-primary me-1"></i>
                                @if ($course->sale_price)
                                    <span class="text-muted text-decoration-line-through me-2">
                                        {{ money($course->price) }}
                                    </span>
                                    <span class="fw-bold text-danger fs-5">
                                        {{ money($course->sale_price) }}
                                    </span>
                                @else
                                    <span class="fw-bold fs-5 text-danger">
                                        {{ money($course->price) }}
                                    </span>
                                @endif
                            </div>

                            <!-- Info list -->
                            <ul class="course-meta list-unstyled mb-3">
                                <li>
                                    <i class="fa-solid fa-bookmark text-warning"></i>
                                    <span>Mã khóa học:</span>
                                    <strong>{{ $course->code }}</strong>
                                </li>

                                <li>
                                    <i class="fa-solid fa-user-graduate text-primary"></i>
                                    <span>Giảng viên:</span>
                                    <strong>{{ $course->teacher->name }}</strong>
                                    <small class="text-muted">({{ $course->teacher->exp }} năm)</small>
                                </li>

                                <li>
                                    <i class="fa-solid fa-clock text-success"></i>
                                    <span>Thời lượng:</span>
                                    <strong>{{ getTime($course->durations) }}</strong>
                                </li>

                                {{-- ✅ THÊM: Cập nhật gần nhất --}}
                                <li>
                                    <i class="fa-solid fa-calendar-check text-secondary"></i>
                                    <span>Cập nhật:</span>
                                    <strong>{{ format_date_dmy($course->updated_at) }}</strong>
                                </li>

                                {{-- ✅ THÊM: Tổng số học viên --}}
                                <li>
                                    <i class="fa-solid fa-users text-info"></i>
                                    <span>Học viên:</span>
                                    <strong>{{ number_format($course->students_count ?? 0) }}</strong>
                                </li>

                                <li>
                                    <i class="fa-solid fa-headset text-info"></i>
                                    <span>Hỗ trợ:</span>
                                    <strong>{{ $course->supports }}</strong>
                                </li>

                                <li class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-file-lines text-info"></i>
                                    <span>Tài liệu đính kèm:</span>

                                    @if ($course->is_document == 1)
                                        <span class="badge bg-success">
                                            <i class="fa-solid fa-check me-1"></i> Có
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fa-solid fa-xmark me-1"></i> Không
                                        </span>
                                    @endif
                                </li>
                            </ul>


                            @php
                                $student = Auth::guard('students')->user();
                                $hasCourse = $student
                                    ? $student->courses()->where('courses.id', $course->id)->wherePivot('status', 1)->exists()
                                    : false;
                                $firstLesson = $course->lessons->whereNotNull('parent_id')->first();
                            @endphp

                            @if ($hasCourse)
                                <a href="{{ route('lessons.home', $firstLesson->slug) }}"
                                    class="btn btn-success w-100 fw-semibold">
                                    <i class="fa-solid fa-play me-1"></i>
                                    Vào học
                                </a>
                            @else
                                <form action="{{ route('courses.create') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="course_id" value="{{ $course->id }}">

                                    <button class="btn btn-primary w-100 fw-semibold payment">
                                        <i class="fa-solid fa-cart-shopping me-1"></i>
                                        Đặt mua khóa học
                                    </button>
                                </form>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
