<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeacherInactiveReminderNotification extends Notification
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
        $teacherName = $this->teacher->name_locale ?? $this->teacher->name ?? 'giang vien';
        $packageName = $this->teacher->currentPackage()?->name_locale ?? 'goi hien tai';

        return (new MailMessage)
            ->subject('Nhac nho hoat dong kenh giang vien')
            ->greeting('Xin chao ' . ($notifiable->name ?? $teacherName) . ',')
            ->line('Kenh giang vien cua ban da khong co hoat dong trong ' . $this->inactiveDays . ' ngay.')
            ->line('Goi hien tai: ' . $packageName . '. Neu ban van muon tiep tuc su dung, hay dang nhap de cap nhat noi dung, tra loi binh luan hoac kiem tra hoc vien.')
            ->action('Mo Teacher Studio', route('teacher.dashboard.index'))
            ->line('Thong bao nay chi de nhac ban khong bo lo kenh cua minh.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'teacher.inactive.reminder',
            'title' => 'Nhac nho hoat dong kenh giang vien',
            'title_translations' => [
                'vi' => 'Nhắc nhở hoạt động kênh giảng viên',
                'en' => 'Teacher activity reminder',
            ],
            'message' => 'Kenh giang vien cua ban da khong co hoat dong trong ' . $this->inactiveDays . ' ngay. Dang nhap lai de tiep tuc quan ly khoa hoc va hoc vien.',
            'message_translations' => [
                'vi' => 'Kênh giảng viên của bạn đã không có hoạt động trong ' . $this->inactiveDays . ' ngày. Đăng nhập lại để tiếp tục quản lý khóa học và học viên.',
                'en' => 'Your teacher portal has been inactive for ' . $this->inactiveDays . ' days. Sign in again to continue managing your courses and students.',
            ],
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
