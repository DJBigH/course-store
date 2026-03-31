<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

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
        $locale = app()->getLocale();

        $titleTranslations = [
            'vi' => 'Mã giảm giá mới',
            'en' => 'New coupon',
            'ko' => '새 할인 쿠폰',
            'ja' => '新しいクーポン',
            'zh' => '新的优惠券',
        ];

        $messageTranslations = [
            'vi' => 'Bạn có mã giảm giá mới',
            'en' => 'You have received a new coupon',
            'ko' => '새로운 할인 쿠폰을 받으셨습니다',
            'ja' => '新しいクーポンを受け取りました',
            'zh' => '您已收到新的优惠券',
        ];

        return [
            'type' => 'coupon.new',
            'title' => $titleTranslations['vi'],
            'title_translations' => $titleTranslations,
            'message' => $messageTranslations['vi'],
            'message_translations' => $messageTranslations,
            'url' => route('students.account.my-coupon', ['locale' => $locale]),
            'severity' => 'success',
            'icon' => 'fas fa-ticket-alt',
            'entity_type' => 'coupon',
            'entity_id' => $this->coupon->id,
            'meta' => [
                'coupon_code' => $this->coupon->code ?? null,
            ],
        ];
    }
}
