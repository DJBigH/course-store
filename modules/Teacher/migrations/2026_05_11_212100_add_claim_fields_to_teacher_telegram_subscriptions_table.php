<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('teacher_telegram_subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('teacher_telegram_subscriptions', 'claim_token')) {
                $table->string('claim_token')->nullable()->after('status');
            }
            if (!Schema::hasColumn('teacher_telegram_subscriptions', 'claimed_at')) {
                $table->timestamp('claimed_at')->nullable()->after('claim_token');
            }
            
            // Đảm bảo started_at có thể null cho quy trình nhận quà
            $table->dateTime('started_at')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('teacher_telegram_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['claim_token', 'claimed_at']);
        });
    }
};
