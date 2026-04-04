@extends('layouts.teacher')

@section('content')
    <div class="teacher-panel">
        <div class="teacher-section-title mb-4">
            <div>
                <h3 class="fw-bold mb-2">{{ __('teacher::dashboard.lessons.trash_title', ['course' => $course->name_locale]) }}</h3>
                <p class="text-muted mb-0">{{ __('teacher::dashboard.lessons.trash_description') }}</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('teacher.dashboard.lessons.index', $course->id) }}" class="btn btn-outline-secondary">
                    {{ __('teacher::dashboard.common.back') }}
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
                        <th>{{ __('teacher::dashboard.lessons.table.name') }}</th>
                        <th>{{ __('teacher::dashboard.lessons.table.trial') }}</th>
                        <th>{{ __('teacher::dashboard.lessons.table.document') }}</th>
                        <th>{{ __('teacher::dashboard.lessons.table.status') }}</th>
                        <th>{{ __('teacher::dashboard.lessons.table.deleted_at') }}</th>
                        <th class="text-end">{{ __('teacher::dashboard.lessons.table.actions') }}</th>
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
                                            {{ __('teacher::dashboard.lessons.actions.restore') }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('teacher.dashboard.lessons.force-delete', [$course->id, $row['id']]) }}" onsubmit="return confirm('{{ __('teacher::dashboard.lessons.confirm_force_delete') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            {{ __('teacher::dashboard.lessons.actions.force_delete') }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-muted">{{ __('teacher::dashboard.lessons.trash_empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
