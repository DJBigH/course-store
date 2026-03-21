<?php

namespace Modules\Orders\seeders;

use Illuminate\Database\Seeder;
use Modules\Orders\src\Models\OrderStatus;

class OrderStatusSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            1 => [
                'name' => 'Chờ thanh toán',
                'name_en' => 'Pending payment',
                'name_ko' => '결제 대기',
                'name_ja' => '支払い待ち',
                'name_zh' => '待付款',
                'color' => 'warning',
                'is_success' => false,
            ],
            2 => [
                'name' => 'Đã thanh toán',
                'name_en' => 'Paid',
                'name_ko' => '결제 완료',
                'name_ja' => '支払い完了',
                'name_zh' => '已付款',
                'color' => 'success',
                'is_success' => true,
            ],
            3 => [
                'name' => 'Thanh toán thất bại',
                'name_en' => 'Payment failed',
                'name_ko' => '결제 실패',
                'name_ja' => '支払い失敗',
                'name_zh' => '支付失败',
                'color' => 'danger',
                'is_success' => false,
            ],
            4 => [
                'name' => 'Hủy thanh toán',
                'name_en' => 'Payment cancelled',
                'name_ko' => '결제 취소',
                'name_ja' => '支払いキャンセル',
                'name_zh' => '取消支付',
                'color' => 'danger',
                'is_success' => false,
            ],
        ];

        foreach ($data as $id => $attributes) {
            OrderStatus::query()->updateOrCreate(
                ['id' => $id],
                array_merge($attributes, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
