<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class BadgeAssignmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $teacher;
    protected $badges;
    protected $status; // 'success' or 'failure'
    protected $message;

    /**
     * Create a new notification instance.
     */
    public function __construct($teacher, $badges, $status = 'success', $message = '')
    {
        $this->teacher = $teacher;
        $this->badges = $badges;
        $this->status = $status;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];
        
        $emailEnabled = DB::table('settings')->where('key', 'teacher_badge_notification_email_enabled')->value('value');
        if ($emailEnabled === '1' && $notifiable->email) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $siteName = DB::table('settings')->where('key', 'site_name')->value('value') ?: config('app.name');
        
        if ($this->status === 'success') {
            $badgeNames = collect($this->badges)->map(fn($b) => $b->name['vi'] ?? 'Huy hiệu')->implode(', ');
            
            return (new MailMessage)
                ->subject('[' . $siteName . '] Thông báo cấp huy hiệu mới')
                ->greeting('Xin chào ' . $this->teacher->name . '!')
                ->line('Chúc mừng bạn đã được cấp các huy hiệu mới trên hệ thống ' . $siteName . '.')
                ->line('Các huy hiệu vừa được cấp: **' . $badgeNames . '**')
                ->action('Xem trang cá nhân', route('teachers.show', $this->teacher->slug))
                ->line('Cảm ơn bạn đã đồng hành cùng chúng tôi!');
        } else {
            return (new MailMessage)
                ->subject('[' . $siteName . '] Thông báo cập nhật huy hiệu thất bại')
                ->greeting('Xin chào ' . $this->teacher->name . '!')
                ->line('Đã có lỗi xảy ra trong quá trình cập nhật huy hiệu cho tài khoản của bạn.')
                ->line('Lỗi: ' . $this->message)
                ->line('Vui lòng liên hệ quản trị viên để biết thêm chi tiết.');
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        if ($this->status === 'success') {
            $badgeNames = collect($this->badges)->map(fn($b) => $b->name['vi'] ?? 'Huy hiệu')->implode(', ');
            return [
                'title' => 'Cấp huy hiệu thành công',
                'message' => 'Bạn vừa được cấp huy hiệu: ' . $badgeNames,
                'status' => 'success',
                'teacher_id' => $this->teacher->id
            ];
        } else {
            return [
                'title' => 'Cấp huy hiệu thất bại',
                'message' => 'Lỗi: ' . $this->message,
                'status' => 'failure',
                'teacher_id' => $this->teacher->id
            ];
        }
    }
}
