<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use Illuminate\Contracts\Queue\ShouldQueue;

class TeacherInactiveReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected object $teacher,
        protected int $inactiveDays
    ) {
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $teacherName = $this->teacher->name_locale ?? $this->teacher->name ?? 'giảng viên';
        $packageName = $this->teacher->currentPackage()?->name_locale ?? 'gói hiện tại';

        return (new MailMessage)
            ->subject('Nhắc nhở hoạt động kênh giảng viên')
            ->greeting('Xin chào ' . ($notifiable->name ?? $teacherName) . ',')
            ->line('Kênh giảng viên của bạn đã không có hoạt động trong ' . $this->inactiveDays . ' ngày.')
            ->line('Gói hiện tại: ' . $packageName . '. Nếu bạn vẫn muốn tiếp tục sử dụng, hãy đăng nhập để cập nhật nội dung, trả lời bình luận hoặc kiểm tra học viên.')
            ->action('Mở Teacher Studio', route('teacher.dashboard.index'))
            ->line('Thông báo này chỉ để nhắc bạn không bỏ lỡ kênh của mình.');
    }

    public function toArray($notifiable): array
    {
        $titleTranslations = [
            'vi' => 'Nhắc nhở hoạt động kênh giảng viên',
            'en' => 'Teacher activity reminder',
            'ko' => '강사 활동 리마인더',
            'ja' => '講師活動のリマインダー',
            'zh' => '讲师活动提醒',
        ];

        $messageTranslations = [
            'vi' => 'Kênh giảng viên của bạn đã không có hoạt động trong ' . $this->inactiveDays . ' ngày. Đăng nhập lại để tiếp tục quản lý khóa học và học viên.',
            'en' => 'Your teacher portal has been inactive for ' . $this->inactiveDays . ' days. Sign in again to continue managing your courses and students.',
            'ko' => '강사 대시보드가 ' . $this->inactiveDays . '일 동안 비활성 상태였습니다. 다시 로그인하여 강의와 학생들을 관리하세요.',
            'ja' => '講師ダッシュボードが ' . $this->inactiveDays . ' 日間活動していません。再ログインしてコースと受講生を管理しましょう。',
            'zh' => '您的讲师后台已有 ' . $this->inactiveDays . ' 天未活动。请重新登录以继续管理您的课程 và 学生。',
        ];

        return [
            'type' => 'teacher.inactive.reminder',
            'title' => $titleTranslations['vi'],
            'title_translations' => $titleTranslations,
            'message' => $messageTranslations['vi'],
            'message_translations' => $messageTranslations,
            'url' => route('teacher.dashboard.index'),
            'severity' => 'warning',
            'icon' => 'fas fa-user-clock',
            'entity_type' => 'teacher',
            'entity_id' => $this->teacher->id ?? null,
            'meta' => [
                'inactive_days' => $this->inactiveDays,
                'package_name' => $this->teacher->currentPackage()?->name_locale,
                'last_active_at' => optional($this->teacher->last_active_at)?->toDateTimeString(),
            ],
        ];
    }
}
