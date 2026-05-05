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
        $locale = app()->getLocale();
        $isSuccess = $this->status === 'success';
        
        $badgeNamesVi = collect($this->badges)->map(fn($b) => $b->name['vi'] ?? 'Huy hiệu')->implode(', ');
        $badgeNamesEn = collect($this->badges)->map(fn($b) => $b->name['en'] ?? 'Badge')->implode(', ');
        $badgeNamesKo = collect($this->badges)->map(fn($b) => $b->name['ko'] ?? '뱃지')->implode(', ');
        $badgeNamesJa = collect($this->badges)->map(fn($b) => $b->name['ja'] ?? 'バッジ')->implode(', ');
        $badgeNamesZh = collect($this->badges)->map(fn($b) => $b->name['zh'] ?? '勋章')->implode(', ');

        $titleTranslations = [
            'vi' => $isSuccess ? 'Cấp huy hiệu thành công' : 'Cấp huy hiệu thất bại',
            'en' => $isSuccess ? 'Badge assigned successfully' : 'Badge assignment failed',
            'ko' => $isSuccess ? '뱃지 지급 완료' : '뱃지 지급 실패',
            'ja' => $isSuccess ? 'バッジの付与完了' : 'バッジの付与失敗',
            'zh' => $isSuccess ? '勋章授予成功' : '勋章授予失败',
        ];

        $messageTranslations = [
            'vi' => $isSuccess ? 'Bạn vừa được cấp huy hiệu: ' . $badgeNamesVi : 'Lỗi: ' . $this->message,
            'en' => $isSuccess ? 'You have been awarded a badge: ' . $badgeNamesEn : 'Error: ' . $this->message,
            'ko' => $isSuccess ? '새로운 뱃지가 지급되었습니다: ' . $badgeNamesKo : '오류: ' . $this->message,
            'ja' => $isSuccess ? '新しいバッジが付与されました: ' . $badgeNamesJa : 'エラー: ' . $this->message,
            'zh' => $isSuccess ? '您已获得新勋章：' . $badgeNamesZh : '错误：' . $this->message,
        ];

        return [
            'type' => 'teacher.badge',
            'title' => $titleTranslations['vi'],
            'title_translations' => $titleTranslations,
            'message' => $messageTranslations['vi'],
            'message_translations' => $messageTranslations,
            'url' => route('teachers.show', $this->teacher->slug),
            'severity' => $isSuccess ? 'success' : 'danger',
            'icon' => $isSuccess ? 'fas fa-medal' : 'fas fa-exclamation-circle',
            'entity_type' => 'teacher',
            'entity_id' => $this->teacher->id,
            'meta' => [
                'status' => $this->status,
                'badge_count' => count($this->badges),
                'error_message' => $isSuccess ? null : $this->message,
            ],
        ];
    }
}
