<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Locale;

class CouponStudentNotification extends Notification
{
    use Queueable;

    protected $coupon;

    public function __construct($coupon)
    {
        $this->coupon = $coupon;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $locale = method_exists($notifiable, 'preferredLocale')
            ? $notifiable->preferredLocale()
            : app()->getLocale();

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
            'type' => 'coupon.new',
            'title' => $titleTranslations['vi'],
            'title_translations' => $titleTranslations,
            'message' => $messageTranslations['vi'],
            'message_translations' => $messageTranslations,
            'url' => route('students.account.my-coupon', ['locale' => $locale]),
        ];
    }
}
