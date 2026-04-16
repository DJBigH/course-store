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
            $table->boolean('can_manage_quizzes')->default(false)->after('can_view_activity_logs');
        });

        DB::table('teacher_packages')->update([
            'can_manage_quizzes' => DB::raw('COALESCE(can_manage_quizzes, 0)'),
        ]);

        DB::table('teacher_packages')->where('code', 'free')->update([
            'can_manage_quizzes' => false,
        ]);

        DB::table('teacher_packages')->where('code', 'starter')->update([
            'can_manage_quizzes' => false,
        ]);

        DB::table('teacher_packages')->whereIn('code', ['pro', 'business', 'premium'])->update([
            'can_manage_quizzes' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table('teacher_packages', function (Blueprint $table) {
            $table->dropColumn('can_manage_quizzes');
        });
    }
};
