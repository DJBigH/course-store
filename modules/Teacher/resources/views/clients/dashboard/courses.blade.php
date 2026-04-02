@extends('layouts.teacher')

@section('content')
    <div class="teacher-panel">
        <div class="teacher-section-title mb-4">
            <div>
                <h3 class="fw-bold mb-2">{{ __('teacher::dashboard.courses.title') }}</h3>
                <p class="text-muted mb-0">{{ __('teacher::dashboard.courses.description') }}</p>
            </div>
        </div>

        @include('teacher::clients.dashboard._tabs')

        <div class="row g-3">
            @forelse ($courses as $course)
                <div class="col-md-6">
                    <article class="teacher-stat-card teacher-course-card">
                        <strong class="d-block mb-2">{{ $course->name_locale }}</strong>
                        <div class="text-muted small mb-3">
                            {{ $course->status ? __('teacher::dashboard.common.course_active') : __('teacher::dashboard.common.course_hidden') }}
                        </div>
                        <div class="d-flex flex-wrap gap-2 small">
                            <span class="teacher-chip">{{ __('teacher::dashboard.courses.labels.lessons', ['count' => $course->lessons_count]) }}</span>
                            <span class="teacher-chip">{{ __('teacher::dashboard.courses.labels.students', ['count' => $course->students_count]) }}</span>
                            <span class="teacher-chip">{{ __('teacher::dashboard.courses.labels.price', ['amount' => money($course->sale_price ?: $course->price)]) }}</span>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <div class="teacher-panel">
                        <p class="text-muted mb-0">{{ __('teacher::dashboard.courses.empty') }}</p>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $courses->links() }}
        </div>
    </div>
@endsection
