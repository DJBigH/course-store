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

        $titleTranslations = [
            'vi' => 'Bạn vừa nhận được chứng chỉ mới',
            'en' => 'You have received a new certificate',
            'ko' => '새 수료증 발급 완료',
            'ja' => '新しい修了証の発行',
            'zh' => '新结业证书已颁发',
        ];

        $messageTranslations = [
            'vi' => 'Chứng chỉ hoàn thành khóa học "' . $courseName . '" đã được cấp bởi ' . $teacherName . '.',
            'en' => 'Your completion certificate for "' . $courseName . '" has been issued by ' . $teacherName . '.',
            'ko' => '"' . $courseName . '" 강좌의 수료증이 ' . $teacherName . ' 강사님에 의해 발급되었습니다.',
            'ja' => $teacherName . ' 講師より「' . $courseName . '」の修了証が発行されました。',
            'zh' => $teacherName . ' 讲师已为您颁发了“' . $courseName . '”课程的结业证书。',
        ];

        return [
            'type' => 'student.certificate.issued',
            'title' => $titleTranslations['vi'],
            'title_translations' => $titleTranslations,
            'message' => $messageTranslations['vi'],
            'message_translations' => $messageTranslations,
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
