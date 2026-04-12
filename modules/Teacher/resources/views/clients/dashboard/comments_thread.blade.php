@php
    $teacherStudentId = $teacher?->student_id;
    $teacherCanToggleComments = $teacher?->packageHasFeature('can_manage_comments') ?? false;
    $resolveRole = function ($comment) use ($teacherStudentId) {
        if ($comment->user_id) {
            return 'admin';
        }
        if ($teacherStudentId && (int) $comment->student_id === (int) $teacherStudentId) {
            return 'teacher';
        }
        return 'student';
    };
    $resolveRoleLabel = function (string $role) {
        return match ($role) {
            'admin' => __('teacher::comments.roles.admin'),
            'teacher' => __('teacher::comments.roles.teacher'),
            default => __('teacher::comments.roles.student'),
        };
    };
@endphp

<div class="teacher-comments-thread" data-teacher-comments>
    <div class="teacher-comments-thread__header">
        <div>
            <div class="teacher-comments-thread__eyebrow">{{ __('teacher::comments.thread.eyebrow') }}</div>
            <h4 class="teacher-comments-thread__title mb-1">{{ $course->name_locale ?: $course->name }}</h4>
            <p class="teacher-comments-thread__meta mb-0">
                {{ __('teacher::comments.thread.meta', ['count' => $threads->count()]) }}
            </p>
        </div>
        <a href="{{ route('courses.detail', ['locale' => app()->getLocale(), 'slug' => $course->slug_locale]) }}#evaluate"
            class="btn btn-outline-secondary btn-sm">
            {{ __('teacher::comments.thread.open_course') }}
        </a>
    </div>

    <div class="teacher-comments-thread__list" data-teacher-comments-list>
        @forelse ($threads as $comment)
            @php
                $commentRole = $resolveRole($comment);
            @endphp
            <div class="teacher-comment-item {{ !$comment->is_visible ? 'is-hidden-comment' : '' }}">
                <div class="teacher-comment-card {{ $commentRole === 'admin' ? 'is-admin' : 'is-student' }}">
                    <img src="{{ courseCommentAvatar($comment->id . '-' . $commentRole) }}"
                        alt="{{ $comment->author_name }}" class="comment-avatar">
                    <div class="comment-main">
                        <div class="comment-meta">
                            <strong>{{ $comment->author_name }}</strong>
                            <span class="badge {{ $commentRole === 'admin' ? 'bg-primary' : 'bg-success' }}">
                                {{ $resolveRoleLabel($commentRole) }}
                            </span>
                            <span class="comment-time">{{ optional($comment->created_at)->format('d/m/Y H:i:s') }}</span>
                            @if ($comment->is_flagged)
                                <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">
                                    {{ __('teacher::comments.comment.flag_badge') }}
                                </span>
                            @endif
                            @if (!$comment->is_visible)
                                <span class="badge bg-secondary">{{ __('teacher::comments.comment.hidden_badge') }}</span>
                            @endif
                        </div>
                        <div class="comment-content">{{ $comment->content }}</div>
                        @if ($comment->is_flagged && $comment->flagged_terms)
                            <div class="comment-flag-note">{{ __('teacher::comments.comment.flag_terms') }}: {{ $comment->flagged_terms }}</div>
                        @endif
                        @if ($teacherCanToggleComments)
                            <div class="comment-actions">
                                <button type="button"
                                    class="btn btn-outline-secondary btn-sm"
                                    data-teacher-comment-toggle
                                    data-action="{{ route('teacher.dashboard.comments.toggle', ['comment' => $comment->id]) }}">
                                    {{ $comment->is_visible ? __('teacher::comments.comment.hide') : __('teacher::comments.comment.show') }}
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                @if ($comment->replies->isNotEmpty())
                    <div class="comment-replies">
                        @foreach ($comment->replies as $reply)
                            @php
                                $replyRole = $resolveRole($reply);
                            @endphp
                            <div class="comment-card reply-card {{ !$reply->is_visible ? 'is-hidden-comment' : '' }} {{ $replyRole === 'admin' ? 'is-admin' : 'is-student' }}">
                                <img src="{{ courseCommentAvatar($reply->id . '-' . $replyRole) }}"
                                    alt="{{ $reply->author_name }}" class="comment-avatar">
                                <div class="comment-main">
                                    <div class="comment-meta">
                                        <strong>{{ $reply->author_name }}</strong>
                                        <span class="badge {{ $replyRole === 'admin' ? 'bg-primary' : 'bg-success' }}">
                                            {{ $resolveRoleLabel($replyRole) }}
                                        </span>
                                        <span class="comment-time">{{ optional($reply->created_at)->format('d/m/Y H:i:s') }}</span>
                                        @if ($reply->is_flagged)
                                            <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">{{ __('teacher::comments.comment.flag_badge') }}</span>
                                        @endif
                                        @if (!$reply->is_visible)
                                            <span class="badge bg-secondary">{{ __('teacher::comments.comment.hidden_badge') }}</span>
                                        @endif
                                    </div>
                                    <div class="comment-content">{{ $reply->content }}</div>
                                    @if ($reply->is_flagged && $reply->flagged_terms)
                                        <div class="comment-flag-note">{{ __('teacher::comments.comment.flag_terms') }}: {{ $reply->flagged_terms }}</div>
                                    @endif
                                    @if ($teacherCanToggleComments)
                                        <div class="comment-actions">
                                            <button type="button"
                                                class="btn btn-outline-secondary btn-sm"
                                                data-teacher-comment-toggle
                                                data-action="{{ route('teacher.dashboard.comments.toggle', ['comment' => $reply->id]) }}">
                                                {{ $reply->is_visible ? __('teacher::comments.comment.hide_reply') : __('teacher::comments.comment.show_reply') }}
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <form class="course-comment-form admin-reply-form"
                    data-teacher-comment-form
                    action="{{ route('teacher.dashboard.comments.reply', ['comment' => $comment->id]) }}"
                    method="POST">
                    @csrf
                    <textarea name="content" rows="2" class="form-control" maxlength="2000"
                        placeholder="{{ __('teacher::comments.comment.reply_placeholder') }}"></textarea>
                    <div class="text-end mt-2">
                        <button type="submit" class="btn btn-dark btn-sm">{{ __('teacher::comments.comment.reply_submit') }}</button>
                    </div>
                </form>
            </div>
        @empty
            <div class="empty-comments">{{ __('teacher::comments.comment.empty') }}</div>
        @endforelse
    </div>
