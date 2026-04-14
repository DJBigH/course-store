<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher', function (Blueprint $table) {
            $table->boolean('is_verified_badge')->default(false)->after('status');
            $table->boolean('is_premium_badge')->default(false)->after('is_verified_badge');
        });
    }

    public function down(): void
    {
        Schema::table('teacher', function (Blueprint $table) {
            $table->dropColumn(['is_verified_badge', 'is_premium_badge']);
        });
    }
};
