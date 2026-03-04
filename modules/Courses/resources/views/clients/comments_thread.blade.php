<div class="course-comments-shell">
    <div class="course-comments-head">
        <div>
            <h3 class="mb-1">{{ __('courses::clients/common.student_reviews') }}</h3>
            <p class="text-muted mb-0">{{ $threads->count() }} trao đổi hiển thị</p>
        </div>
    </div>

    @if (auth('students')->check() && $canComment)
        <form class="course-comment-form mt-3" data-comment-form
            action="{{ route('courses.comments.store', ['locale' => app()->getLocale(), 'slug' => $course->slug_locale]) }}"
            method="POST">
            @csrf
            <textarea name="content" rows="3" class="form-control" maxlength="2000"
                placeholder="Đặt câu hỏi hoặc chia sẻ cảm nhận của bạn..."></textarea>
            <div class="d-flex justify-content-between align-items-center mt-2">
                <small class="text-muted">Chỉ học viên đã mua khoá học mới có thể bình luận.</small>
                <button type="submit" class="btn btn-primary btn-sm">Gửi bình luận</button>
            </div>
        </form>
    @elseif (auth('students')->check())
        <div class="alert alert-warning mt-3 mb-0">Bạn cần mua khoá học trước khi bình luận.</div>
    @else
        <div class="alert alert-info mt-3 mb-0">Đăng nhập học viên và mua khoá học để tham gia hỏi đáp.</div>
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
                                {{ $comment->author_role === 'admin' ? 'Admin' : 'Hoc vien' }}
                            </span>
                            <span class="comment-time">{{ optional($comment->created_at)->format('d/m/Y H:i:s') }}</span>
                            @if ($comment->is_flagged)
                                <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">
                                    Flag
                                </span>
                            @endif
                            @if (!$comment->is_visible)
                                <span class="badge bg-secondary">Đang ẩn</span>
                            @endif
                        </div>
                        <div class="comment-content">{{ $comment->content }}</div>
                        @if ($comment->is_flagged && $viewerIsAdmin && $comment->flagged_terms)
                            <div class="comment-flag-note">Từ khóa nhạy cảm: {{ $comment->flagged_terms }}</div>
                        @endif
                        @if ($viewerIsAdmin)
                            <div class="comment-actions">
                                <button type="button"
                                    class="btn btn-outline-secondary btn-sm"
                                    data-visibility-form
                                    data-action="{{ route('courses.comments.toggle', ['locale' => app()->getLocale(), 'slug' => $course->slug_locale, 'commentId' => $comment->id]) }}">
                                    {{ $comment->is_visible ? 'Ẩn bình luận' : 'Hiện bình luận' }}
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
                                            {{ $reply->author_role === 'admin' ? 'Admin' : 'Học viên' }}
                                        </span>
                                        <span
                                            class="comment-time">{{ optional($reply->created_at)->format('d/m/Y H:i:s') }}</span>
                                        @if ($reply->is_flagged)
                                            <span
                                                class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">Flag</span>
                                        @endif
                                        @if (!$reply->is_visible)
                                            <span class="badge bg-secondary">Đang ẩn</span>
                                        @endif
                                    </div>
                                    <div class="comment-content">{{ $reply->content }}</div>
                                    @if ($reply->is_flagged && $viewerIsAdmin && $reply->flagged_terms)
                                        <div class="comment-flag-note">Từ khoá nhạy cảm: {{ $reply->flagged_terms }}
                                        </div>
                                    @endif
                                    @if ($viewerIsAdmin)
                                        <div class="comment-actions">
                                            <button type="button"
                                                class="btn btn-outline-secondary btn-sm"
                                                data-visibility-form
                                                data-action="{{ route('courses.comments.toggle', ['locale' => app()->getLocale(), 'slug' => $course->slug_locale, 'commentId' => $reply->id]) }}">
                                                {{ $reply->is_visible ? 'Ẩn phản hồi' : 'Hiện phản hồi' }}
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
                        <textarea name="content" rows="2" class="form-control" maxlength="2000"
                            placeholder="Trả lời học viên tại đây..."></textarea>
                        <div class="text-end mt-2">
                            <button type="submit" class="btn btn-dark btn-sm">Trả lời với tư cách admin</button>
                        </div>
                    </form>
                @endif
            </div>
        @empty
            <div class="empty-comments">
                Chưa có bình luận nào. Hãy mở đầu cuộc trò chuyện đầu tiên.
            </div>
        @endforelse
    </div>
</div>
