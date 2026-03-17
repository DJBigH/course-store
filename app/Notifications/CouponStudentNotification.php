<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CouponStudentNotification extends Notification
{
    use Queueable;

    protected $conpon;

    public function __construct($conpon)
    {
        $this->conpon = $conpon;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $titleTranslations = [
            'vi' => 'Mã giảm giá mới',
            'en' => 'New coupon',
            'ko' => '새 쿠폰',
            'ja' => '新しいクーポン',
            'zh' => '新优惠券',
        ];

        $messageTranslations = [
            'vi' => 'Bạn có mã giảm giá mới',
            'en' => 'You have received a new coupon',
            'ko' => '새 할인 쿠폰이 도착했습니다',
            'ja' => '新しい割引クーポンがあります',
            'zh' => '你收到了一张新优惠券',
        ];

        return [
            'type' => 'conpon.new',
            'title' => $titleTranslations['vi'],
            'title_translations' => $titleTranslations,
            'message' => $messageTranslations['vi'],
            'message_translations' => $messageTranslations,
            'url' => route('students.account.my-coupon'),
        ];
    }
}
