<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Modules\Courses\src\Models\CourseComment;
use Illuminate\Support\Str;

class CommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected CourseComment $comment,
        protected string $action = 'new_comment' // 'new_comment' or 'reply'
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $course = $this->comment->course;
        $courseName = $course->name_locale ?: $course->name;
        $authorName = $this->comment->student ? $this->comment->student->name : ($this->comment->user ? $this->comment->user->name : 'Người dùng');
        $contentPreview = Str::limit($this->comment->content, 50);

        if ($this->action === 'reply') {
            $titleTranslations = [
                'vi' => 'Phản hồi mới từ giảng viên',
                'en' => 'New reply from teacher',
                'ko' => '강사의 새로운 답글',
                'ja' => '講師からの新しい返信',
                'zh' => '讲师的新回复',
            ];
            $messageTranslations = [
                'vi' => "Giảng viên vừa trả lời bình luận của bạn trong khóa học \"{$courseName}\": \"{$contentPreview}\"",
                'en' => "Teacher replied to your comment in course \"{$courseName}\": \"{$contentPreview}\"",
                'ko' => "강사가 \"{$courseName}\" 강좌의 댓글에 답글을 남겼습니다: \"{$contentPreview}\"",
                'ja' => "講師がコース「{$courseName}」のコメントに返信しました: 「{$contentPreview}」",
                'zh' => "讲师回复了您在课程 \"{$courseName}\" 中的评论: \"{$contentPreview}\"",
            ];
            $type = 'student.comment.reply';
            $url = route('lessons.home', ['locale' => app()->getLocale(), 'slug' => $course->slug]); // Should ideally go to specific lesson
        } else {
            $titleTranslations = [
                'vi' => 'Bình luận mới từ học viên',
                'en' => 'New comment from student',
                'ko' => '학생의 새로운 댓글',
                'ja' => '受講生からの新しいコメント',
                'zh' => '学生的新评论',
            ];
            $messageTranslations = [
                'vi' => "Học viên {$authorName} vừa gửi bình luận trong khóa học \"{$courseName}\": \"{$contentPreview}\"",
                'en' => "Student {$authorName} posted a comment in course \"{$courseName}\": \"{$contentPreview}\"",
                'ko' => "학생 {$authorName}님이 \"{$courseName}\" 강좌에 댓글을 남겼습니다: \"{$contentPreview}\"",
                'ja' => "受講生 {$authorName} さんがコース「{$courseName}」にコメントを投稿しました: 「{$contentPreview}」",
                'zh' => "学生 {$authorName} 在课程 \"{$courseName}\" 中发表了评论: \"{$contentPreview}\"",
            ];
            $type = 'teacher.comment.new';
            $url = route('teacher.dashboard.comments.index', ['course_id' => $course->id]);
        }

        return [
            'type' => $type,
            'title' => $titleTranslations['vi'],
            'title_translations' => $titleTranslations,
            'message' => $messageTranslations['vi'],
            'message_translations' => $messageTranslations,
            'url' => $url,
            'severity' => 'info',
            'icon' => 'fas fa-comments',
            'entity_type' => 'comment',
            'entity_id' => $this->comment->id,
            'meta' => [
                'course_id' => $course->id,
                'comment_id' => $this->comment->id,
                'author_name' => $authorName,
            ],
        ];
    }
}
