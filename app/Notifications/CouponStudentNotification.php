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
        return [
            'type' => 'conpon.new',
            'message' => 'Bạn có mã giảm giá mới',
            'url' => route('students.account.my-coupon'),
        ];
    }
}
