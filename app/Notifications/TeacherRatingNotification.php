<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Modules\Students\src\Models\TeacherRating;

class TeacherRatingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected TeacherRating $rating
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $teacher = $this->rating->teacher;
        $teacherName = $teacher->name_locale ?: $teacher->name;
        $studentName = $this->rating->student ? $this->rating->student->name : 'Học viên';
        $stars = $this->rating->rating;

        $titleTranslations = [
            'vi' => 'Đánh giá mới cho hồ sơ của bạn',
            'en' => 'New profile rating',
            'ko' => '새로운 프로필 평가',
            'ja' => '新しいプロフィール評価',
            'zh' => '新个人资料评分',
        ];

        $messageTranslations = [
            'vi' => "Học viên {$studentName} vừa đánh giá {$stars} sao cho hồ sơ Giảng viên của bạn.",
            'en' => "Student {$studentName} just rated your Teacher profile with {$stars} stars.",
            'ko' => "{$studentName} 학생이 강사 프로필에 {$stars} 별점을 남겼습니다.",
            'ja' => "受講生 {$studentName} さんがあなたの講師プロフィールを {$stars} 星で評価しました。",
            'zh' => "学生 {$studentName} 刚刚为您的讲师个人资料评分了 {$stars} 颗星。",
        ];

        return [
            'type' => 'teacher.profile.rating',
            'title' => $titleTranslations['vi'],
            'title_translations' => $titleTranslations,
            'message' => $messageTranslations['vi'],
            'message_translations' => $messageTranslations,
            'url' => route('teacher.dashboard.index'),
            'severity' => $stars >= 4 ? 'success' : ($stars >= 3 ? 'info' : 'warning'),
            'icon' => 'fas fa-star',
            'entity_type' => 'teacher_rating',
            'entity_id' => $this->rating->id,
            'meta' => [
                'teacher_id' => $teacher->id,
                'rating' => $stars,
                'student_name' => $studentName,
            ],
        ];
    }
}
