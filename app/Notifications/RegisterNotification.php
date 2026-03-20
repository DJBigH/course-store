<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RegisterNotification extends Notification
{
    use Queueable;

    protected $student;

    public function __construct($student)
    {
        $this->student = $student;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $messageTranslations = [
            'vi' => 'Có khách hàng mới đăng ký',
            'en' => 'A new customer has registered',
            'ko' => '새 고객이 가입했습니다',
            'ja' => '新しい会員登録があります',
            'zh' => '有新客户注册',
        ];

        return [
            'type' => 'student.new',
            'message' => $messageTranslations['vi'],
            'message_translations' => $messageTranslations,
            'url' => route('students.edit', $this->student->id),
        ];
    }
}
