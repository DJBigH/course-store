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
        Schema::table('teacher_course_bundles', function (Blueprint $table) {
            $table->integer('quantity')->nullable()->after('price')->comment('Số lượng giới hạn, null là không giới hạn');
            $table->boolean('is_coming_soon')->default(false)->after('status');
            $table->timestamp('coming_soon_start_at')->nullable()->after('is_coming_soon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_course_bundles', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'is_coming_soon', 'coming_soon_start_at']);
        });
    }
};
