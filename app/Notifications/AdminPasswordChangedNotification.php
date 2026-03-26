<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminPasswordChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        $this->onQueue('default');
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Mật khẩu quản trị đã được thay đổi')
            ->greeting('Xin chào!')
            ->line('Mật khẩu cho tài khoản quản trị của bạn vừa được thay đổi thành công.')
            ->line('Tất cả phiên đăng nhập trước đó đã được đăng xuất để đảm bảo an toàn.')
            ->action('Đăng nhập quản trị', route('login'))
            ->line('Nếu bạn không thực hiện thay đổi này, vui lòng kiểm tra lại tài khoản và liên hệ hỗ trợ ngay.');
    }
}
