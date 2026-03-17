<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordChangeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $mailLocale;

    /**
     * Create a new notification instance.
     */
    public function __construct(?string $locale = null)
    {
        $this->mailLocale = $locale ?: app()->getLocale() ?: config('app.locale', 'vi');
        $this->locale($this->mailLocale);
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
        $texts = [
            'subject' => [
                'vi' => 'Thông báo: Mật khẩu tài khoản của bạn đã được thay đổi',
                'en' => 'Notice: Your account password has been changed',
                'ko' => '알림: 계정 비밀번호가 변경되었습니다',
                'ja' => 'お知らせ: アカウントのパスワードが変更されました',
                'zh' => '通知：你的账户密码已被更改',
            ],
            'greeting' => [
                'vi' => 'Xin chào!',
                'en' => 'Hello!',
                'ko' => '안녕하세요!',
                'ja' => 'こんにちは！',
                'zh' => '你好！',
            ],
            'line_1' => [
                'vi' => 'Chúng tôi xin thông báo rằng mật khẩu của tài khoản bạn vừa được thay đổi thành công.',
                'en' => 'We would like to inform you that your account password has just been changed successfully.',
                'ko' => '회원님의 계정 비밀번호가 방금 성공적으로 변경되었음을 알려드립니다.',
                'ja' => 'アカウントのパスワードが正常に変更されたことをお知らせします。',
                'zh' => '我们通知你，你的账户密码刚刚已成功更改。',
            ],
            'line_2' => [
                'vi' => 'Nếu chính bạn là người thực hiện thay đổi này, bạn không cần thực hiện thêm hành động nào.',
                'en' => 'If you made this change, no further action is required.',
                'ko' => '이 변경을 직접 하셨다면 추가로 조치하실 필요가 없습니다.',
                'ja' => 'この変更がご自身によるものであれば、追加の操作は不要です。',
                'zh' => '如果这是你本人操作的，则无需执行其他操作。',
            ],
            'action' => [
                'vi' => 'Đăng nhập vào hệ thống',
                'en' => 'Sign in to your account',
                'ko' => '계정에 로그인',
                'ja' => 'アカウントにログイン',
                'zh' => '登录账户',
            ],
            'line_3' => [
                'vi' => 'Trong trường hợp bạn không thực hiện thay đổi mật khẩu, vui lòng liên hệ ngay với bộ phận hỗ trợ của chúng tôi để được hỗ trợ kịp thời.',
                'en' => 'If you did not make this password change, please contact our support team immediately for assistance.',
                'ko' => '비밀번호를 직접 변경하지 않았다면 즉시 고객지원팀에 문의해 주세요.',
                'ja' => 'このパスワード変更に心当たりがない場合は、すぐにサポートへご連絡ください。',
                'zh' => '如果这次密码变更并非你本人操作，请立即联系支持团队以获得帮助。',
            ],
        ];

        return (new MailMessage)
            ->subject(localizedValue($texts['subject'], $this->mailLocale))
            ->greeting(localizedValue($texts['greeting'], $this->mailLocale))
            ->line(localizedValue($texts['line_1'], $this->mailLocale))
            ->line(localizedValue($texts['line_2'], $this->mailLocale))
            ->action(localizedValue($texts['action'], $this->mailLocale), url('/dang-nhap'))
            ->line(localizedValue($texts['line_3'], $this->mailLocale));
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
