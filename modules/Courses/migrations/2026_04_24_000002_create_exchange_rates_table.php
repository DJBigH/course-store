<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('code', 3)->unique(); // USD, VND, etc.
            $blueprint->decimal('rate', 20, 8); // Rate relative to 1 USD
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
