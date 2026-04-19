<?php

namespace Modules\Finances\src\Support;

use Illuminate\Support\Collection;
use Modules\Orders\src\Models\OrderDetail;

class FinanceCalculator
{
    public static function breakdown(OrderDetail $detail, float $commissionRate): array
    {
        $grossAmount = (float) ($detail->price ?? 0);
        $order = $detail->order;
        $orderTotal = (float) ($order->total ?? 0);
        $orderDiscount = (float) ($order->discount ?? 0);

        $allocatedDiscount = 0.0;
        if ($orderTotal > 0 && $orderDiscount > 0 && $grossAmount > 0) {
            $allocatedDiscount = min($grossAmount, $orderDiscount * ($grossAmount / $orderTotal));
        }

        $netRevenue = max($grossAmount - $allocatedDiscount, 0);
        $teacherRevenue = $netRevenue * max(min($commissionRate, 100), 0) / 100;
        $platformRevenue = max($netRevenue - $teacherRevenue, 0);

        return [
            'gross_amount' => $grossAmount,
            'allocated_discount' => $allocatedDiscount,
            'net_revenue' => $netRevenue,
            'teacher_revenue' => $teacherRevenue,
            'platform_revenue' => $platformRevenue,
            'commission_rate' => $commissionRate,
        ];
    }

    public static function summarize(iterable $details, callable $commissionResolver): array
    {
        $summary = [
            'gross_amount' => 0.0,
            'allocated_discount' => 0.0,
            'net_revenue' => 0.0,
            'teacher_revenue' => 0.0,
            'platform_revenue' => 0.0,
        ];

        foreach ($details as $detail) {
            if (!$detail instanceof OrderDetail) {
                continue;
            }

            $commissionRate = (float) $commissionResolver($detail);
            $breakdown = self::breakdown($detail, $commissionRate);

            foreach ($summary as $key => $value) {
                $summary[$key] += $breakdown[$key];
            }
        }

        return $summary;
    }

    public static function decorate(Collection $details, callable $commissionResolver): Collection
    {
        return $details->map(function ($detail) use ($commissionResolver) {
            if (!$detail instanceof OrderDetail) {
                return $detail;
            }

            $detail->finance_breakdown = self::breakdown($detail, (float) $commissionResolver($detail));

            return $detail;
        });
    }
}
