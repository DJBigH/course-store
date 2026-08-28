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
        Schema::table('teacher_packages', function (Blueprint $table) {
            $table->decimal('max_payout_per_day', 18, 2)->nullable()->after('price')->comment('Hạn mức rút tiền tối đa / ngày (VND), null là không giới hạn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_packages', function (Blueprint $table) {
            $table->dropColumn('max_payout_per_day');
        });
    }
};
