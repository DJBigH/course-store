@extends('layouts.teacher')

@section('content')
    <div class="teacher-panel">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
            <div>
                <h3 class="fw-bold mb-2">{{ __('quizzes::teacher/messages.results.title') }}</h3>
                <p class="text-muted mb-0">{{ $quiz->title }}</p>
            </div>
            <a href="{{ route('teacher.dashboard.quizzes.index', $course->id) }}" class="btn btn-outline-secondary">{{ __('quizzes::teacher/messages.results.back_to_list') }}</a>
        </div>

        @if (!$teacher->packageHasFeature('can_import_export'))
            @include('teacher::clients.dashboard.partials.package_feature_notice', [
                'message' => __('courses::teacher/messages.package_features.import_export_locked'),
            ])
        @endif

        <form method="GET" class="row g-2 mb-4">
            <div class="col-md-3">
                <input type="text" name="student" value="{{ request('student') }}" class="form-control" placeholder="{{ __('quizzes::teacher/messages.results.filter.student_placeholder') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">{{ __('quizzes::teacher/messages.results.filter.status_all') }}</option>
                    <option value="passed" @selected(request('status') === 'passed')>{{ __('quizzes::teacher/messages.results.filter.status_passed') }}</option>
                    <option value="failed" @selected(request('status') === 'failed')>{{ __('quizzes::teacher/messages.results.filter.status_failed') }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="from" value="{{ request('from') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <input type="date" name="to" value="{{ request('to') }}" class="form-control">
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-primary">{{ __('quizzes::teacher/messages.results.filter.submit') }}</button>
                <a href="{{ route('teacher.dashboard.quizzes.results', [$course->id, $quiz->id]) }}" class="btn btn-outline-secondary">{{ __('quizzes::teacher/messages.results.filter.reset') }}</a>
                @if ($teacher->packageHasFeature('can_import_export'))
                    <a href="{{ route('teacher.dashboard.quizzes.results.export', [$course->id, $quiz->id]) }}?{{ http_build_query(request()->query()) }}" class="btn btn-outline-success">{{ __('quizzes::teacher/messages.results.filter.export') }}</a>
                @endif
            </div>
        </form>

        <div class="row g-3 mb-4">
            <div class="col-md-3"><div class="teacher-panel text-center"><div class="small text-muted">{{ __('quizzes::teacher/messages.results.stats.total') }}</div><div class="fs-4 fw-bold">{{ $submissions->count() }}</div></div></div>
            <div class="col-md-3"><div class="teacher-panel text-center"><div class="small text-muted">{{ __('quizzes::teacher/messages.results.stats.passed') }}</div><div class="fs-4 fw-bold">{{ $submissions->where('passed', true)->count() }}</div></div></div>
            <div class="col-md-3"><div class="teacher-panel text-center"><div class="small text-muted">{{ __('quizzes::teacher/messages.results.stats.failed') }}</div><div class="fs-4 fw-bold">{{ $submissions->where('passed', false)->count() }}</div></div></div>
            <div class="col-md-3"><div class="teacher-panel text-center"><div class="small text-muted">{{ __('quizzes::teacher/messages.results.stats.avg') }}</div><div class="fs-4 fw-bold">{{ number_format((float) $submissions->avg('score'), 1) }}%</div></div></div>
        </div>

        <div class="d-grid gap-3">
            @forelse ($submissions as $submission)
                <div class="teacher-panel">
                    <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                        <div>
                            <div class="fw-semibold">{{ $submission->student?->name ?: __('quizzes::teacher/messages.results.item.student_fallback') }} <span class="text-muted small">#{{ $submission->student_id }}</span></div>
                            <div class="small text-muted">{{ __('quizzes::teacher/messages.results.item.student_id', ['id' => $submission->student_id]) }} • {{ __('quizzes::teacher/messages.results.item.attempt', ['count' => $submission->attempt_no]) }} • {{ __('quizzes::teacher/messages.results.item.submitted_at', ['date' => optional($submission->submitted_at)->format('d/m/Y H:i')]) }}</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold fs-4">{{ $submission->score }}%</div>
                            <span class="badge bg-{{ $submission->passed ? 'success' : 'danger' }}">{{ $submission->passed ? __('quizzes::teacher/messages.results.item.passed') : __('quizzes::teacher/messages.results.item.failed') }}</span>
                        </div>
                    </div>

                    <div class="mt-3">
                        @foreach ($submission->answers as $answer)
                            <div class="border rounded p-3 mb-2">
                                <div class="fw-semibold">{{ $answer->question?->question }}</div>
                                @if($answer->question?->question_type === 'short_answer')
                                    <div class="small bg-warning-subtle text-warning-emphasis p-2 mt-2 rounded border border-warning">
                                        <i class="fas fa-exclamation-circle me-1"></i> <strong>{{ __('quizzes::teacher/messages.results.item.essay_label') }}</strong><br>
                                        {{ $answer->answer_text ?: __('quizzes::teacher/messages.results.item.not_answered') }}
                                    </div>
                                    <div class="small mt-2">
                                        <span class="badge bg-warning text-dark">{{ __('quizzes::teacher/messages.results.item.pending') }}</span>
                                    </div>
                                @else
                                    <div class="small text-muted mt-1">{{ $answer->choice?->choice_text ?: $answer->answer_text ?: __('quizzes::teacher/messages.results.item.not_answered') }}</div>
                                    <div class="small mt-1">{{ __('quizzes::teacher/messages.results.item.points', ['points' => $answer->points_earned]) }} • <span class="badge {{ $answer->is_correct ? 'bg-success' : 'bg-danger' }}">{{ $answer->is_correct ? __('quizzes::teacher/messages.results.item.correct') : __('quizzes::teacher/messages.results.item.incorrect') }}</span></div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="alert alert-info">{{ __('quizzes::teacher/messages.results.empty') }}</div>
            @endforelse
        </div>
    </div>
@endsection
