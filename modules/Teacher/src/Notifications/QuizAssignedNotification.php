<?php

namespace Modules\Teacher\src\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Modules\Courses\src\Models\Courses;
use Modules\Courses\src\Models\CourseQuiz;

class QuizAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly CourseQuiz $quiz,
        public readonly Courses    $course,
        public readonly string      $quizUrl,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Dữ liệu lưu vào bảng notifications (database channel).
     */
    public function toDatabase(object $notifiable): array
    {
        $quizTitle = $this->quiz->title;
        $courseName = $this->course->name_locale ?: $this->course->name;
        
        $titleTranslations = [
            'vi' => '📝 Bạn có bài kiểm tra mới!',
            'en' => '📝 You have a new quiz!',
            'ko' => '📝 mới bài kiểm tra!',
            'ja' => '📝 新しいクイズがあります！',
            'zh' => '📝 您 có một bài kiểm tra mới!',
        ];

        $messageTranslations = [
            'vi' => "Giáo viên vừa giao bài kiểm tra **{$quizTitle}** trong khóa học **{$courseName}**. Nhấn vào đây để làm bài ngay.",
            'en' => "Teacher has assigned a new quiz **{$quizTitle}** in course **{$courseName}**. Click here to start now.",
            'ko' => "선생님이 **{$courseName}** 강좌에 **{$quizTitle}** 퀴즈를 할당했습니다. 지금 바로 시작해보세요.",
            'ja' => "講師が **{$courseName}** コースに **{$quizTitle}** クイズを割り当てました。今すぐ開始してください。",
            'zh' => "老师在 **{$courseName}** 课程中分配了新测试 **{$quizTitle}**。点击立即开始。",
        ];

        return [
            'type' => 'student.quiz_assigned',
            'title' => $titleTranslations['vi'],
            'title_translations' => $titleTranslations,
            'message' => $messageTranslations['vi'],
            'message_translations' => $messageTranslations,
            'url' => $this->quizUrl,
            'icon' => 'fas fa-tasks',
            'severity' => 'info',
            'entity_type' => 'course_quiz',
            'entity_id' => $this->quiz->id,
            'meta' => [
                'quiz_id' => $this->quiz->id,
                'quiz_title' => $quizTitle,
                'course_id' => $this->course->id,
                'course_name' => $courseName,
                'action_label' => 'Làm bài ngay',
            ],
        ];
    }
}
