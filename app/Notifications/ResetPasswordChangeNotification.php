<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordChangeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Thông báo: Mật khẩu tài khoản của bạn đã được thay đổi')
            ->greeting('Xin chào!')
            ->line('Chúng tôi xin thông báo rằng mật khẩu của tài khoản bạn vừa được thay đổi thành công.')
            ->line('Nếu chính bạn là người thực hiện thay đổi này, bạn không cần thực hiện thêm hành động nào.')
            ->action('Đăng nhập vào hệ thống', url('/dang-nhap'))
            ->line('Trong trường hợp bạn không thực hiện thay đổi mật khẩu, vui lòng liên hệ ngay với bộ phận hỗ trợ của chúng tôi để được hỗ trợ kịp thời.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
