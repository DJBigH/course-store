@extends('layouts.client')

@section('content')
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
                            <span class="badge bg-primary">{{ $quiz->questions->count() }} câu hỏi</span>
                            <span class="badge bg-secondary">Đạt {{ $quiz->passing_score }}%</span>
                            @if ($quiz->max_attempts)
                                <span class="badge bg-info">Tối đa {{ $quiz->max_attempts }} lần làm</span>
                            @endif
                            @if ($quiz->time_limit_minutes)
                                <span class="badge bg-warning text-dark">⏱ {{ $quiz->time_limit_minutes }} phút</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="small text-muted">Khóa học: {{ $course->name_locale }}</div>
                        @if ($quiz->lesson)
                            <div class="small text-muted">Bài học: {{ $quiz->lesson->name_locale }}</div>
                        @endif
                        @if ($deadline = ($assignment?->deadline_at ?? $quiz->deadline_at))
                            <div class="small text-danger fw-bold mt-1">
                                Hạn: {{ $deadline->format('d/m/Y H:i') }}
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
                    <h6 class="fw-bold mb-3">Lịch sử làm bài ({{ $attemptsUsed }} lượt)</h6>
                    <div class="d-grid gap-2">
                        @foreach ($pastSubmissions as $sub)
                            <div class="d-flex justify-content-between align-items-center border rounded px-3 py-2">
                                <span class="small">Lượt {{ $sub->attempt_no }} — {{ optional($sub->submitted_at)->format('d/m/Y H:i') ?: 'Chưa nộp' }}</span>
                                <span>
                                    <strong>{{ $sub->score }}%</strong>
                                    <span class="badge ms-1 bg-{{ $sub->passed ? 'success' : 'danger' }}">{{ $sub->passed ? 'Đạt' : 'Chưa đạt' }}</span>
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if ($isExpired)
            <div class="alert alert-danger">Quiz đã hết hạn lúc {{ optional($deadline)->format('d/m/Y H:i') }}.</div>
        @elseif ($maxAttempts && $attemptsLeft === 0)
            <div class="alert alert-warning">Bạn đã dùng hết {{ $maxAttempts }} lượt làm bài.</div>
        @elseif (!$activeSubmission)
            {{-- Chưa có lượt làm đang mở → cho phép bắt đầu --}}
            <div class="card mb-4">
                <div class="card-body text-center py-4">
                    @if ($attemptsLeft !== null)
                        <p class="text-muted mb-3">Còn lại <strong>{{ $attemptsLeft }}</strong> lượt làm.</p>
                    @endif
                    <form method="POST" action="{{ route('teacher.dashboard.quizzes.start', [$course->id, $quiz->id]) }}">
                        @csrf
                        <button class="btn btn-primary btn-lg">
                            {{ $pastSubmissions->count() ? '🔄 Làm lại' : '▶ Bắt đầu làm bài' }}
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
                                <div class="fw-semibold mb-3">Câu {{ $index + 1 }}. {{ $question->question }}
                                    @if ($question->points > 1)
                                        <span class="badge bg-light text-dark ms-1">({{ $question->points }} điểm)</span>
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
                                        placeholder="Nhập câu trả lời của bạn..."></textarea>
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
                    <button class="btn btn-primary btn-lg" onclick="return confirm('Bạn chắc chắn muốn nộp bài?')">
                        📤 Nộp bài
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
                if (diff <= 0) { el.textContent = '(Đã hết hạn)'; return; }
                const h = Math.floor(diff / 3600000);
                const m = Math.floor((diff % 3600000) / 60000);
                el.textContent = `(còn ${h > 0 ? h + 'h ' : ''}${m}p)`;
            };
            tick();
            setInterval(tick, 30000);
        })();
    </script>
@endsection
