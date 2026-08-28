@php
    $lessonQuizzes = $quizzes->where('lesson_id', $lesson->id);
    $otherQuizzes = $quizzes->where('lesson_id', '!=', $lesson->id);
@endphp

<div class="cp-quiz-container p-3">
    @if($lessonQuizzes->isNotEmpty())
        <div class="cp-quiz-section mb-4">
            <h6 class="cp-quiz-section-title">
                <i class="fa-solid fa-book-open"></i> {{ __('quizzes::clients.lesson_quizzes') }}
            </h6>
            @foreach($lessonQuizzes as $item)
                @include('lessons::clients.quiz_item', ['quiz' => $item])
            @endforeach
        </div>
    @endif

    @if($otherQuizzes->isNotEmpty())
        <div class="cp-quiz-section">
            <h6 class="cp-quiz-section-title muted">
                <i class="fa-solid fa-layer-group"></i> {{ __('quizzes::clients.course_quizzes') }}
            </h6>
            @foreach($otherQuizzes as $item)
                @include('lessons::clients.quiz_item', ['quiz' => $item])
            @endforeach
        </div>
    @endif

    @if($quizzes->isEmpty())
        <div class="text-center py-5 opacity-50">
            <i class="fa-solid fa-clipboard-question d-block mb-2 fs-3"></i>
            <div class="small">{{ __('quizzes::clients.no_quizzes') }}</div>
        </div>
    @endif
</div>

<style>
    .cp-quiz-section-title {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--cp-tab-active);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .cp-quiz-section-title.muted {
        color: var(--cp-text-muted);
    }

    .cp-quiz-item {
        padding: 1.25rem;
        background-color: var(--cp-nav-bg);
        border-radius: 0.75rem;
        margin-bottom: 0.75rem;
        border: 1px solid var(--cp-border);
        transition: all 0.2s;
    }

    .cp-quiz-item:hover {
        border-color: var(--cp-tab-active);
        transform: translateY(-2px);
    }

    .cp-quiz-item-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0.75rem;
        gap: 1rem;
    }

    .cp-quiz-title {
        font-size: 0.9375rem;
        font-weight: 600;
        color: var(--cp-text-main);
        line-height: 1.4;
    }

    .cp-quiz-status {
        font-size: 0.6875rem;
        font-weight: 800;
        text-transform: uppercase;
        padding: 0.25rem 0.625rem;
        border-radius: 999px;
        white-space: nowrap;
    }

    .status-not_started { background-color: var(--cp-sidebar-bg); color: var(--cp-text-muted); }
    .status-in_progress { background-color: rgba(59, 130, 246, 0.15); color: #3b82f6; }
    .status-passed { background-color: rgba(16, 185, 129, 0.15); color: #10b981; }
    .status-failed { background-color: rgba(239, 68, 68, 0.15); color: #ef4444; }

    .cp-quiz-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .cp-quiz-meta-item {
        font-size: 0.75rem;
        color: var(--cp-text-muted);
        display: flex;
        align-items: center;
        gap: 0.375rem;
    }

    .cp-quiz-actions {
        display: flex;
        gap: 0.5rem;
    }

    .cp-quiz-btn {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.625rem;
        border-radius: 0.5rem;
        font-size: 0.8125rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
    }

    .cp-quiz-btn-primary {
        background-color: var(--cp-tab-active);
        color: #fff;
    }

    .cp-quiz-btn-primary:hover {
        opacity: 0.9;
        color: #fff;
    }

    .cp-quiz-btn-outline {
        background-color: var(--cp-sidebar-bg);
        border: 1px solid var(--cp-border);
        color: var(--cp-text-main);
    }

    .cp-quiz-btn-outline:hover {
        background-color: var(--cp-border);
        color: var(--cp-text-main);
    }
</style>
