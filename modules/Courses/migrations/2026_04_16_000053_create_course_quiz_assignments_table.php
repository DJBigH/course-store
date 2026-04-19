<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_quiz_assignments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('quiz_id');
            $table->unsignedInteger('student_id');
            $table->unsignedInteger('assigned_by')->nullable();
            $table->timestamp('deadline_at')->nullable();
            $table->string('status', 30)->default('assigned');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('quiz_id', 'quiz_assignments_quiz_fk')
                ->references('id')
                ->on('course_quizzes')
                ->cascadeOnDelete();

            $table->foreign('student_id', 'quiz_assignments_student_fk')
                ->references('id')
                ->on('students')
                ->cascadeOnDelete();

            $table->index(['quiz_id', 'student_id'], 'quiz_assignments_quiz_student_idx');
            $table->unique(['quiz_id', 'student_id'], 'quiz_assignments_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_quiz_assignments');
    }
};
