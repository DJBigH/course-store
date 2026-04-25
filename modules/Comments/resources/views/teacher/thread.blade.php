@php
    $teacherStudentId = $teacher?->student_id;
    $teacherCanToggleComments = $teacher?->packageHasFeature('can_manage_comments') ?? false;
    
    $resolveRole = function ($comment) use ($teacherStudentId) {
        if ($comment->user_id) return 'admin';
        if ($teacherStudentId && (int) $comment->student_id === (int) $teacherStudentId) return 'teacher';
        return 'student';
    };

    $resolveRoleBadge = function (string $role) {
        return match ($role) {
            'admin' => '<span class="teacher-chip teacher-chip--primary">Admin</span>',
            'teacher' => '<span class="teacher-chip teacher-chip--success">' . __('comments::teacher/messages.roles.teacher') . '</span>',
            default => '<span class="teacher-chip teacher-chip--outline">' . __('comments::teacher/messages.roles.student') . '</span>',
        };
    };
@endphp

<div class="teacher-comments-thread" data-teacher-comments>
    <div class="teacher-comments-thread-container">
        @forelse ($threads as $comment)
            @php $commentRole = $resolveRole($comment); @endphp
            
            <div class="teacher-comment-group {{ !$comment->is_visible ? 'is-collapsed' : '' }}">
                <div class="teacher-comment-card {{ $commentRole === 'teacher' ? 'is-mine' : '' }}">
                    <div class="teacher-comment-card__side">
                        <img src="{{ courseCommentAvatar($comment->id . '-' . $commentRole) }}" alt="" class="comment-avatar">
                        <div class="comment-line"></div>
                    </div>
                    
                    <div class="teacher-comment-card__body">
                        <div class="teacher-comment-card__header">
                            <div class="teacher-comment-card__info">
                                <span class="teacher-comment-card__author">{{ $comment->author_name }}</span>
                                {!! $resolveRoleBadge($commentRole) !!}
                                <span class="teacher-comment-card__date">{{ optional($comment->created_at)->diffForHumans() }}</span>
                                
                                @if ($comment->is_flagged)
                                    <span class="teacher-chip teacher-chip--danger ms-2">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        {{ __('comments::teacher/messages.comment.flag_badge') }}
                                    </span>
                                @endif
                                
                                @if (!$comment->is_visible)
                                    <span class="teacher-chip teacher-chip--muted ms-2">{{ __('comments::teacher/messages.comment.hidden_badge') }}</span>
                                @endif
                            </div>

                            @if ($teacherCanToggleComments)
                                <div class="teacher-comment-card__actions">
                                    <button type="button" 
                                        class="btn btn-link p-0 text-decoration-none" 
                                        data-teacher-comment-toggle 
                                        data-action="{{ route('teacher.dashboard.comments.toggle', ['comment' => $comment->id]) }}">
                                        {!! $comment->is_visible ? '<i class="fas fa-eye-slash text-muted"></i>' : '<i class="fas fa-eye text-primary"></i>' !!}
                                    </button>
                                </div>
                            @endif
                        </div>

                        <div class="teacher-comment-card__content">
                            {{ $comment->content }}
                        </div>
                        
                        @if ($comment->is_flagged && $comment->flagged_terms)
                             <div class="teacher-comment-card__flag-note">
                                 <strong>{{ __('comments::teacher/messages.comment.flag_terms') }}:</strong> {{ $comment->flagged_terms }}
                             </div>
                        @endif

                        {{-- Replies --}}
                        <div class="teacher-comment-replies">
                            @foreach ($comment->replies as $reply)
                                @php $replyRole = $resolveRole($reply); @endphp
                                <div class="teacher-reply-item {{ !$reply->is_visible ? 'is-muted' : '' }}">
                                    <div class="teacher-reply-item__header">
                                        <strong>{{ $reply->author_name }}</strong>
                                        {!! $resolveRoleBadge($replyRole) !!}
                                        <span class="text-muted small ms-2">{{ optional($reply->created_at)->diffForHumans() }}</span>
                                        
                                        @if ($teacherCanToggleComments)
                                            <button type="button" 
                                                class="btn btn-link p-0 text-decoration-none ms-auto"
                                                data-teacher-comment-toggle 
                                                data-action="{{ route('teacher.dashboard.comments.toggle', ['comment' => $reply->id]) }}">
                                                {!! $reply->is_visible ? '<i class="fas fa-eye-slash text-muted small"></i>' : '<i class="fas fa-eye text-primary small"></i>' !!}
                                            </button>
                                        @endif
                                    </div>
                                    <div class="teacher-reply-item__content">
                                        {{ $reply->content }}
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Reply Form --}}
                        <form class="teacher-comment-reply-box mt-3" data-teacher-comment-form action="{{ route('teacher.dashboard.comments.reply', ['comment' => $comment->id]) }}" method="POST">
                            @csrf
                            <div class="input-group">
                                <input name="content" type="text" class="form-control" placeholder="{{ __('comments::teacher/messages.comment.reply_placeholder') }}">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="teacher-empty-state py-4">
                <p class="text-muted">{{ __('comments::teacher/messages.comment.empty') }}</p>
            </div>
        @endforelse
    </div>
