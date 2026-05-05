<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use Illuminate\Contracts\Queue\ShouldQueue;

class AdminInactiveTeacherAlertNotification extends Notification implements ShouldQueue
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
        $teacherName = $this->teacher->name_locale ?? $this->teacher->name ?? ('Teacher #' . ($this->teacher->id ?? ''));
        $packageName = $this->teacher->currentPackage()?->name_locale ?? 'Chưa xác định';

        return (new MailMessage)
            ->subject('Cảnh báo giảng viên lâu không hoạt động')
            ->greeting('Xin chào ' . ($notifiable->name ?? 'admin') . ',')
            ->line('Hệ thống ghi nhận một giảng viên lâu không hoạt động.')
            ->line('Giảng viên: ' . $teacherName)
            ->line('Số ngày không hoạt động: ' . $this->inactiveDays)
            ->line('Gói hiện tại: ' . $packageName)
            ->action('Xem danh sách giảng viên', route('teacher.index'))
            ->line('Bạn có thể lọc theo cột hoạt động gần nhất trong admin để kiểm tra thêm.');
    }

    public function toArray($notifiable): array
    {
        $teacherName = $this->teacher->name_locale ?? $this->teacher->name ?? ('Teacher #' . ($this->teacher->id ?? ''));

        $titleTranslations = [
            'vi' => 'Cảnh báo giảng viên không hoạt động',
            'en' => 'Inactive teacher alert',
            'ko' => '비활성 강사 알림',
            'ja' => '非アクティブ講師のアラート',
            'zh' => '非活跃讲师警报',
        ];

        $messageTranslations = [
            'vi' => $teacherName . ' đã không có hoạt động trong ' . $this->inactiveDays . ' ngày.',
            'en' => $teacherName . ' has been inactive for ' . $this->inactiveDays . ' days.',
            'ko' => $teacherName . ' 강사가 ' . $this->inactiveDays . '일 동안 활동하지 않았습니다.',
            'ja' => $teacherName . ' 講師が ' . $this->inactiveDays . ' 日間活動していません。',
            'zh' => $teacherName . ' 讲师已有 ' . $this->inactiveDays . ' 天未活动。',
        ];

        return [
            'type' => 'teacher.inactive.admin_alert',
            'title' => $titleTranslations['vi'],
            'title_translations' => $titleTranslations,
            'message' => $messageTranslations['vi'],
            'message_translations' => $messageTranslations,
            'url' => route('teacher.index'),
            'severity' => 'warning',
            'icon' => 'fas fa-user-slash',
            'entity_type' => 'teacher',
            'entity_id' => $this->teacher->id ?? null,
            'meta' => [
                'inactive_days' => $this->inactiveDays,
                'teacher_name' => $teacherName,
                'package_name' => $this->teacher->currentPackage()?->name_locale,
                'last_active_at' => optional($this->teacher->last_active_at)?->toDateTimeString(),
            ],
        ];
    }
}
