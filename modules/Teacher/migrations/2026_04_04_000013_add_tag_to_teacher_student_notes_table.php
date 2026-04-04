<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_student_notes', function (Blueprint $table) {
            if (!Schema::hasColumn('teacher_student_notes', 'tag')) {
                $table->string('tag', 30)->nullable()->after('student_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('teacher_student_notes', function (Blueprint $table) {
            if (Schema::hasColumn('teacher_student_notes', 'tag')) {
                $table->dropColumn('tag');
            }
        });
    }
};
