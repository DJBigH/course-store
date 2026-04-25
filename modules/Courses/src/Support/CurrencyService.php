<?php

namespace Modules\Courses\src\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Modules\Settings\src\Models\Setting;
use Modules\Courses\src\Models\ExchangeRate;

class CurrencyService
{
    protected string $baseUrl = 'https://fxapi.app/api';

    public function updateRates(): bool
    {
        $response = Http::get("{$this->baseUrl}/usd.json");

        if ($response->successful()) {
            $data = $response->json();
            $rates = array_change_key_case($data['rates'] ?? [], CASE_LOWER);
            
            if (empty($rates) || !isset($rates['vnd'])) {
                \Log::error('FxAPI: Could not find VND rate in response.');
                return false;
            }

            $vndBase = (float) $rates['vnd'];
            
            // 1. Cập nhật tỷ giá USD (VND per USD)
            Setting::updateOrCreate(
                ['key' => 'currency_rate_usd'],
                ['value' => (string) round($vndBase, 2)]
            );
            ExchangeRate::updateOrCreate(['code' => 'USD'], ['rate' => 1]);

            $targetCurrencies = ['KRW', 'JPY', 'CNY'];
            
            foreach ($targetCurrencies as $code) {
                $keyCode = strtolower($code);
                if (isset($rates[$keyCode])) {
                    $ratePerUsd = (float) $rates[$keyCode];
                    // Tỷ giá quy đổi: 1 [Code] = [Value] VND
                    $vndPerUnit = $vndBase / $ratePerUsd;

                    // Cập nhật bảng settings
                    $settingKey = 'currency_rate_' . $keyCode;
                    Setting::updateOrCreate(
                        ['key' => $settingKey],
                        ['value' => (string) round($vndPerUnit, 2)]
                    );

                    // Cập nhật bảng ExchangeRate
                    ExchangeRate::updateOrCreate(
                        ['code' => $code],
                        ['rate' => (float) $ratePerUsd]
                    );
                }
            }
            
            // Đảm bảo VND luôn có trong ExchangeRate
            ExchangeRate::updateOrCreate(['code' => 'VND'], ['rate' => $vndBase]);

            // Xóa cache để hệ thống nhận tỉ giá mới
            Cache::forget('currency_rates_base_vnd');
            Cache::forget('exchange_rates');
            
            return true;
        }

        \Log::error('FxAPI Error: ' . $response->body());
        return false;
    }

    public function convert(float $amount, string $from, string $to, bool $includeFee = false): float
    {
        if ($from === $to) {
            return $amount;
        }

        $rates = Cache::remember('currency_rates_base_vnd', 3600, function () {
            return [
                'VND' => 1.0,
                'USD' => (float) Setting::getValue('currency_rate_usd') ?: 25000.0,
                'KRW' => (float) Setting::getValue('currency_rate_krw') ?: 18.0,
                'JPY' => (float) Setting::getValue('currency_rate_jpy') ?: 160.0,
                'CNY' => (float) Setting::getValue('currency_rate_cny') ?: 3500.0,
            ];
        });

        if (!isset($rates[$from]) || !isset($rates[$to]) || $rates[$to] == 0) {
            return $amount;
        }

        // Convert to VND first (base)
        $vndAmount = $amount * $rates[$from];
        // Convert to target
        $targetAmount = $vndAmount / $rates[$to];

        if ($includeFee) {
            $feePct = (float) Setting::getValue('currency_conversion_fee') ?: 0;
            // Fee usually reduces the received amount in conversion
            $targetAmount *= (1 - ($feePct / 100));
        }

        return $this->formatCurrency($targetAmount, $to);
    }

    public function formatCurrency(float $amount, string $code): float
    {
        if ($code === 'VND' || $code === 'JPY' || $code === 'KRW') {
            return round($amount, 0);
        }
        return round($amount, 2);
    }

    public function getCurrencySymbol(string $code): string
    {
        return match ($code) {
            'VND' => '₫',
            'USD' => '$',
            'JPY' => '¥',
            'KRW' => '₩',
            'CNY' => '元',
            default => $code,
        };
    }

    public function getLocaleCurrency(string $locale): string
    {
        return match ($locale) {
            'en' => 'USD',
            'ko' => 'KRW',
            'ja' => 'JPY',
            'zh' => 'CNY',
            default => 'VND',
        };
    }
}