</div>

<style>
    .teacher-comment-group {
        margin-bottom: 2rem;
        position: relative;
    }
    
    .teacher-comment-card {
        display: flex;
        gap: 1.25rem;
    }
    
    .teacher-comment-card__side {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 48px;
    }
    
    .comment-avatar {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        object-fit: cover;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .comment-line {
        flex: 1;
        width: 2px;
        background: rgba(255,255,255,0.06);
        margin: 0.5rem 0;
    }
    
    .teacher-comment-card__body {
        flex: 1;
        background: rgba(255,255,255,0.02);
        border: 1px solid rgba(255,255,255,0.05);
        border-radius: 20px;
        padding: 1.25rem;
        transition: all 0.3s ease;
    }
    
    .teacher-comment-card:hover .teacher-comment-card__body {
        background: rgba(255,255,255,0.035);
        border-color: rgba(255,255,255,0.1);
    }
    
    .teacher-comment-card.is-mine .teacher-comment-card__body {
        background: rgba(59, 130, 246, 0.04);
        border-color: rgba(59, 130, 246, 0.1);
    }
    
    .teacher-comment-card__header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }
    
    .teacher-comment-card__author {
        font-weight: 700;
        color: #fff;
        margin-right: 0.75rem;
    }
    
    .teacher-comment-card__date {
        font-size: 0.8rem;
        color: rgba(255,255,255,0.4);
        margin-left: 0.75rem;
    }
    
    .teacher-comment-card__content {
        color: rgba(255,255,255,0.8);
        line-height: 1.6;
        font-size: 0.95rem;
    }
    
    .teacher-comment-card__flag-note {
        margin-top: 0.75rem;
        padding: 0.5rem 0.75rem;
        background: rgba(239, 68, 68, 0.1);
        border-radius: 8px;
        color: #fca5a5;
        font-size: 0.85rem;
    }
    
    .teacher-comment-replies {
        margin-top: 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .teacher-reply-item {
        padding-left: 1.25rem;
        border-left: 2px solid rgba(255,255,255,0.05);
    }
    
    .teacher-reply-item__header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.25rem;
    }
    
    .teacher-reply-item__header strong {
        color: #fff;
        font-size: 0.9rem;
    }
    
    .teacher-reply-item__content {
        color: rgba(255,255,255,0.6);
        font-size: 0.9rem;
    }
    
    .teacher-reply-item.is-muted {
        opacity: 0.5;
    }
    
    .teacher-comment-reply-box .form-control {
        background: rgba(0,0,0,0.2);
        border: 1px solid rgba(255,255,255,0.1);
        color: #fff;
        border-radius: 12px 0 0 12px !important;
    }
    
    .teacher-comment-reply-box .btn-primary {
        border-radius: 0 12px 12px 0 !important;
    }
    
    /* Light Theme Adjustments */
    html[data-theme="light"] .teacher-comment-card__author,
    html[data-theme="light"] .teacher-reply-item__header strong {
        color: #1e293b;
    }
    
    html[data-theme="light"] .teacher-comment-card__body {
        background: #fff;
        border-color: #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    
    html[data-theme="light"] .teacher-comment-card__content {
        color: #475569;
    }
    
    html[data-theme="light"] .teacher-comment-reply-box .form-control {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #1e293b;
    }

    html[data-theme="light"] .comment-line {
        background: #e2e8f0;
    }
</style>
