<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Courses\src\Support\CurrencyService;

class UpdateExchangeRates extends Command
{
    protected $signature = 'app:update-exchange-rates';
    protected $description = 'Fetch latest exchange rates from FxAPI';

    public function handle(CurrencyService $currencyService)
    {
        $this->info('Updating exchange rates...');
        
        if ($currencyService->updateRates()) {
            $this->info('Exchange rates updated successfully.');
        } else {
            $this->error('Failed to update exchange rates. Check logs for details.');
        }
    }
}
