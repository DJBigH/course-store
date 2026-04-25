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
        if (Schema::hasTable('teacher_ratings')) {
            Schema::table('teacher_ratings', function (Blueprint $table) {
                if (!Schema::hasColumn('teacher_ratings', 'status')) {
                    $table->tinyInteger('status')->default(1)->after('rating')->comment('1: Show, 0: Hide');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('teacher_ratings')) {
            Schema::table('teacher_ratings', function (Blueprint $table) {
                if (Schema::hasColumn('teacher_ratings', 'status')) {
                    $table->dropColumn('status');
                }
            });
        }
    }
};
