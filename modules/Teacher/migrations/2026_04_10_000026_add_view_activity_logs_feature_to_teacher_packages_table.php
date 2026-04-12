<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_packages', function (Blueprint $table) {
            $table->boolean('can_view_activity_logs')->default(false)->after('can_view_student_progress');
        });

        DB::table('teacher_packages')->whereIn('code', ['pro', 'business', 'premium'])->update([
            'can_view_activity_logs' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table('teacher_packages', function (Blueprint $table) {
            $table->dropColumn('can_view_activity_logs');
        });
    }
};
