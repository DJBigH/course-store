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
        $titleTranslations = [
            'vi' => 'Học viên mới đăng ký',
            'en' => 'New student registration',
            'ko' => '새로운 수강생 등록',
            'ja' => '新しい受講者登録',
            'zh' => '新学员注册',
        ];

        $messageTranslations = [
            'vi' => 'Có học viên mới đăng ký',
            'en' => 'A new student has registered',
            'ko' => '새로운 수강생이 등록했습니다',
            'ja' => '新しい受講者が登録しました',
            'zh' => '有新学员注册',
        ];
        return [
            'type' => 'student.new',
            'title' => $titleTranslations['vi'],
            'title_translations' => $titleTranslations,
            'message' => $messageTranslations['vi'],
            'message_translations' => $messageTranslations,
            'url' => route('students.edit', $this->student->id),
            'severity' => 'success',
            'icon' => 'fas fa-user-plus',
            'entity_type' => 'student',
            'entity_id' => $this->student->id,
            'meta' => [
                'student_name' => $this->student->name ?? null,
                'student_email' => $this->student->email ?? null,
            ],
        ];
    }
}
