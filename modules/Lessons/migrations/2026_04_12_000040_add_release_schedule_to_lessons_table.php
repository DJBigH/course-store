<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->string('release_mode', 40)->default('immediate')->after('status');
            $table->dateTime('release_at')->nullable()->after('release_mode');
            $table->unsignedInteger('release_after_days')->nullable()->after('release_at');
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn([
                'release_mode',
                'release_at',
                'release_after_days',
            ]);
        });
    }
};
