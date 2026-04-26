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
        return [
            'type' => 'admin.course_gift',
            'title' => '🎁 Bạn được tặng khóa học mới',
            'message' => 'Bạn vừa được Admin tặng khóa học: ' . $this->course->name,
            'url' => route('students.account.my-courses', ['locale' => $this->locale]),
            'icon' => 'fas fa-gift',
            'severity' => 'success',
            'meta' => [
                'course_id' => $this->course->id,
            ],
        ];
    }
}
