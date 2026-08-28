@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                {{-- Kết quả tổng quan --}}
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <div class="card-body p-0">
                        <div class="p-4 p-md-5 text-center {{ $submission->passed ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                            <div class="display-1 fw-bold mb-3">
                                {{ $submission->score }}%
                            </div>
                            <h2 class="fw-bold mb-2">
                                {{ $submission->passed ? __('courses::teacher/messages.quizzes_results.congrats') : __('courses::teacher/messages.quizzes_results.sorry') }}
                            </h2>
                            <p class="mb-0 opacity-75">
                                {{ __('courses::teacher/messages.quizzes_results.quiz_label') }} <strong>{{ $quiz->title }}</strong> • 
                                {{ __('courses::teacher/messages.quizzes_results.passing_label') }} {{ $quiz->passing_score }}% • 
                                {{ __('courses::teacher/messages.quizzes_results.submitted_at') }} {{ $submission->submitted_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        <div class="p-4 border-top bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('teacher.dashboard.courses') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                    {{ __('courses::teacher/messages.quizzes_results.back_to_course') }}
                                </a>
                            </div>
                            <div class="d-flex gap-2">
                                @if (!$submission->passed && ($quiz->max_attempts == 0 || $submission->attempt_no < $quiz->max_attempts))
                                    <a href="{{ route('teacher.dashboard.quizzes.show', [$course->id, $quiz->id]) }}" class="btn btn-primary rounded-pill px-4">
                                        {{ __('courses::teacher/messages.quizzes_results.retry_btn') }}
                                    </a>
                                @endif
                                <button onclick="window.print()" class="btn btn-outline-dark rounded-pill px-4">
                                    <i class="fas fa-print me-1"></i> {{ __('courses::teacher/messages.quizzes_results.print_btn') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="teacher-panel text-center">
                    <div class="small text-muted">{{ __('courses::teacher/messages.quizzes_results.questions_total') }}</div>
                    <div class="fs-4 fw-bold">{{ $quiz->questions->count() }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="teacher-panel text-center">
                    <div class="small text-muted">{{ __('courses::teacher/messages.quizzes_results.passing_score') }}</div>
                    <div class="fs-4 fw-bold">{{ $quiz->passing_score }}%</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="teacher-panel text-center">
                    <div class="small text-muted">{{ __('courses::teacher/messages.quizzes_results.attempts') }}</div>
                    <div class="fs-4 fw-bold">{{ $submission->attempt_no }}</div>
                </div>
            </div>
        </div>

        <div class="d-grid gap-3">
            @foreach ($submission->answers as $answer)
                <div class="teacher-panel">
                    <div class="fw-semibold mb-2">{{ $answer->question?->question }}</div>
                    <div class="small text-muted">{{ __('courses::teacher/messages.quizzes_results.points_earned', ['points' => $answer->points_earned]) }}</div>
                    <div class="mt-2">{{ $answer->answer_text ?: ($answer->choice?->choice_text ?: __('courses::teacher/messages.quizzes_results.no_answer')) }}</div>
                    <div class="mt-2 badge bg-{{ $answer->is_correct ? 'success' : 'secondary' }}">
                        {{ $answer->is_correct ? __('courses::teacher/messages.quizzes_results.correct') : __('courses::teacher/messages.quizzes_results.incorrect') }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
