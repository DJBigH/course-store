<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Modules\Courses\src\Models\Courses;

class CourseStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Courses $course,
        protected string $changeType // 'published', 'draft', 'locked', 'unlocked'
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $courseName = $this->course->name_locale ?: $this->course->name;

        switch ($this->changeType) {
            case 'published':
                $titleTranslations = [
                    'vi' => 'Khóa học đã được xuất bản',
                    'en' => 'Course published',
                    'ko' => '강좌가 게시되었습니다',
                    'ja' => 'コースが公開されました',
                    'zh' => '课程已发布',
                ];
                $messageTranslations = [
                    'vi' => "Khóa học \"{$courseName}\" của bạn đã được xuất bản và hiển thị công khai.",
                    'en' => "Your course \"{$courseName}\" has been published and is now visible to everyone.",
                    'ko' => "귀하의 강좌 \"{$courseName}\"가 게시되어 이제 모든 사람이 볼 수 있습니다.",
                    'ja' => "コース「{$courseName}」が公開され、誰でも閲覧できるようになりました。",
                    'zh' => "您的课程 \"{$courseName}\" 已发布，现在所有人都可以看到。",
                ];
                $severity = 'success';
                $icon = 'fas fa-eye';
                break;
            case 'draft':
                $titleTranslations = [
                    'vi' => 'Khóa học đã bị ẩn (Bản nháp)',
                    'en' => 'Course moved to draft',
                    'ko' => '강좌가 초안으로 이동되었습니다',
                    'ja' => 'コースが下書きに移動されました',
                    'zh' => '课程已移至草稿',
                ];
                $messageTranslations = [
                    'vi' => "Khóa học \"{$courseName}\" của bạn đã được chuyển về trạng thái bản nháp.",
                    'en' => "Your course \"{$courseName}\" has been moved back to draft status.",
                    'ko' => "귀하의 강좌 \"{$courseName}\"가 초안 상태로 되돌아갔습니다.",
                    'ja' => "コース「{$courseName}」が下書き状態に戻されました。",
                    'zh' => "您的课程 \"{$courseName}\" 已移回草稿状态。",
                ];
                $severity = 'warning';
                $icon = 'fas fa-eye-slash';
                break;
            case 'locked':
                $titleTranslations = [
                    'vi' => 'Quyền học khóa học bị khóa',
                    'en' => 'Course learning access locked',
                    'ko' => '강좌 학습 액세스가 잠겼습니다',
                    'ja' => 'コースの学習アクセスがロックされました',
                    'zh' => '课程学习访问已锁定',
                ];
                $messageTranslations = [
                    'vi' => "Quyền truy cập học tập của khóa học \"{$courseName}\" đã bị khóa bởi quản trị viên.",
                    'en' => "Learning access for your course \"{$courseName}\" has been locked by the administrator.",
                    'ko' => "귀하의 강좌 \"{$courseName}\"의 학습 액세스가 관리자에 의해 잠겼습니다.",
                    'ja' => "コース「{$courseName}」の学習アクセスが管理者によってロックされました。",
                    'zh' => "您的课程 \"{$courseName}\" 的学习访问权限已被管理员锁定。",
                ];
                $severity = 'danger';
                $icon = 'fas fa-lock';
                break;
            case 'unlocked':
                $titleTranslations = [
                    'vi' => 'Quyền học khóa học đã được mở',
                    'en' => 'Course learning access unlocked',
                    'ko' => '강좌 학습 액세스가 해제되었습니다',
                    'ja' => 'コースの学習アクセスが解除されました',
                    'zh' => '课程学习访问已解锁',
                ];
                $messageTranslations = [
                    'vi' => "Quyền truy cập học tập của khóa học \"{$courseName}\" đã được mở lại.",
                    'en' => "Learning access for your course \"{$courseName}\" has been unlocked.",
                    'ko' => "귀하의 강좌 \"{$courseName}\"의 학습 액세스가 해제되었습니다.",
                    'ja' => "コース「{$courseName}」の学習アクセスが解除されました。",
                    'zh' => "您的课程 \"{$courseName}\" 的学习访问权限已解锁。",
                ];
                $severity = 'success';
                $icon = 'fas fa-lock-open';
                break;
            default:
                return [];
        }

        return [
            'type' => 'teacher.course.' . $this->changeType,
            'title' => $titleTranslations['vi'],
            'title_translations' => $titleTranslations,
            'message' => $messageTranslations['vi'],
            'message_translations' => $messageTranslations,
            'url' => route('teacher.courses.edit', $this->course->id),
            'severity' => $severity,
            'icon' => $icon,
            'entity_type' => 'course',
            'entity_id' => $this->course->id,
            'meta' => [
                'change_type' => $this->changeType,
            ],
        ];
    }
}
