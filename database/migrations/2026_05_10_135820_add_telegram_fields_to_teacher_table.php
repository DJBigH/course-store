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
        Schema::table('teacher', function (Blueprint $table) {
            $table->string('telegram_chat_id')->nullable()->after('student_id');
            $table->boolean('is_telegram_notifications_enabled')->default(0)->after('telegram_chat_id');
            $table->timestamp('telegram_feature_expires_at')->nullable()->after('is_telegram_notifications_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher', function (Blueprint $table) {
            $table->dropColumn([
                'telegram_chat_id',
                'is_telegram_notifications_enabled',
                'telegram_feature_expires_at',
            ]);
        });
    }
};
