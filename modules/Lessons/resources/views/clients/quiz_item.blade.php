@php
    $submissions = $quizSubmissions->get($quiz->id, collect());
    $lastSubmission = $submissions->first();
    $activeSubmission = $student ? \Modules\Courses\src\Models\CourseQuizSubmission::query()
        ->where('quiz_id', $quiz->id)
        ->where('student_id', $student->id)
        ->whereNull('submitted_at')
        ->first() : null;

    $status = 'not_started';
    $statusLabel = __('quizzes::clients.status.not_started');
    
    if ($activeSubmission) {
        $status = 'in_progress';
        $statusLabel = __('quizzes::clients.status.in_progress');
    } elseif ($lastSubmission) {
        $status = $lastSubmission->passed ? 'passed' : 'failed';
        $statusLabel = $lastSubmission->passed ? __('quizzes::clients.status.passed') : __('quizzes::clients.status.failed');
    }

    $quizUrl = route('teacher.dashboard.quizzes.show', ['course' => $course->id, 'quiz' => $quiz->id]);
    $resultUrl = $lastSubmission ? route('teacher.dashboard.quizzes.result', ['course' => $course->id, 'quiz' => $quiz->id, 'submission' => $lastSubmission->id]) : '#';
@endphp

<div class="cp-quiz-item">
    <div class="cp-quiz-item-header">
        <span class="cp-quiz-title">{{ $quiz->title }}</span>
        <span class="cp-quiz-status status-{{ $status }}">{{ $statusLabel }}</span>
    </div>
    
    <div class="cp-quiz-meta">
        @if($lastSubmission)
            <div class="cp-quiz-meta-item">
                <i class="fa-solid fa-chart-simple"></i>
                {{ __('quizzes::clients.score', ['score' => $lastSubmission->score]) }}
            </div>
        @endif
        <div class="cp-quiz-meta-item">
            <i class="fa-solid fa-rotate-left"></i>
            {{ __('quizzes::clients.attempts', ['count' => $submissions->count()]) }}
        </div>
        @if($quiz->deadline_at)
            <div class="cp-quiz-meta-item">
                <i class="fa-regular fa-clock"></i>
                {{ __('quizzes::clients.deadline', ['date' => $quiz->deadline_at->format('d/m/Y H:i')]) }}
            </div>
        @endif
    </div>

    <div class="cp-quiz-actions">
        @if($status === 'not_started' || $status === 'in_progress' || ($quiz->max_attempts && $submissions->count() < $quiz->max_attempts))
            <a href="{{ $quizUrl }}" class="cp-quiz-btn cp-quiz-btn-primary">
                @if($status === 'in_progress')
                    <i class="fa-solid fa-play"></i>
                    {{ __('quizzes::clients.resume') }}
                @else
                    <i class="fa-solid fa-pencil"></i>
                    {{ __('quizzes::clients.start_now') }}
                @endif
            </a>
        @endif
        
        @if($lastSubmission)
            <a href="{{ $resultUrl }}" class="cp-quiz-btn cp-quiz-btn-outline">
                <i class="fa-solid fa-eye"></i>
                {{ __('quizzes::clients.view_result') }}
            </a>
        @endif
    </div>
</div>
