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
            $table->timestamp('end_at')->nullable()->after('coming_soon_start_at')->comment('Ngày giờ kết thúc bán');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_course_bundles', function (Blueprint $table) {
            $table->dropColumn('end_at');
        });
    }
};
