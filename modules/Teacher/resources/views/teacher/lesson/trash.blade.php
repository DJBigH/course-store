@extends('layouts.teacher')

@section('content')
    <div class="teacher-panel">
        <div class="teacher-section-title mb-4">
            <div>
                <h3 class="fw-bold mb-2">{{ __('teacher::teacher/lesson/list.trash_title', ['course' => $course->name_locale]) }}</h3>
                <p class="text-muted mb-0">{{ __('teacher::teacher/lesson/list.trash_description') }}</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('teacher.dashboard.lessons.index', $course->id) }}" class="btn btn-outline-secondary">
                    {{ __('teacher::teacher/course/common.actions.back') }}
                </a>
            </div>
        </div>

        @if (session('msg_success'))
            <div class="alert alert-success">{{ session('msg_success') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>{{ __('teacher::teacher/lesson/common.form.name') }}</th>
                        <th>{{ __('teacher::teacher/lesson/add.form.is_trial') }}</th>
                        <th>{{ __('teacher::teacher/lesson/add.form.document') }}</th>
                        <th>{{ __('teacher::teacher/course/common.form.status') }}</th>
                        <th>{{ __('teacher::teacher/course/list.table.deleted_at') }}</th>
                        <th class="text-end">{{ __('teacher::teacher/course/list.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($trashedLessonRows as $row)
                        <tr>
                            <td>{{ $row['name'] }}</td>
                            <td>{{ $row['is_trial'] }}</td>
                            <td>{{ $row['has_document'] }}</td>
                            <td>{{ $row['status'] }}</td>
                            <td>{{ $row['deleted_at'] }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                    <form method="POST" action="{{ route('teacher.dashboard.lessons.restore', [$course->id, $row['id']]) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                            {{ __('teacher::teacher/lesson/common.actions.restore') }}
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-muted">{{ __('teacher::teacher/lesson/list.trash_empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
