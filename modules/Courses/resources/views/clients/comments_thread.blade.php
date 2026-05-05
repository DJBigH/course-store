<div class="course-reviews-container pt-4">
    <div class="d-flex align-items-center justify-content-between mb-5">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-comments text-primary me-2"></i>
                {{ __('courses::clients/common.student_reviews') }}
            </h4>
            <p class="text-muted small mb-0">
                {{ $threads->count() }} {{ __('courses::clients/common.comment_threads') }}
            </p>
        </div>
    </div>

    <div id="course-rating-wrap" class="mb-5">
        @include('courses::clients.partials.rating_panel', [
            'course' => $course,
            'canRate' => $canRate ?? false,
            'viewerCourseRating' => $viewerCourseRating ?? null,
            'ratingBreakdown' => $ratingBreakdown ?? []
        ])
    </div>

    <div class="comments-section-title mb-4">
        <h5 class="fw-bold text-dark">{{ __('courses::clients/common.comment_threads') }}</h5>
    </div>

    @if ((auth('students')->check() || auth()->check()) && $canComment)
        <div class="comment-submission-card card border-0 shadow-sm rounded-4 overflow-hidden mb-5 border-start border-primary border-4">
            <div class="card-body p-4">
                <form class="course-comment-form" data-comment-form
                    action="{{ route('courses.comments.store', ['locale' => app()->getLocale(), 'slug' => $course->slug_locale]) }}"
                    method="POST">
                    @csrf
                    <textarea name="content" rows="3" class="form-control border-light shadow-none bg-light rounded-3 mb-3" 
                        data-rich-editor maxlength="2000"
                        placeholder="{{ __('courses::clients/common.comment_placeholder') }}"></textarea>
                    
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-circle-user text-muted fs-5"></i>
                            <span class="fw-medium text-dark small">
                                {{ auth()->check() ? (auth()->user()->name) : auth('students')->user()->name }}
                            </span>
                        </div>
                        <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm fw-bold">
                            <i class="fa-solid fa-paper-plane me-2 small"></i>{{ __('courses::clients/common.comment_submit') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @elseif (auth('students')->check() || auth()->check())
        <div class="alert alert-warning-soft rounded-4 border-0 p-4 mb-5 shadow-sm d-flex align-items-center gap-3">
            <div class="icon-box bg-warning text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; flex-shrink: 0;">
                <i class="fa-solid fa-lock"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-1 text-dark">{{ __('courses::clients/common.comment_need_purchase') }}</h6>
                <p class="mb-0 text-muted small">{{ __('courses::clients/common.comment_purchase_only') }}</p>
            </div>
        </div>
    @else
        <div class="alert alert-info-soft rounded-4 border-0 p-4 mb-5 shadow-sm d-flex align-items-center gap-3">
            <div class="icon-box bg-info text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; flex-shrink: 0;">
                <i class="fa-solid fa-circle-info"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-1 text-dark">{{ __('courses::clients/common.comment_login_to_join') }}</h6>
                <p class="mb-0 text-muted small">{{ __('courses::clients/common.comment_login_required') }}</p>
            </div>
        </div>
    @endif

    @php
        $teacherStudentId = $course->teacher?->student_id;
        $resolveCommentRole = function ($comment) use ($teacherStudentId) {
            if ($comment->user_id) return 'admin';
            if ($teacherStudentId && (int) $comment->student_id === (int) $teacherStudentId) return 'teacher';
            return 'student';
        };
        $resolveRoleLabel = function (string $role, $comment = null) {
            if ($role === 'admin' && $comment && $comment->user_id && $comment->admin) {
                return $comment->admin->group?->name ?? __('courses::clients/common.comment_role_admin');
            }
            return match ($role) {
                'admin' => __('courses::clients/common.comment_role_admin'),
                'teacher' => __('courses::clients/common.comment_role_teacher'),
                default => __('courses::clients/common.comment_role_student'),
            };
        };
    @endphp

    <div class="course-comments-list d-grid gap-4 mt-2">
        @forelse ($threads as $comment)
            @php
                $commentRole = $resolveCommentRole($comment);
            @endphp
            <div class="comment-thread-wrap {{ !$comment->is_visible ? 'opacity-50' : '' }}">
                <article class="comment-card-modern p-4 rounded-4 border bg-white shadow-sm transition-all hover-shadow {{ $commentRole === 'admin' ? 'border-primary-subtle' : '' }}">
                    <div class="d-flex gap-3">
                        <div class="comment-body flex-grow-1">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <h6 class="fw-bold mb-0 text-dark">{{ $comment->author_name }}</h6>
                                    <span class="badge rounded-pill {{ $commentRole === 'admin' ? 'bg-primary' : ($commentRole === 'teacher' ? 'bg-warning text-dark' : 'bg-light text-secondary border') }}" style="font-size: 10px;">
                                        {{ $resolveRoleLabel($commentRole, $comment) }}
                                    </span>
                                </div>
                                <time class="text-muted small opacity-75">{{ optional($comment->created_at)->diffForHumans() }}</time>
                            </div>
                            <div class="comment-text text-secondary mb-3" style="line-height: 1.6; font-size: 0.95rem;">{!! $comment->content !!}</div>
                            
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="comment-actions d-flex gap-3">
                                    @if ($comment->is_flagged)
                                        <span class="text-danger small fw-bold"><i class="fa-solid fa-flag me-1"></i>{{ __('courses::clients/common.comment_flag_badge') }}</span>
                                    @endif
                                </div>
                                
                                @if ($viewerIsAdmin)
                                    <div class="admin-toolbar">
                                        <button type="button" class="btn btn-sm btn-light rounded-pill px-3 text-muted border-0"
                                            data-visibility-form
                                            data-action="{{ route('courses.comments.toggle', ['locale' => app()->getLocale(), 'slug' => $course->slug_locale, 'commentId' => $comment->id]) }}">
                                            <i class="fa-solid {{ $comment->is_visible ? 'fa-eye-slash' : 'fa-eye' }} me-1"></i>
                                            {{ $comment->is_visible ? __('courses::clients/common.comment_hide') : __('courses::clients/common.comment_show') }}
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </article>

                @if ($comment->replies->isNotEmpty())
                    <div class="replies-list mt-3 ms-5 d-grid gap-3 border-start ps-4">
                        @foreach ($comment->replies as $reply)
                            @php
                                $replyRole = $resolveCommentRole($reply);
                            @endphp
                            <article class="reply-card-modern p-3 rounded-4 border bg-light/50 shadow-none {{ $replyRole === 'admin' ? 'border-primary-subtle bg-primary-subtle/5' : '' }}">
                                <div class="d-flex gap-3">
                                    <div class="comment-body flex-grow-1">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <div class="d-flex align-items-center gap-2">
                                                <h6 class="fw-bold mb-0 text-dark small">{{ $reply->author_name }}</h6>
                                                <span class="badge rounded-pill {{ $replyRole === 'admin' ? 'bg-primary' : 'bg-light text-secondary border' }}" style="font-size: 9px;">
                                                    {{ $resolveRoleLabel($replyRole, $reply) }}
                                                </span>
                                            </div>
                                            <time class="text-muted" style="font-size: 11px;">{{ optional($reply->created_at)->diffForHumans() }}</time>
                                        </div>
                                        <div class="comment-text text-secondary small" style="line-height: 1.5;">{!! $reply->content !!}</div>
                                        
                                        @if ($viewerIsAdmin)
                                            <div class="text-end mt-2">
                                                <button type="button" class="btn btn-link btn-sm text-decoration-none text-muted p-0" style="font-size: 11px;"
                                                    data-visibility-form
                                                    data-action="{{ route('courses.comments.toggle', ['locale' => app()->getLocale(), 'slug' => $course->slug_locale, 'commentId' => $reply->id]) }}">
                                                    {{ $reply->is_visible ? __('courses::clients/common.comment_hide_reply') : __('courses::clients/common.comment_show_reply') }}
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif

                @if ($viewerIsAdmin)
                    <div class="admin-reply-box mt-3 ms-5 ps-4">
                        <form class="course-comment-form" data-comment-form
                            action="{{ route('courses.comments.reply', ['locale' => app()->getLocale(), 'slug' => $course->slug_locale, 'commentId' => $comment->id]) }}"
                            method="POST">
                            @csrf
                            <textarea name="content" rows="2" class="form-control bg-white border shadow-none rounded-3 mb-2" 
                                data-rich-editor maxlength="2000"
                                placeholder="{{ __('courses::clients/common.comment_reply_placeholder') }}"></textarea>
                            <div class="text-end">
                                <button type="submit" class="btn btn-dark btn-sm rounded-pill px-4 shadow-sm fw-bold">{{ __('courses::clients/common.comment_reply_as_admin') }}</button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <div class="empty-reviews-state text-center py-5 rounded-4 border border-dashed bg-light/50">
                <div class="icon-circle bg-white shadow-sm mx-auto mb-3" style="width: 72px; height: 72px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                    <i class="fa-solid fa-comments text-muted opacity-50"></i>
                </div>
                <h5 class="fw-bold text-dark">{{ __('courses::clients/common.comment_empty') }}</h5>
                <p class="text-muted small px-5">{{ __('courses::clients/common.comment_empty_desc') ?? 'Be the first to share your experience with this course!' }}</p>
            </div>
        @endforelse
    </div>
</div>
