<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
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
        $statusName = $this->order->status->name_locale ?? '';
        $orderCode = $this->order->code ?? ('#' . $this->order->id);
        $titleTranslations = [
            'vi' => 'Cập nhật đơn hàng',
            'en' => 'Order update',
            'ko' => '?? ????',
            'ja' => '?????',
            'zh' => '????',
        ];

        $messageTranslations = [
            'vi' => 'Đơn hàng ' . $orderCode . ' có trạng thái: ' . $statusName,
            'en' => 'Order ' . $orderCode . ' has status: ' . $statusName,
            'ko' => '?? ' . $orderCode . ' ??: ' . $statusName,
            'ja' => '?? ' . $orderCode . ' ???: ' . $statusName,
            'zh' => '?? ' . $orderCode . ' ??:' . $statusName,
        ];

        return [
            'type' => 'order.status',
            'title' => $titleTranslations['vi'],
            'title_translations' => $titleTranslations,
            'message' => $messageTranslations['vi'],
            'message_translations' => $messageTranslations,
            'url' => route('orders.show', $this->order->id),
            'severity' => 'info',
            'icon' => 'fas fa-receipt',
            'entity_type' => 'order',
            'entity_id' => $this->order->id,
            'order_id' => $this->order->id,
            'meta' => [
                'order_code' => $orderCode,
                'status' => $statusName,
            ],
        ];
    }
}
