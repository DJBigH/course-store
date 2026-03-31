<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class AdminResetPasswordQueued extends ResetPassword implements ShouldQueue
{
    use Queueable;

    public function __construct($token)
    {
        parent::__construct($token);

        $this->onQueue('default');
    }

    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject('Đặt lại mật khẩu quản trị')
            ->greeting('Xin chào!')
            ->line('Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản quản trị của bạn.')
            ->action('Đặt lại mật khẩu', $url)
            ->line('Liên kết này sẽ hết hạn sau ' . config('auth.passwords.users.expire') . ' phút.')
            ->line('Nếu bạn không thực hiện yêu cầu này, bạn có thể bỏ qua email này.');
    }

    protected function resetUrl($notifiable)
    {
        return URL::route('admin.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
    }
}
