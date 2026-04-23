<article class="teacher-stat-card h-100">
    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
        <div>
            <div class="text-uppercase small text-muted mb-1">
                {{ $lesson->parent_id ? __('lessons::teacher/messages.lesson_label') : __('lessons::teacher/messages.module_label') }}
            </div>
            <strong class="d-block">{{ $lesson->name_locale }}</strong>
            <div class="text-muted small mt-1">
                {{ __('lessons::teacher/messages.labels.position', ['position' => $lesson->position]) }}
            </div>
        </div>
        <span class="teacher-chip">
            {{ $lesson->status ? __('teacher::teacher/course/common.status.published') : __('teacher::teacher/course/common.status.draft') }}
        </span>
    </div>

    <div class="d-flex flex-wrap gap-2 small mb-3">
        @if ($lesson->parent_id)
            <span class="teacher-chip">{{ __('lessons::teacher/messages.labels.duration', ['duration' => getTime($lesson->durations)]) }}</span>
            <span class="teacher-chip">{{ __('lessons::teacher/messages.labels.trial', ['value' => $lesson->is_trial ? __('courses::teacher/messages.common.yes') : __('courses::teacher/messages.common.no')]) }}</span>
            @if (app(\Modules\Lessons\src\Support\LessonReleaseManager::class)->hasPendingRelease($lesson))
                <span class="teacher-chip" style="background: rgba(245, 158, 11, 0.16); color: #fbbf24; border-color: rgba(245, 158, 11, 0.3);">{{ __('lessons::teacher/messages.scheduling.pending_badge') }}</span>
            @elseif ($lesson->release_mode === \Modules\Lessons\src\Support\LessonReleaseManager::MODE_AFTER_PREVIOUS_COMPLETED)
                <span class="teacher-chip" style="background: rgba(56, 189, 248, 0.16); color: #67e8f9; border-color: rgba(56, 189, 248, 0.28);">{{ __('lessons::teacher/messages.scheduling.progress_badge') }}</span>
            @endif
            <span class="teacher-chip">{{ $lesson->releaseSummary() ?: __('lessons::teacher/messages.scheduling.immediate') }}</span>
        @endif
        <span class="teacher-chip">{{ __('lessons::teacher/messages.labels.document', ['value' => $lesson->document_id ? __('courses::teacher/messages.common.yes') : __('courses::teacher/messages.common.no')]) }}</span>
    </div>

    @if (
        $lesson->parent_id &&
        $lesson->release_mode === \Modules\Lessons\src\Support\LessonReleaseManager::MODE_AFTER_PREVIOUS_COMPLETED &&
        !empty($previousLesson)
    )
        <div class="small mb-3" style="color: #93c5fd;">
            {{ __('lessons::teacher/messages.scheduling.waiting_on_previous', ['lesson' => $previousLesson->name_locale]) }}
        </div>
    @endif

    <div class="d-flex flex-wrap gap-2">
        @if($maintCourse ?? false)
            <button class="btn btn-sm btn-outline-secondary" disabled title="{{ __('lessons::teacher/messages.actions.edit') }} ({{ __('teacher::teacher/dashboard.common.maintenance_badge') ?? 'BAO TRI' }})">
                <i class="fa-solid fa-pen-to-square"></i>
            </button>
        @else
            <a href="{{ route('teacher.dashboard.lessons.edit', [$course->id, $lesson->id]) }}" class="btn btn-sm btn-outline-primary" title="{{ __('lessons::teacher/messages.actions.edit') }}">
                <i class="fa-solid fa-pen-to-square"></i>
            </a>
        @endif
        @if ($lesson->parent_id && $lesson->video)
            <button type="button" class="btn btn-sm btn-outline-info js-lesson-preview-btn" 
                data-id="{{ $lesson->id }}" 
                data-url="{{ route('teacher.dashboard.lessons.preview_data', [$course->id, $lesson->id]) }}"
                title="{{ __('lessons::teacher/messages.actions.preview_video') }}">
                <i class="fa-solid fa-play"></i>
            </button>
        @endif
        @if ($lesson->parent_id && isset($teacher) && $teacher?->packageHasFeature('can_manage_quizzes'))
            @if($maintQuizzes ?? false)
                <button class="btn btn-sm btn-outline-success" disabled>
                    {{ __('lessons::teacher/messages.actions.create_quiz') }} ({{ __('teacher::teacher/dashboard.common.maintenance_badge') ?? 'BAO TRI' }})
                </button>
            @else
                <a href="{{ route('teacher.dashboard.quizzes.index', $course->id) }}" class="btn btn-sm btn-outline-success">
                    {{ __('lessons::teacher/messages.actions.create_quiz') }}
                </a>
            @endif
        @elseif ($lesson->parent_id)
            <a href="{{ route('teacher.dashboard.package.upgrade') }}" class="btn btn-sm btn-outline-warning" title="{{ __('lessons::teacher/messages.actions.upgrade_to_create_quiz') }}">
                {{ __('lessons::teacher/messages.actions.create_quiz') }} <span class="ms-1 badge bg-warning text-dark">{{ __('lessons::teacher/messages.labels.locked_badge') }}</span>
            </a>
        @endif
        @if (!$lesson->parent_id)
            @if($maintCourse ?? false)
                <button class="btn btn-sm btn-outline-secondary" disabled>
                    {{ __('lessons::teacher/messages.actions.add_child') }} ({{ __('teacher::teacher/dashboard.common.maintenance_badge') ?? 'BAO TRI' }})
                </button>
            @else
                <a href="{{ route('teacher.dashboard.lessons.create', [$course->id, 'module' => $lesson->id]) }}" class="btn btn-sm btn-outline-secondary">
                    {{ __('lessons::teacher/messages.actions.add_child') }}
                </a>
            @endif
        @endif
        @if($maintCourse ?? false)
            <button type="button" class="btn btn-sm btn-outline-danger" disabled>
                {{ __('lessons::teacher/messages.actions.delete') }} ({{ __('teacher::teacher/dashboard.common.maintenance_badge') ?? 'BAO TRI' }})
            </button>
        @else
            <form method="POST" action="{{ route('teacher.dashboard.lessons.delete', [$course->id, $lesson->id]) }}" onsubmit="return confirm('{{ __('lessons::teacher/messages.confirm_delete') }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    {{ __('lessons::teacher/messages.actions.delete') }}
                </button>
            </form>
        @endif
    </div>
</article>
