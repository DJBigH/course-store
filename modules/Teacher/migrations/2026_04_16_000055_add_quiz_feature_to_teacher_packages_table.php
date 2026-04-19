<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_packages', function (Blueprint $table) {
            // can_manage_quizzes: tạo/sửa/xóa/gán quiz
            if (!Schema::hasColumn('teacher_packages', 'can_manage_quizzes')) {
                $table->boolean('can_manage_quizzes')->default(false)->after('can_sell_bundles');
            }
        });
    }

    public function down(): void
    {
        Schema::table('teacher_packages', function (Blueprint $table) {
            if (Schema::hasColumn('teacher_packages', 'can_manage_quizzes')) {
                $table->dropColumn('can_manage_quizzes');
            }
        });
    }
};
