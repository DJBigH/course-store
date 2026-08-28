<?php

namespace Modules\Finances\src\Support;

use Illuminate\Support\Collection;
use Modules\Orders\src\Models\OrderDetail;

class FinanceCalculator
{
    public static function breakdown(OrderDetail $detail, float $commissionRate): array
    {
        $order = $detail->order;
        $vndRate = (float) (\Illuminate\Support\Facades\DB::table('settings')->where('key', 'currency_rate_usd')->value('value') ?: 25000.0);
        $multiplier = 1.0;
        if ($order && $order->currency !== 'VND') {
            $multiplier = $vndRate / ($order->exchange_rate ?: 1.0);
        }

        $grossAmount = (float) ($detail->price ?? 0) * $multiplier;
        $orderTotal = (float) ($order->total ?? 0) * $multiplier;
        $orderDiscount = (float) ($order->discount ?? 0) * $multiplier;

        $allocatedDiscount = 0.0;
        if ($orderTotal > 0 && $orderDiscount > 0 && $grossAmount > 0) {
            $allocatedDiscount = min($grossAmount, $orderDiscount * ($grossAmount / $orderTotal));
        }

        $netRevenue = max($grossAmount - $allocatedDiscount, 0);
        $teacherRevenue = $netRevenue * min($commissionRate, 100) / 100;
        $platformRevenue = $netRevenue - max($teacherRevenue, 0);

        return [
            'gross_amount' => $grossAmount,
            'allocated_discount' => $allocatedDiscount,
            'net_revenue' => $netRevenue,
            'teacher_revenue' => $teacherRevenue,
            'platform_revenue' => $platformRevenue,
            'commission_rate' => $commissionRate,
        ];
    }

    public static function breakdownForUpgrade(\Modules\Orders\src\Models\Order $order, float $commissionRate): array
    {
        $vndRate = (float) (\Illuminate\Support\Facades\DB::table('settings')->where('key', 'currency_rate_usd')->value('value') ?: 25000.0);
        $multiplier = 1.0;
        if ($order->currency !== 'VND') {
            $multiplier = $vndRate / ($order->exchange_rate ?: 1.0);
        }

        $grossAmount = (float) ($order->total ?? 0) * $multiplier;
        $orderDiscount = (float) ($order->discount ?? 0) * $multiplier;

        // For upgrades, the entire discount belongs to the package
        $allocatedDiscount = min($grossAmount, $orderDiscount);
        $netRevenue = max($grossAmount - $allocatedDiscount, 0);
        $teacherRevenue = $netRevenue * min($commissionRate, 100) / 100;
        $platformRevenue = $netRevenue - max($teacherRevenue, 0);

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
