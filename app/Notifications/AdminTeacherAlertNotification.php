<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Modules\Teacher\src\Models\TeacherApplication;

class AdminTeacherAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected object $subject,
        protected string $alertType = 'new_application' // 'new_application' or 'course_created'
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        if ($this->alertType === 'new_application') {
            $name = $this->subject->full_name;
            $titleTranslations = [
                'vi' => 'Hồ sơ đăng ký giảng viên mới',
                'en' => 'New teacher application',
                'ko' => '새로운 강사 신청',
                'ja' => '新しい講師の応募',
                'zh' => '新讲师申请',
            ];
            $messageTranslations = [
                'vi' => "Hệ thống nhận được hồ sơ đăng ký giảng viên mới từ {$name}.",
                'en' => "The system received a new teacher application from {$name}.",
                'ko' => "{$name}님으로부터 새로운 강사 신청서가 접수되었습니다.",
                'ja' => "{$name} さんから新しい講師の応募がありました。",
                'zh' => "系统收到来自 {$name} 的新讲师申请。",
            ];
            $url = route('teacher-applications.index'); // Fixed route name
            $type = 'admin.teacher.application';
        } else {
            $courseName = $this->subject->name_locale ?: $this->subject->name;
            $teacherName = $this->subject->teacher->name ?? 'Giảng viên';
            $titleTranslations = [
                'vi' => 'Khóa học mới được tạo',
                'en' => 'New course created',
                'ko' => '새로운 강좌 생성',
                'ja' => '新しいコースが作成されました',
                'zh' => '新课程已创建',
            ];
            $messageTranslations = [
                'vi' => "Giảng viên {$teacherName} vừa tạo khóa học mới: \"{$courseName}\".",
                'en' => "Teacher {$teacherName} has created a new course: \"{$courseName}\".",
                'ko' => "{$teacherName} 강사가 새로운 강좌 \"{$courseName}\"를 생성했습니다.",
                'ja' => "講師 {$teacherName} さんが新しいコース「{$courseName}」を作成しました。",
                'zh' => "讲师 {$teacherName} 创建了新课程：\"{$courseName}\"。",
            ];
            $url = route('courses.index');
            $type = 'admin.course.created';
        }

        return [
            'type' => $type,
            'title' => $titleTranslations['vi'],
            'title_translations' => $titleTranslations,
            'message' => $messageTranslations['vi'],
            'message_translations' => $messageTranslations,
            'url' => $url,
            'severity' => 'info',
            'icon' => $this->alertType === 'new_application' ? 'fas fa-user-tie' : 'fas fa-book',
            'entity_type' => $this->alertType === 'new_application' ? 'teacher_application' : 'course',
            'entity_id' => $this->subject->id,
            'meta' => [
                'subject_id' => $this->subject->id,
            ],
        ];
    }
}
