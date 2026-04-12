<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminInactiveTeacherAlertNotification extends Notification
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
        $packageName = $this->teacher->currentPackage()?->name_locale ?? 'Chua xac dinh';

        return (new MailMessage)
            ->subject('Canh bao giang vien lau khong hoat dong')
            ->greeting('Xin chao ' . ($notifiable->name ?? 'admin') . ',')
            ->line('He thong ghi nhan mot giang vien lau khong hoat dong.')
            ->line('Giang vien: ' . $teacherName)
            ->line('So ngay khong hoat dong: ' . $this->inactiveDays)
            ->line('Goi hien tai: ' . $packageName)
            ->action('Xem danh sach giang vien', route('teacher.index'))
            ->line('Ban co the loc theo cot hoat dong gan nhat trong admin de kiem tra them.');
    }

    public function toArray($notifiable): array
    {
        $teacherName = $this->teacher->name_locale ?? $this->teacher->name ?? ('Teacher #' . ($this->teacher->id ?? ''));

        return [
            'type' => 'teacher.inactive.admin_alert',
            'title' => 'Canh bao giang vien khong hoat dong',
            'title_translations' => [
                'vi' => 'Cảnh báo giảng viên không hoạt động',
                'en' => 'Inactive teacher alert',
            ],
            'message' => $teacherName . ' da khong co hoat dong trong ' . $this->inactiveDays . ' ngay.',
            'message_translations' => [
                'vi' => $teacherName . ' đã không có hoạt động trong ' . $this->inactiveDays . ' ngày.',
                'en' => $teacherName . ' has been inactive for ' . $this->inactiveDays . ' days.',
            ],
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
