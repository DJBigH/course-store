<article class="teacher-stat-card h-100">
    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
        <div>
            <div class="text-uppercase small text-muted mb-1">
                {{ $lesson->parent_id ? __('teacher::dashboard.lessons.lesson_label') : __('teacher::dashboard.lessons.module_label') }}
            </div>
            <strong class="d-block">{{ $lesson->name_locale }}</strong>
            <div class="text-muted small mt-1">
                {{ __('teacher::dashboard.lessons.labels.position', ['position' => $lesson->position]) }}
            </div>
        </div>
        <span class="teacher-chip">
            {{ $lesson->status ? __('teacher::dashboard.courses.status.published') : __('teacher::dashboard.courses.status.draft') }}
        </span>
    </div>

    <div class="d-flex flex-wrap gap-2 small mb-3">
        @if ($lesson->parent_id)
            <span class="teacher-chip">{{ __('teacher::dashboard.lessons.labels.duration', ['duration' => getTime($lesson->durations)]) }}</span>
            <span class="teacher-chip">{{ __('teacher::dashboard.lessons.labels.trial', ['value' => $lesson->is_trial ? __('teacher::dashboard.common.yes') : __('teacher::dashboard.common.no')]) }}</span>
            @if (app(\Modules\Lessons\src\Support\LessonReleaseManager::class)->hasPendingRelease($lesson))
                <span class="teacher-chip" style="background: rgba(245, 158, 11, 0.16); color: #fbbf24; border-color: rgba(245, 158, 11, 0.3);">Chưa tới lịch mở</span>
            @elseif ($lesson->release_mode === \Modules\Lessons\src\Support\LessonReleaseManager::MODE_AFTER_PREVIOUS_COMPLETED)
                <span class="teacher-chip" style="background: rgba(56, 189, 248, 0.16); color: #67e8f9; border-color: rgba(56, 189, 248, 0.28);">Mở theo tiến độ</span>
            @endif
            <span class="teacher-chip">{{ $lesson->releaseSummary() }}</span>
        @endif
        <span class="teacher-chip">{{ __('teacher::dashboard.lessons.labels.document', ['value' => $lesson->document_id ? __('teacher::dashboard.common.yes') : __('teacher::dashboard.common.no')]) }}</span>
    </div>

    @if (
        $lesson->parent_id &&
        $lesson->release_mode === \Modules\Lessons\src\Support\LessonReleaseManager::MODE_AFTER_PREVIOUS_COMPLETED &&
        !empty($previousLesson)
    )
        <div class="small mb-3" style="color: #93c5fd;">
            Đang chờ học viên hoàn thành bài <strong>{{ $previousLesson->name_locale }}</strong> để mở bài này.
        </div>
    @endif

    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('teacher.dashboard.lessons.edit', [$course->id, $lesson->id]) }}" class="btn btn-sm btn-outline-primary">
            {{ __('teacher::dashboard.lessons.actions.edit') }}
        </a>
        @if (!$lesson->parent_id)
            <a href="{{ route('teacher.dashboard.lessons.create', [$course->id, 'module' => $lesson->id]) }}" class="btn btn-sm btn-outline-secondary">
                {{ __('teacher::dashboard.lessons.actions.add_child') }}
            </a>
        @endif
        <form method="POST" action="{{ route('teacher.dashboard.lessons.delete', [$course->id, $lesson->id]) }}" onsubmit="return confirm('{{ __('teacher::dashboard.lessons.confirm_delete') }}')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger">
                {{ __('teacher::dashboard.lessons.actions.delete') }}
            </button>
        </form>
    </div>
</article>
