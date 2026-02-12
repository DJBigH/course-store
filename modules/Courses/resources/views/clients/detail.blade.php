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
                                    <i class="fa-solid fa-file"></i> {{ __('courses::clients/common.information') }}
                                </a>
                            </li>
                            <li>
                                <a href="#curriculum">
                                    <i class="fa-solid fa-book"></i>
                                    {{ __('courses::clients/common.curriculum') }}
                                </a>
                            </li>
                            <li>
                                <a href="#author">
                                    <i class="fa-solid fa-user"></i>
                                    {{ __('courses::clients/common.author') }}
                                </a>
                            </li>
                            <li>
                                <a href="#evaluate">
                                    <i class="fa-solid fa-comment"></i>
                                    {{ __('courses::clients/common.evaluate') }}
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="course-descreption instructor-box" id="information">
                        <div class="course-content">
                            {!! $course->detail_locale !!}
                        </div>
                    </div>

                    <div class="accordion instructor-box" id="curriculum">
                        <div class="accordion-top px-2">
                            <p>
                                <i class="fa-solid fa-book me-1"></i>
                                {{ __('courses::clients/common.include') }}: {{ getLessonCount($course)->module }}
                                {{ __('courses::clients/common.portion') }} - {{ getLessonCount($course)->lessons }}
                                {{ __('courses::clients/common.lessons') }}
                            </p>
                            <p>
                                <i class="fa-solid fa-clock me-1"></i>
                                {{ __('courses::clients/common.duration') }} {{ getTime($course->durations) }}
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
                                    <p class="text-muted mb-1 small">{{ __('courses::clients/common.instructor') }}</p>

                                    <h5 class="instructor-name mb-1 fw-semibold">
                                        <a href="/giang-vien/{{ $course->teacher->slug }}"
                                            class="text-decoration-none text-dark hover-primary">
                                            {{ $course->teacher->name }}
                                        </a>
                                    </h5>

                                    <div class="d-flex align-items-center gap-2 text-muted">
                                        <i class="bi bi-mortarboard"></i>
                                        <span>{{ $course->teacher->exp }}
                                            {{ __('courses::clients/common.experience_years') }}</span>
                                    </div>
                                </div>

                            </div>

                            <hr>

                            <div class="course-content-infor instructor-desc">
                                {!! $course->teacher->description_locale !!}
                            </div>
                        </div>
                    @endif


                    <div class="course-video mb-4 instructor-box" id="evaluate">
                        <h2 class="fs-4">{{ __('courses::clients/common.student_reviews') }}</h2>
                    </div>
                </div>
                <div class="col-12 col-lg-3">
                    <div class="course-profile shadow-sm rounded mb-4">
                        <!-- Thumbnail -->
                        <div class="course-thumb">
                            <img src="{{ $course->thumbnail }}" alt="{{ $course->name_locale }}">
                        </div>

                        <!-- Content -->
                        <div class="course-info p-3">
                            <!-- Price -->
                            <div class="course-price mb-3">
                                <i class="fa-solid fa-tag text-primary me-1"></i>
                                @if ($course->sale_price)
                                    <span class="text-muted text-decoration-line-through me-2">
                                        {{ moneyLocale($course->price) }}
                                    </span>
                                    <span class="fw-bold text-danger fs-5">
                                        {{ moneyLocale($course->sale_price) }}
                                    </span>
                                @else
                                    <span class="fw-bold fs-5 text-danger">
                                        {{ moneyLocale($course->price) }}
                                    </span>
                                @endif
                            </div>

                            <!-- Info list -->
                            <ul class="course-meta list-unstyled mb-3">
                                <li>
                                    <i class="fa-solid fa-bookmark text-warning"></i>
                                    <span>{{ __('courses::clients/common.course_code') }}:</span>
                                    <strong>{{ $course->code }}</strong>
                                </li>

                                <li>
                                    <i class="fa-solid fa-user-graduate text-primary"></i>
                                    <span>{{ __('courses::clients/common.instructor') }}:</span>
                                    <strong>{{ $course->teacher->name }}</strong>
                                    <small class="text-muted">({{ $course->teacher->exp }}
                                        {{ __('courses::clients/common.exp') }})</small>
                                </li>

                                <li>
                                    <i class="fa-solid fa-clock text-success"></i>
                                    <span>{{ __('courses::clients/common.duration') }}:</span>
                                    <strong>{{ getTime($course->durations) }}</strong>
                                </li>

                                {{-- ✅ THÊM: Cập nhật gần nhất --}}
                                <li>
                                    <i class="fa-solid fa-calendar-check text-secondary"></i>
                                    <span>{{ __('courses::clients/common.updated_at') }}:</span>
                                    <strong>{{ format_date_dmy($course->updated_at) }}</strong>
                                </li>

                                {{-- ✅ THÊM: Tổng số học viên --}}
                                <li>
                                    <i class="fa-solid fa-users text-info"></i>
                                    <span>{{ __('courses::clients/common.students') }}:</span>
                                    <strong>{{ number_format($course->students_count ?? 0) }}</strong>
                                </li>

                                <li>
                                    <i class="fa-solid fa-headset text-info"></i>
                                    <span>{{ __('courses::clients/common.support') }}:</span>
                                    <strong>{!! $course->supports_locale !!}</strong>
                                </li>

                                <li class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-file-lines text-info"></i>
                                    <span>{{ __('courses::clients/common.attachments') }}:</span>

                                    @if ($course->is_document == 1)
                                        <span class="badge bg-success">
                                            <i class="fa-solid fa-check me-1"></i> {{ __('courses::clients/common.yes') }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fa-solid fa-xmark me-1"></i> {{ __('courses::clients/common.no') }}
                                        </span>
                                    @endif
                                </li>
                            </ul>


                            @php
                                $student = Auth::guard('students')->user();
                                $hasCourse = $student
                                    ? $student
                                        ->courses()
                                        ->where('courses.id', $course->id)
                                        ->wherePivot('status', 1)
                                        ->exists()
                                    : false;
                                $firstLesson = $course->lessons->whereNotNull('parent_id')->first();
                            @endphp

                            @if ($hasCourse && $firstLesson)
                                <a href="{{ route('lessons.home', ['locale' => app()->getLocale(), 'slug' => $firstLesson->slug]) }}"
                                    class="btn btn-success w-100 fw-semibold">
                                    <i class="fa-solid fa-play me-1"></i>
                                    {{ __('courses::clients/common.start_learning') }}
                                </a>
                            @elseif ($hasCourse)
                                <button class="btn btn-secondary w-100" disabled>
                                    <i class="fa-solid fa-circle-info me-1"></i>
                                    {{ __('courses::clients/common.no_lectures') }}

                                </button>
                            @else
                                <form action="{{ route('courses.create', ['locale' => app()->getLocale()]) }}"
                                    method="POST">
                                    @csrf
                                    <input type="hidden" name="course_id" value="{{ $course->id }}">

                                    <button class="btn btn-primary w-100 fw-semibold payment">
                                        <i class="fa-solid fa-cart-shopping me-1"></i>
                                        {{ __('courses::clients/common.buy_course') }}
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
