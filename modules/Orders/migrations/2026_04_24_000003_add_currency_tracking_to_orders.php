<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $blueprint) {
            $blueprint->string('currency', 3)->default('VND')->after('total');
            $blueprint->decimal('exchange_rate', 20, 8)->nullable()->after('currency');
            $blueprint->decimal('conversion_fee_pct', 5, 2)->nullable()->after('exchange_rate');
            $blueprint->decimal('base_total', 15, 2)->nullable()->after('conversion_fee_pct'); // Amount in base currency (VND)
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['currency', 'exchange_rate', 'conversion_fee_pct', 'base_total']);
        });
    }
};
