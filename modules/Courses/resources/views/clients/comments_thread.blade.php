<div class="course-comments-shell">
    <div class="course-comments-head">
        <div>
            <h3 class="mb-1">{{ __('courses::clients/common.student_reviews') }}</h3>
            <p class="text-muted mb-0">{{ $threads->count() }} {{ __('courses::clients/common.comment_threads') }}</p>
        </div>
    </div>

    @if (auth('students')->check() && $canComment)
        <form class="course-comment-form mt-3" data-comment-form
            action="{{ route('courses.comments.store', ['locale' => app()->getLocale(), 'slug' => $course->slug_locale]) }}"
            method="POST">
            @csrf
            <textarea name="content" rows="3" class="form-control ckeditor" data-rich-editor maxlength="2000"
                placeholder="{{ __('courses::clients/common.comment_placeholder') }}"></textarea>
            <div class="d-flex justify-content-between align-items-center mt-2">
                <small class="text-muted">{{ __('courses::clients/common.comment_purchase_only') }}</small>
                <button type="submit" class="btn btn-primary btn-sm">{{ __('courses::clients/common.comment_submit') }}</button>
            </div>
        </form>
    @elseif (auth('students')->check())
        <div class="alert alert-warning mt-3 mb-0">{{ __('courses::clients/common.comment_need_purchase') }}</div>
    @else
        <div class="alert alert-info mt-3 mb-0">{{ __('courses::clients/common.comment_login_to_join') }}</div>
    @endif

    <div class="course-comments-list mt-4">
        @forelse ($threads as $comment)
            <div class="comment-thread {{ !$comment->is_visible ? 'is-hidden-comment' : '' }}">
                <div
                    class="comment-card {{ $comment->author_role === 'admin' ? 'is-admin' : 'is-student' }}">
                    <img src="{{ courseCommentAvatar($comment->id . '-' . $comment->author_role) }}"
                        alt="{{ $comment->author_name }}" class="comment-avatar">
                    <div class="comment-main">
                        <div class="comment-meta">
                            <strong>{{ $comment->author_name }}</strong>
                            <span class="badge {{ $comment->author_role === 'admin' ? 'bg-primary' : 'bg-success' }}">
                                {{ $comment->author_role === 'admin' ? __('courses::clients/common.comment_role_admin') : __('courses::clients/common.comment_role_student') }}
                            </span>
                            <span class="comment-time">{{ optional($comment->created_at)->format('d/m/Y H:i:s') }}</span>
                            @if ($comment->is_flagged)
                                <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">
                                    {{ __('courses::clients/common.comment_flag_badge') }}
                                </span>
                            @endif
                            @if (!$comment->is_visible)
                                <span class="badge bg-secondary">{{ __('courses::clients/common.comment_hidden_badge') }}</span>
                            @endif
                        </div>
                        <div class="comment-content">{{ $comment->content }}</div>
                        @if ($comment->is_flagged && $viewerIsAdmin && $comment->flagged_terms)
                            <div class="comment-flag-note">{{ __('courses::clients/common.comment_flagged_terms') }}: {{ $comment->flagged_terms }}</div>
                        @endif
                        @if ($viewerIsAdmin)
                            <div class="comment-actions">
                                <button type="button"
                                    class="btn btn-outline-secondary btn-sm"
                                    data-visibility-form
                                    data-action="{{ route('courses.comments.toggle', ['locale' => app()->getLocale(), 'slug' => $course->slug_locale, 'commentId' => $comment->id]) }}">
                                    {{ $comment->is_visible ? __('courses::clients/common.comment_hide') : __('courses::clients/common.comment_show') }}
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                @if ($comment->replies->isNotEmpty())
                    <div class="comment-replies">
                        @foreach ($comment->replies as $reply)
                            <div class="comment-card reply-card {{ !$reply->is_visible ? 'is-hidden-comment' : '' }} {{ $reply->author_role === 'admin' ? 'is-admin' : 'is-student' }}">
                                <img src="{{ courseCommentAvatar($reply->id . '-' . $reply->author_role) }}"
                                    alt="{{ $reply->author_name }}" class="comment-avatar">
                                <div class="comment-main">
                                    <div class="comment-meta">
                                        <strong>{{ $reply->author_name }}</strong>
                                        <span
                                            class="badge {{ $reply->author_role === 'admin' ? 'bg-primary' : 'bg-success' }}">
                                            {{ $reply->author_role === 'admin' ? __('courses::clients/common.comment_role_admin') : __('courses::clients/common.comment_role_student') }}
                                        </span>
                                        <span
                                            class="comment-time">{{ optional($reply->created_at)->format('d/m/Y H:i:s') }}</span>
                                        @if ($reply->is_flagged)
                                            <span
                                                class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">{{ __('courses::clients/common.comment_flag_badge') }}</span>
                                        @endif
                                        @if (!$reply->is_visible)
                                            <span class="badge bg-secondary">{{ __('courses::clients/common.comment_hidden_badge') }}</span>
                                        @endif
                                    </div>
                                    <div class="comment-content">{{ $reply->content }}</div>
                                    @if ($reply->is_flagged && $viewerIsAdmin && $reply->flagged_terms)
                                        <div class="comment-flag-note">{{ __('courses::clients/common.comment_flagged_terms') }}: {{ $reply->flagged_terms }}
                                        </div>
                                    @endif
                                    @if ($viewerIsAdmin)
                                        <div class="comment-actions">
                                            <button type="button"
                                                class="btn btn-outline-secondary btn-sm"
                                                data-visibility-form
                                                data-action="{{ route('courses.comments.toggle', ['locale' => app()->getLocale(), 'slug' => $course->slug_locale, 'commentId' => $reply->id]) }}">
                                                {{ $reply->is_visible ? __('courses::clients/common.comment_hide_reply') : __('courses::clients/common.comment_show_reply') }}
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if ($viewerIsAdmin)
                    <form class="course-comment-form admin-reply-form"
                        data-comment-form
                        action="{{ route('courses.comments.reply', ['locale' => app()->getLocale(), 'slug' => $course->slug_locale, 'commentId' => $comment->id]) }}"
                        method="POST">
                        @csrf
                        <textarea name="content" rows="2" class="form-control" data-rich-editor maxlength="2000"
                            placeholder="{{ __('courses::clients/common.comment_reply_placeholder') }}"></textarea>
                        <div class="text-end mt-2">
                            <button type="submit" class="btn btn-dark btn-sm">{{ __('courses::clients/common.comment_reply_as_admin') }}</button>
                        </div>
                    </form>
                @endif
            </div>
        @empty
            <div class="empty-comments">
                {{ __('courses::clients/common.comment_empty') }}
            </div>
        @endforelse
    </div>
</div>
