<?php

namespace Modules\Courses\seeders;

use Illuminate\Database\Seeder;
use Modules\Settings\src\Models\Setting;
use Modules\Courses\src\Models\ExchangeRate;

class ExchangeRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::updateOrCreate(['key' => 'currency_conversion_fee'], ['value' => '2']);
        Setting::updateOrCreate(['key' => 'exchange_rate_api_key'], ['value' => 'fx_placeholder']);

        // Seed some approximate rates relative to 1 USD
        $rates = [
            'USD' => 1,
            'VND' => 25450,
            'KRW' => 1375,
            'JPY' => 155,
            'CNY' => 7.24
        ];

        foreach ($rates as $code => $rate) {
            ExchangeRate::updateOrCreate(['code' => $code], ['rate' => $rate]);
        }
    }
}
