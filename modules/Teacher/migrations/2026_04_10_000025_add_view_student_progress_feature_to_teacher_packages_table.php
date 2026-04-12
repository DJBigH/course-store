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
            $table->boolean('can_view_student_progress')->default(false)->after('can_manage_students');
        });

        DB::table('teacher_packages')->where('code', 'free')->update([
            'can_view_student_progress' => false,
        ]);

        DB::table('teacher_packages')->where('code', 'starter')->update([
            'can_view_student_progress' => false,
        ]);

        DB::table('teacher_packages')->where('code', 'pro')->update([
            'can_view_student_progress' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table('teacher_packages', function (Blueprint $table) {
            $table->dropColumn('can_view_student_progress');
        });
    }
};
