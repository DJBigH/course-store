@extends('layouts.client')
@section('content')
    @include('part.clients.page_title')
    <section class="all-course">
        <div class="container">
            @if ($courses && $courses->count())
                <div class="row">
                    @foreach ($courses as $course)
                        <div class="col-12 col-lg-6 mb-4">
                            <div class="d-flex course">
                                <div class="banner-course">
                                    <img src="{{ $course->thumbnail }}" alt="{{ $course->name }}" />
                                </div>

                                <div class="descreption-course">
                                    <div class="descreption-top">
                                        <p><i class="fa-solid fa-clock"></i> {{ getTime($course->durations) }} học</p>
                                        <p><i class="fa-solid fa-video"></i> {{ getLessonCount($course)->module }}
                                            phần/{{ getLessonCount($course)->lessons }} bài</p>
                                        <p><i class="fa-solid fa-eye"></i>
                                            {{ $course->view ? number_format($course->view) : 0 }} lượt xem</p>
                                    </div>

                                    <div class="descreption-meta">
                                        <p>
                                            <i class="fa-solid fa-calendar-check"></i>
                                            Cập nhật:
                                            <span>{{ format_date_dmy($course->updated_at) }}</span>
                                        </p>

                                        <p>
                                            <i class="fa-solid fa-users"></i>
                                            {{ number_format($course->students_count ?? 0) }} học viên
                                        </p>
                                    </div>


                                    <h5 class="descreption-title">
                                        <a href="/khoa-hoc/{{ $course->slug }}">
                                            {{ $course->name }}
                                        </a>
                                    </h5>

                                    <div class="descreption-teacher">
                                        <img src="{{ $course->teacher?->image }}" alt="{{ $course->teacher?->name }}" />
                                        <span>
                                            <strong style="font-weight: bold">Giảng viên:</strong>
                                            {{ $course->teacher?->name }}
                                        </span>
                                    </div>

                                    <p class="descreption-price">
                                        @if ($course->sale_price)
                                            <span class="sale">{{ money($course->price) }}</span>
                                            <span>{{ money($course->sale_price) }}</span>
                                        @else
                                            <span>{{ money($course->price) }}</span>
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

                    <h4 class="fw-bold mb-2">Chưa có khóa học nào</h4>

                    <p class="text-muted mb-4">
                        Hiện tại danh mục này chưa có khóa học.
                        <br>
                        Bạn có thể khám phá các khóa học khác phù hợp với mình 🚀
                    </p>

                    <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="btn btn-primary px-4">
                        <i class="fa-solid fa-book-open me-1"></i>
                        Khám phá khóa học
                    </a>
                </div>
            @endif
        </div>
    </section>

@endsection

@section('stylesheet')
    <style>
        .empty-course {
            background: #f8fafc;
            border-radius: 14px;
            padding: 60px 20px;
            box-shadow: inset 0 0 0 1px #e5e7eb;
        }

        .empty-course img {
            opacity: 0.9;
        }

        .empty-course h4 {
            color: #111827;
        }

        .empty-course p {
            font-size: 15px;
        }
    </style>
@endsection