</div>

<style>
    .teacher-comments-thread {
        border: 1px solid rgba(96, 165, 250, 0.16);
        border-radius: 22px;
        background: rgba(18, 28, 50, 0.72);
        box-shadow: 0 18px 42px rgba(2, 6, 23, 0.18);
        padding: 1.35rem;
    }

    .teacher-comments-thread__header {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(96, 165, 250, 0.12);
        margin-bottom: 1rem;
    }

    .teacher-comments-thread__eyebrow {
        display: inline-flex;
        padding: 0.3rem 0.7rem;
        border-radius: 999px;
        background: rgba(59, 130, 246, 0.16);
        color: #93c5fd;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .teacher-comments-thread__title {
        color: #f8fbff;
        font-weight: 800;
        font-size: 1.35rem;
    }

    .teacher-comments-thread__meta {
        color: #a9bbd5;
    }

    .teacher-comment-item + .teacher-comment-item {
        margin-top: 1.25rem;
        padding-top: 1.25rem;
        border-top: 1px dashed rgba(148, 163, 184, 0.2);
    }

    .teacher-comment-card,
    .comment-card {
        border-radius: 18px;
        padding: 1rem;
        background: rgba(11, 19, 36, 0.72);
        border: 1px solid rgba(96, 165, 250, 0.12);
    }

    .comment-avatar {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        object-fit: cover;
        margin-right: 0.9rem;
    }

    .comment-main {
        flex: 1;
    }

    .comment-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        align-items: center;
        margin-bottom: 0.55rem;
        color: #cbd5f5;
    }

    .comment-content {
        color: #e2e8f0;
        line-height: 1.7;
    }

    .comment-actions {
        margin-top: 0.75rem;
    }

    .comment-flag-note {
        margin-top: 0.55rem;
        color: #fca5a5;
        font-size: 0.85rem;
    }

    .comment-replies {
        margin-left: 2.5rem;
        margin-top: 0.9rem;
        display: grid;
        gap: 0.75rem;
    }

    .reply-card {
        background: rgba(15, 23, 42, 0.82);
    }

    .course-comment-form textarea {
        background: rgba(8, 14, 28, 0.9);
        border: 1px solid rgba(59, 130, 246, 0.3);
        color: #e2e8f0;
    }

    .course-comment-form textarea:focus {
        border-color: rgba(59, 130, 246, 0.6);
        box-shadow: 0 0 0 0.15rem rgba(59, 130, 246, 0.2);
    }

    .admin-reply-form {
        margin-top: 0.85rem;
    }

    .empty-comments {
        padding: 1.25rem;
        border-radius: 18px;
        text-align: center;
        color: #a9bbd5;
        background: rgba(11, 19, 36, 0.6);
        border: 1px dashed rgba(96, 165, 250, 0.2);
    }

    html[data-theme="light"] .teacher-comments-thread {
        background: var(--admin-surface);
        border-color: var(--admin-border);
        box-shadow: var(--admin-card-shadow);
    }

    html[data-theme="light"] .teacher-comments-thread__eyebrow {
        background: rgba(37, 99, 235, 0.12);
        color: #1d4ed8;
    }

    html[data-theme="light"] .teacher-comments-thread__title {
        color: var(--admin-text);
    }

    html[data-theme="light"] .teacher-comments-thread__meta {
        color: var(--admin-muted);
    }

    html[data-theme="light"] .teacher-comment-card,
    html[data-theme="light"] .comment-card,
    html[data-theme="light"] .reply-card {
        background: var(--admin-subtle-bg);
        border-color: var(--admin-border);
    }

    html[data-theme="light"] .comment-meta {
        color: var(--admin-muted);
    }

    html[data-theme="light"] .comment-content {
        color: var(--admin-text);
    }

    html[data-theme="light"] .comment-flag-note {
        color: #b91c1c;
    }

    html[data-theme="light"] .course-comment-form textarea {
        background: var(--admin-input-bg);
        border-color: var(--admin-border);
        color: var(--admin-text);
    }

    html[data-theme="light"] .course-comment-form textarea:focus {
        border-color: color-mix(in srgb, var(--admin-primary) 60%, var(--admin-border));
        box-shadow: 0 0 0 0.15rem color-mix(in srgb, var(--admin-primary) 18%, transparent);
    }

    html[data-theme="light"] .empty-comments {
        background: var(--admin-subtle-bg);
        border-color: var(--admin-border);
        color: var(--admin-muted);
    }

    @media (max-width: 767.98px) {
        .comment-replies {
            margin-left: 0.75rem;
        }
    }
</style>
