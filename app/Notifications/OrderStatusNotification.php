<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Modules\Orders\src\Models\Order;

class OrderStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Order $order,
        protected string $statusType // 'cancelled', 'refunded'
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        switch ($this->statusType) {
            case 'cancelled':
                $titleTranslations = [
                    'vi' => 'Đơn hàng đã bị hủy',
                    'en' => 'Order cancelled',
                    'ko' => '주문이 취소되었습니다',
                    'ja' => '注文がキャンセルされました',
                    'zh' => '订单已取消',
                ];
                $messageTranslations = [
                    'vi' => "Đơn hàng #{$this->order->code} của bạn đã bị hủy.",
                    'en' => "Your order #{$this->order->code} has been cancelled.",
                    'ko' => "주문 #{$this->order->code}이 취소되었습니다.",
                    'ja' => "注文 #{$this->order->code} はキャンセルされました。",
                    'zh' => "您的订单 #{$this->order->code} 已取消。",
                ];
                $severity = 'danger';
                $icon = 'fas fa-times-circle';
                break;
            case 'refunded':
                $titleTranslations = [
                    'vi' => 'Đơn hàng đã được hoàn tiền',
                    'en' => 'Order refunded',
                    'ko' => '주문이 환불되었습니다',
                    'ja' => '注文が払い戻されました',
                    'zh' => '订单已退款',
                ];
                $messageTranslations = [
                    'vi' => "Đơn hàng #{$this->order->code} của bạn đã được hoàn tiền thành công.",
                    'en' => "Your order #{$this->order->code} has been successfully refunded.",
                    'ko' => "주문 #{$this->order->code}이 성공적으로 환불되었습니다.",
                    'ja' => "注文 #{$this->order->code} の払い戻しが正常に完了しました。",
                    'zh' => "您的订单 #{$this->order->code} 已成功退款。",
                ];
                $severity = 'info';
                $icon = 'fas fa-undo';
                break;
            default:
                return [];
        }

        return [
            'type' => 'student.order.' . $this->statusType,
            'title' => $titleTranslations['vi'],
            'title_translations' => $titleTranslations,
            'message' => $messageTranslations['vi'],
            'message_translations' => $messageTranslations,
            'url' => route('student.orders.index'), // Adjust if needed
            'severity' => $severity,
            'icon' => $icon,
            'entity_type' => 'order',
            'entity_id' => $this->order->id,
            'meta' => [
                'order_code' => $this->order->code,
            ],
        ];
    }
}
