@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')
    <div class="container py-4" style="max-width: 860px">
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start">
                    <div>
                        <h4 class="fw-bold mb-1">{{ $quiz->title }}</h4>
                        @if ($quiz->description)
                            <p class="text-muted mb-2">{{ $quiz->description }}</p>
                        @endif
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-primary">{{ $quiz->questions->count() }} {{ __('courses::teacher/messages.quizzes_student.questions_unit') }}</span>
                            <span class="badge bg-secondary">{{ __('courses::teacher/messages.quizzes_student.passing_score_unit', ['score' => $quiz->passing_score]) }}</span>
                            @if ($quiz->max_attempts)
                                <span class="badge bg-info">{{ __('courses::teacher/messages.quizzes_student.max_attempts_unit', ['count' => $quiz->max_attempts]) }}</span>
                            @endif
                            @if ($quiz->time_limit_minutes)
                                <span class="badge bg-warning text-dark">⏱ {{ __('courses::teacher/messages.quizzes_student.time_limit_unit', ['count' => $quiz->time_limit_minutes]) }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="small text-muted">{{ __('courses::teacher/messages.quizzes_student.course_label') }} {{ $course->name_locale }}</div>
                        @if ($quiz->lesson)
                            <div class="small text-muted">{{ __('courses::teacher/messages.quizzes_student.lesson_label') }} {{ $quiz->lesson->name_locale }}</div>
                        @endif
                        @if ($deadline = ($assignment?->deadline_at ?? $quiz->deadline_at))
                            <div class="small text-danger fw-bold mt-1">
                                {{ __('courses::teacher/messages.quizzes_student.deadline_label') }} {{ $deadline->format('d/m/Y H:i') }}
                                <span class="ms-1" data-quiz-countdown data-deadline="{{ $deadline->toIso8601String() }}"></span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @php
            $deadline = $assignment?->deadline_at ?? $quiz->deadline_at;
            $isExpired = $deadline && now()->greaterThan($deadline);
            $attemptsUsed = $pastSubmissions->count();
            $maxAttempts = $quiz->max_attempts;
            $attemptsLeft = $maxAttempts ? max(0, $maxAttempts - $attemptsUsed) : null;
            $canStart = !$isExpired && ($attemptsLeft === null || $attemptsLeft > 0);
            // Lần làm bài gần nhất (đã hoàn thành)
            $lastDone = $pastSubmissions->first();
        @endphp

        {{-- Lịch sử làm bài --}}
        @if ($pastSubmissions->count())
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">{{ __('courses::teacher/messages.quizzes_student.history_title') }} {{ __('courses::teacher/messages.quizzes_student.history_count', ['count' => $attemptsUsed]) }}</h6>
                    <div class="d-grid gap-2">
                        @foreach ($pastSubmissions as $sub)
                            <div class="d-flex justify-content-between align-items-center border rounded px-3 py-2">
                                <span class="small">{{ __('courses::teacher/messages.quizzes_student.attempt_label', ['count' => $sub->attempt_no]) }} — {{ optional($sub->submitted_at)->format('d/m/Y H:i') ?: __('courses::teacher/messages.quizzes_student.not_submitted') }}</span>
                                <span>
                                    <strong>{{ $sub->score }}%</strong>
                                    <span class="badge ms-1 bg-{{ $sub->passed ? 'success' : 'danger' }}">{{ $sub->passed ? __('courses::teacher/messages.quizzes_student.passed') : __('courses::teacher/messages.quizzes_student.failed') }}</span>
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if ($isExpired)
            <div class="alert alert-danger">{{ __('courses::teacher/messages.quizzes_student.expired_msg', ['date' => optional($deadline)->format('d/m/Y H:i')]) }}</div>
        @elseif ($maxAttempts && $attemptsLeft === 0)
            <div class="alert alert-warning">{{ __('courses::teacher/messages.quizzes_student.out_of_attempts', ['count' => $maxAttempts]) }}</div>
        @elseif (!$activeSubmission)
            {{-- Chưa có lượt làm đang mở → cho phép bắt đầu --}}
            <div class="card mb-4">
                <div class="card-body text-center py-4">
                    @if ($attemptsLeft !== null)
                        <p class="text-muted mb-3">{{ __('courses::teacher/messages.quizzes_student.attempts_left', ['count' => $attemptsLeft]) }}</p>
                    @endif
                    <form method="POST" action="{{ route('teacher.dashboard.quizzes.start', [$course->id, $quiz->id]) }}">
                        @csrf
                        <button class="btn btn-primary btn-lg">
                            {{ $pastSubmissions->count() ? '🔄 ' . __('courses::teacher/messages.quizzes_student.redo_btn') : '▶ ' . __('courses::teacher/messages.quizzes_student.start_btn') }}
                        </button>
                    </form>
                </div>
            </div>
        @else
            {{-- Đang có lượt làm chưa nộp --}}
            @if (session('msg_success'))
                <div class="alert alert-success">{{ session('msg_success') }}</div>
            @endif
            @if (session('msg_danger'))
                <div class="alert alert-danger">{{ session('msg_danger') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif

            <form method="POST" action="{{ route('teacher.dashboard.quizzes.submit', [$course->id, $quiz->id]) }}">
                @csrf
                <input type="hidden" name="submission_id" value="{{ $activeSubmission->id }}">
                <div class="d-grid gap-4">
                    @foreach ($quiz->questions->sortBy('position') as $index => $question)
                        <div class="card">
                            <div class="card-body">
                                <div class="fw-semibold mb-3">{{ __('courses::teacher/messages.quizzes_student.question_label', ['count' => $index + 1]) }}. {{ $question->question }}
                                    @if ($question->points > 1)
                                        <span class="badge bg-light text-dark ms-1">({{ __('courses::teacher/messages.quizzes_student.points_unit', ['count' => $question->points]) }})</span>
                                    @endif
                                </div>
                                <input type="hidden" name="answers[{{ $index }}][question_id]" value="{{ $question->id }}">

                                @if ($question->question_type === 'multiple_choice')
                                    {{-- Nhiều đáp án: dùng checkbox --}}
                                    <div class="d-grid gap-2">
                                        @foreach ($question->choices->sortBy('position') as $choice)
                                            <label class="form-check border rounded p-2 mb-0 d-flex align-items-center gap-2" style="cursor:pointer">
                                                <input class="form-check-input flex-shrink-0"
                                                    type="checkbox"
                                                    name="answers[{{ $index }}][choice_ids][]"
                                                    value="{{ $choice->id }}">
                                                {{ $choice->choice_text }}
                                            </label>
                                        @endforeach
                                    </div>
                                @elseif ($question->question_type === 'short_answer')
                                    <textarea name="answers[{{ $index }}][answer_text]"
                                        class="form-control" rows="3"
                                        placeholder="{{ __('courses::teacher/messages.quizzes_student.short_answer_placeholder') }}"></textarea>
                                @else
                                    {{-- single_choice / true_false: dùng radio --}}
                                    <div class="d-grid gap-2">
                                        @foreach ($question->choices->sortBy('position') as $choice)
                                            <label class="form-check border rounded p-2 mb-0 d-flex align-items-center gap-2" style="cursor:pointer">
                                                <input class="form-check-input flex-shrink-0"
                                                    type="radio"
                                                    name="answers[{{ $index }}][choice_id]"
                                                    value="{{ $choice->id }}">
                                                {{ $choice->choice_text }}
                                            </label>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary btn-lg" onclick="return confirm('{{ __('courses::teacher/messages.quizzes_student.confirm_submit') }}')">
                        📤 {{ __('courses::teacher/messages.quizzes_student.submit_btn') }}
                    </button>
                </div>
            </form>
        @endif
    </div>

    <script>
        (() => {
            const el = document.querySelector('[data-quiz-countdown]');
            if (!el) return;
            const deadline = new Date(el.dataset.deadline);
            const tick = () => {
                const diff = deadline.getTime() - Date.now();
                if (diff <= 0) { el.textContent = "{{ __('courses::teacher/messages.quizzes_student.countdown_expired') }}"; return; }
                const h = Math.floor(diff / 3600000);
                const m = Math.floor((diff % 3600000) / 60000);
                el.textContent = "{{ __('courses::teacher/messages.quizzes_student.countdown_remaining', ['time' => '']) }}".replace(':time', `${h > 0 ? h + 'h ' : ''}${m}p`);
            };
            tick();
            setInterval(tick, 30000);
        })();
    </script>
@endsection
