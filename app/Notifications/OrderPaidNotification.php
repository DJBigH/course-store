<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

use Illuminate\Contracts\Queue\ShouldQueue;

class OrderPaidNotification extends Notification implements ShouldQueue
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
            'ko' => '주문 업데이트',
            'ja' => '注文の更新',
            'zh' => '订单更新',
        ];

        $messageTranslations = [
            'vi' => 'Đơn hàng ' . $orderCode . ' có trạng thái: ' . $statusName,
            'en' => 'Order ' . $orderCode . ' has status: ' . $statusName,
            'ko' => '주문 ' . $orderCode . ' 상태: ' . $statusName,
            'ja' => '注文 ' . $orderCode . ' のステータス: ' . $statusName,
            'zh' => '订单 ' . $orderCode . ' 状态：' . $statusName,
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
