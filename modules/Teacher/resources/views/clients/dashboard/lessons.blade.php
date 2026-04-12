@extends('layouts.teacher')

@section('content')
    <div class="teacher-panel">
        <div class="teacher-section-title mb-4">
            <div>
                <h3 class="fw-bold mb-2">{{ __('teacher::dashboard.lessons.title', ['course' => $course->name_locale]) }}</h3>
                <p class="text-muted mb-0">{{ __('teacher::dashboard.lessons.description') }}</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('teacher.dashboard.courses') }}" class="btn btn-outline-secondary">
                    {{ __('teacher::dashboard.common.back') }}
                </a>
                <a href="{{ route('teacher.dashboard.lessons.trash', $course->id) }}" class="btn btn-outline-secondary">
                    {{ __('teacher::dashboard.lessons.actions.trash') }}
                </a>
                <a href="{{ route('teacher.dashboard.lessons.create', $course->id) }}" class="btn btn-primary">
                    {{ __('teacher::dashboard.lessons.actions.create') }}
                </a>
            </div>
        </div>

        @if (session('msg_success'))
            <div class="alert alert-success">{{ session('msg_success') }}</div>
        @endif
        @if (session('msg_danger'))
            <div class="alert alert-danger">{{ session('msg_danger') }}</div>
        @endif

        @forelse ($modules as $module)
            <section class="teacher-panel mb-4">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <div class="text-uppercase small text-muted mb-1">{{ __('teacher::dashboard.lessons.module_label') }}</div>
                        <h4 class="h5 mb-1">{{ $module->name_locale }}</h4>
                        <p class="text-muted mb-0">{{ __('teacher::dashboard.lessons.labels.position', ['position' => $module->position]) }}</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('teacher.dashboard.lessons.edit', [$course->id, $module->id]) }}" class="btn btn-sm btn-outline-primary">
                            {{ __('teacher::dashboard.lessons.actions.edit') }}
                        </a>
                        <a href="{{ route('teacher.dashboard.lessons.create', [$course->id, 'module' => $module->id]) }}" class="btn btn-sm btn-outline-secondary">
                            {{ __('teacher::dashboard.lessons.actions.add_child') }}
                        </a>
                        <form method="POST" action="{{ route('teacher.dashboard.lessons.delete', [$course->id, $module->id]) }}" onsubmit="return confirm('{{ __('teacher::dashboard.lessons.confirm_delete') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                {{ __('teacher::dashboard.lessons.actions.delete') }}
                            </button>
                        </form>
                    </div>
                </div>

                @if ($module->subLessons->isNotEmpty())
                    <div class="row g-3">
                        @foreach ($module->subLessons->values() as $lesson)
                            <div class="col-lg-6">
                                @include('teacher::clients.dashboard.partials.lesson_item', [
                                    'lesson' => $lesson,
                                    'course' => $course,
                                    'previousLesson' => $loop->index > 0 ? $module->subLessons->values()->get($loop->index - 1) : null,
                                ])
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">{{ __('teacher::dashboard.lessons.empty_module') }}</p>
                @endif
            </section>
        @empty
            <div class="teacher-panel">
                <p class="text-muted mb-0">{{ __('teacher::dashboard.lessons.empty') }}</p>
            </div>
        @endforelse
    </div>
@endsection
