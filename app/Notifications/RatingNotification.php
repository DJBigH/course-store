<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Modules\Courses\src\Models\CourseRating;

class RatingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected CourseRating $rating
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $course = $this->rating->course;
        $courseName = $course->name_locale ?: $course->name;
        $studentName = $this->rating->student ? $this->rating->student->name : 'Học viên';
        $stars = $this->rating->rating;

        $titleTranslations = [
            'vi' => 'Đánh giá mới cho khóa học',
            'en' => 'New course rating',
            'ko' => '새로운 강좌 평가',
            'ja' => '新しいコース評価',
            'zh' => '新课程评分',
        ];

        $messageTranslations = [
            'vi' => "Học viên {$studentName} vừa đánh giá {$stars} sao cho khóa học \"{$courseName}\".",
            'en' => "Student {$studentName} just rated your course \"{$courseName}\" with {$stars} stars.",
            'ko' => "{$studentName} 학생이 \"{$courseName}\" 강좌에 {$stars} 별점을 남겼습니다.",
            'ja' => "受講生 {$studentName} さんがコース「{$courseName}」を {$stars} 星で評価しました。",
            'zh' => "学生 {$studentName} 刚刚为您的课程 \"{$courseName}\" 评分了 {$stars} 颗星。",
        ];

        return [
            'type' => 'teacher.course.rating',
            'title' => $titleTranslations['vi'],
            'title_translations' => $titleTranslations,
            'message' => $messageTranslations['vi'],
            'message_translations' => $messageTranslations,
            'url' => route('teacher.dashboard.index'), // Or specific course stats page
            'severity' => $stars >= 4 ? 'success' : ($stars >= 3 ? 'info' : 'warning'),
            'icon' => 'fas fa-star',
            'entity_type' => 'rating',
            'entity_id' => $this->rating->id,
            'meta' => [
                'course_id' => $course->id,
                'rating' => $stars,
                'student_name' => $studentName,
            ],
        ];
    }
}
