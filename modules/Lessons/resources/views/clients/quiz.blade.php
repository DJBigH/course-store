@php
    $lessonQuizzes = $quizzes->where('lesson_id', $lesson->id);
    $otherQuizzes = $quizzes->where('lesson_id', '!=', $lesson->id);
@endphp

<div class="quiz-container p-3">
    @if($lessonQuizzes->isNotEmpty())
        <div class="quiz-section mb-4">
            <h6 class="quiz-section-title text-uppercase fw-bold mb-3" style="font-size: 12px; color: #3b82f6; letter-spacing: 0.05em;">
                <i class="fa-solid fa-book-open me-1"></i> {{ __('quizzes::clients.lesson_quizzes') }}
            </h6>
            @foreach($lessonQuizzes as $item)
                @include('lessons::clients.quiz_item', ['quiz' => $item])
            @endforeach
        </div>
    @endif

    @if($otherQuizzes->isNotEmpty())
        <div class="quiz-section">
            <h6 class="quiz-section-title text-uppercase fw-bold mb-3" style="font-size: 12px; color: #64748b; letter-spacing: 0.05em;">
                <i class="fa-solid fa-layer-group me-1"></i> {{ __('quizzes::clients.course_quizzes') }}
            </h6>
            @foreach($otherQuizzes as $item)
                @include('lessons::clients.quiz_item', ['quiz' => $item])
            @endforeach
        </div>
    @endif

    @if($quizzes->isEmpty())
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="fa-solid fa-clipboard-question fa-3x text-muted opacity-25"></i>
            </div>
            <p class="text-muted mb-0">{{ __('quizzes::clients.no_quizzes') }}</p>
        </div>
    @endif
</div>

<style>
    .quiz-item {
        padding: 16px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        margin-bottom: 12px;
        background: #fff;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .quiz-item:hover {
        border-color: #3b82f6;
        box-shadow: 0 10px 20px rgba(59, 130, 246, 0.08);
        transform: translateY(-2px);
    }
    .quiz-item__title {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 6px;
        display: block;
        font-size: 0.95rem;
        line-height: 1.4;
    }
    .quiz-item__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        font-size: 12px;
        color: #64748b;
        margin-bottom: 12px;
    }
    .quiz-item__meta-item {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .quiz-item__status {
        display: inline-flex;
        align-items: center;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }
    .status--not-started { background: #f1f5f9; color: #475569; }
    .status--in-progress { background: #eff6ff; color: #2563eb; }
    .status--passed { background: #f0fdf4; color: #16a34a; }
    .status--failed { background: #fef2f2; color: #dc2626; }

    .quiz-item__actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .btn-quiz {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.2s ease;
        text-decoration: none !important;
        width: 100%;
    }
    .btn-quiz--primary {
        background: #2563eb;
        color: #fff;
    }
    .btn-quiz--primary:hover {
        background: #1d4ed8;
        transform: scale(1.02);
    }
    .btn-quiz--outline {
        background: transparent;
        border: 1px solid #e2e8f0;
        color: #475569;
    }
    .btn-quiz--outline:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    html[data-theme="dark"] .quiz-item {
        background: #1e293b;
        border-color: #334155;
    }
    html[data-theme="dark"] .quiz-item__title {
        color: #f8fafc;
    }
    html[data-theme="dark"] .quiz-item__meta {
        color: #94a3b8;
    }
    html[data-theme="dark"] .btn-quiz--outline {
        border-color: #334155;
        color: #cbd5e1;
    }
    html[data-theme="dark"] .btn-quiz--outline:hover {
        background: #0f172a;
    }
</style>
