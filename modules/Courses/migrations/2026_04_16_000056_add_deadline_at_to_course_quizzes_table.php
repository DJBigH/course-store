<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_quizzes', function (Blueprint $table) {
            if (!Schema::hasColumn('course_quizzes', 'deadline_at')) {
                $table->timestamp('deadline_at')->nullable()->after('published_at')->comment('Han nop bai quiz');
            }
        });
    }

    public function down(): void
    {
        Schema::table('course_quizzes', function (Blueprint $table) {
            if (Schema::hasColumn('course_quizzes', 'deadline_at')) {
                $table->dropColumn('deadline_at');
            }
        });
    }
};
