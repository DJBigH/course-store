<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPaidNotification extends Notification
{
    use Queueable;

    protected $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $statusName = $this->order->status->name ?? '';
        $messageTranslations = [
            'vi' => 'Có đơn hàng mới ' . $statusName,
            'en' => 'A new order has status ' . $statusName,
            'ko' => '새 주문이 있습니다: ' . $statusName,
            'ja' => '新しい注文があります: ' . $statusName,
            'zh' => '有新订单：' . $statusName,
        ];

        return [
            'message' => $messageTranslations['vi'],
            'message_translations' => $messageTranslations,
            'url' => route('orders.show', $this->order->id),
            'order_id' => $this->order->id,
        ];
    }
}
