<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('teacher_payout_requests', function (Blueprint $table) {
            $table->string('currency_code', 10)->default('VND')->after('amount');
            $table->decimal('exchange_rate', 15, 6)->default(1.0)->after('currency_code');
            $table->decimal('original_amount', 18, 2)->nullable()->after('exchange_rate');
            $table->decimal('converted_amount_vnd', 18, 2)->nullable()->after('original_amount');
            $table->decimal('fee_percentage', 5, 2)->default(0.0)->after('converted_amount_vnd');
            $table->decimal('fee_amount_vnd', 18, 2)->default(0.0)->after('fee_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_payout_requests', function (Blueprint $table) {
            $table->dropColumn(['currency_code', 'exchange_rate', 'original_amount', 'converted_amount_vnd', 'fee_percentage', 'fee_amount_vnd']);
        });
    }
};
