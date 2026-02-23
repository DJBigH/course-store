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
                                    </div>

                                    <h5 class="descreption-title">
                                            <a
                                                href="{{ route('courses.detail', ['locale' => app()->getLocale(), 'slug' => $course->slug_locale]) }}">
                                                {{ $course->name_locale ?? $course->name }}
                                            </a>
                                    </h5>


                                    <div class="descreption-teacher">
                                        <img src="{{ $course->teacher?->image }}" alt="{{ $course->teacher?->name }}" />
                                        <span>
                                            <strong
                                                style="font-weight: bold">{{ __('courses::clients/common.instructor') }}:</strong>
                                            {{ $course->teacher?->name }}
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
