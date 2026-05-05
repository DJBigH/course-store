<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Courses\src\Models\Courses;

class AdminCourseGiftNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Courses $course,
        protected string $locale = 'vi',
        protected bool $sendEmail = true
    ) {}

    public function via($notifiable): array
    {
        $channels = ['database'];
        if ($this->sendEmail) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🎁 Bạn nhận được quà tặng khóa học mới!')
            ->greeting('Chào ' . $notifiable->name . '!')
            ->line('Ban quản trị hệ thống BigK Udemy vừa tặng bạn khóa học mới: **' . $this->course->name . '**.')
            ->line('Bạn có thể truy cập vào mục "Khóa học của tôi" để bắt đầu học ngay lập tức.')
            ->action('Đi đến khóa học', route('students.account.my-courses', ['locale' => $this->locale]))
            ->line('Chúc bạn có những trải nghiệm học tập tuyệt vời!');
    }

    public function toDatabase($notifiable): array
    {
        $courseName = $this->course->name_locale ?: $this->course->name;
        
        $titleTranslations = [
            'vi' => '🎁 Bạn được tặng khóa học mới',
            'en' => '🎁 You received a new course gift',
            'ko' => '🎁 새 강좌 선물 도착',
            'ja' => '🎁 新しいコースのプレゼント',
            'zh' => '🎁 收到新课程赠送',
        ];

        $messageTranslations = [
            'vi' => 'Bạn vừa được Admin tặng khóa học: ' . $courseName,
            'en' => 'Admin has gifted you the course: ' . $courseName,
            'ko' => '관리자가 **' . $courseName . '** 강좌를 선물했습니다. 지금 바로 학습을 시작하세요!',
            'ja' => '管理者から **' . $courseName . '** コースが届きました。今すぐ学習を始めましょう！',
            'zh' => '管理员向您赠送了 **' . $courseName . '** 课程。现在就开始学习吧！',
        ];

        return [
            'type' => 'admin.course_gift',
            'title' => $titleTranslations['vi'],
            'title_translations' => $titleTranslations,
            'message' => $messageTranslations['vi'],
            'message_translations' => $messageTranslations,
            'url' => route('students.account.my-courses', ['locale' => $this->locale]),
            'icon' => 'fas fa-gift',
            'severity' => 'success',
            'entity_type' => 'course',
            'entity_id' => $this->course->id,
            'meta' => [
                'course_id' => $this->course->id,
                'course_name' => $courseName,
            ],
        ];
    }
}
