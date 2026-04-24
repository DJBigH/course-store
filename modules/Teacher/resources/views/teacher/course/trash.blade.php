@extends('layouts.teacher')

@section('content')
    <div class="teacher-panel">
        <div class="teacher-section-title mb-4">
            <div>
                <h3 class="fw-bold mb-2">{{ __('teacher::teacher/course/list.trash_title') }}</h3>
                <p class="text-muted mb-0">{{ __('teacher::teacher/course/list.trash_description') }}</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('teacher.dashboard.courses') }}" class="btn btn-outline-secondary">
                    {{ __('teacher::teacher/course/common.actions.back') }}
                </a>
            </div>
        </div>

        @if (session('msg_success'))
            <div class="alert alert-success">{{ session('msg_success') }}</div>
        @endif
        @if (session('msg_danger'))
            <div class="alert alert-danger">{{ session('msg_danger') }}</div>
        @endif

        <div class="row g-3">
            @forelse ($courses as $course)
                <div class="col-md-6">
                    <article class="teacher-stat-card teacher-course-card">
                        <strong class="d-block mb-2">{{ $course->name_locale }}</strong>
                        <div class="text-muted small mb-3">
                            {{ __('teacher::teacher/course/common.labels.deleted_at', ['time' => optional($course->deleted_at)->format('d/m/Y H:i')]) }}
                        </div>
                        <div class="d-flex flex-wrap gap-2 small">
                            <span class="teacher-chip">{{ __('teacher::teacher/course/common.labels.lessons', ['count' => $course->lessons_count]) }}</span>
                            <span class="teacher-chip">{{ __('teacher::teacher/course/common.labels.students', ['count' => $course->students_count]) }}</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <form method="POST" action="{{ route('teacher.dashboard.courses.restore', $course->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    {{ __('teacher::teacher/course/common.actions.restore') }}
                                </button>
                            </form>

                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <div class="teacher-panel">
                        <p class="text-muted mb-0">{{ __('teacher::teacher/course/list.trash_empty') }}</p>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $courses->links() }}
        </div>
    </div>
@endsection
