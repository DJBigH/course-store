<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher', function (Blueprint $table) {
            $table->string('badge_key', 50)->nullable()->after('is_premium_badge');
            $table->string('badge_label', 100)->nullable()->after('badge_key');
            $table->string('badge_tone', 30)->nullable()->after('badge_label');
        });
    }

    public function down(): void
    {
        Schema::table('teacher', function (Blueprint $table) {
            $table->dropColumn(['badge_key', 'badge_label', 'badge_tone']);
        });
    }
};
