<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Modules\Certificates\src\Models\Certificate;

class StudentCertificateIssuedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Certificate $certificate,
        protected string $locale = 'vi'
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $courseName = $this->certificate->course_name_snapshot;
        $teacherName = $this->certificate->teacher_name_snapshot;

        return [
            'type' => 'student.certificate.issued',
            'title' => 'Ban vua nhan duoc chung chi moi',
            'title_translations' => [
                'vi' => 'Bạn vừa nhận được chứng chỉ mới',
                'en' => 'You have received a new certificate',
            ],
            'message' => 'Chung chi hoan thanh khoa hoc "' . $courseName . '" da duoc cap boi ' . $teacherName . '.',
            'message_translations' => [
                'vi' => 'Chứng chỉ hoàn thành khóa học "' . $courseName . '" đã được cấp bởi ' . $teacherName . '.',
                'en' => 'Your completion certificate for "' . $courseName . '" has been issued by ' . $teacherName . '.',
            ],
            'url' => route('students.account.certificates.show', [
                'locale' => $this->locale,
                'id' => $this->certificate->id,
            ]),
            'severity' => 'success',
            'icon' => 'fas fa-award',
            'entity_type' => 'certificate',
            'entity_id' => $this->certificate->id,
            'meta' => [
                'certificate_code' => $this->certificate->code,
                'course_name' => $courseName,
                'teacher_name' => $teacherName,
                'issued_at' => optional($this->certificate->issued_at)->toDateTimeString(),
            ],
        ];
    }
}
