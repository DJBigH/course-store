<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_quiz_submissions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('quiz_id');
            $table->unsignedInteger('student_id');
            $table->unsignedInteger('assignment_id')->nullable();
            $table->unsignedSmallInteger('attempt_no')->default(1);
            $table->unsignedSmallInteger('score')->default(0);
            $table->boolean('passed')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->foreign('quiz_id', 'quiz_submissions_quiz_fk')
                ->references('id')
                ->on('course_quizzes')
                ->cascadeOnDelete();

            $table->foreign('student_id', 'quiz_submissions_student_fk')
                ->references('id')
                ->on('students')
                ->cascadeOnDelete();

            $table->foreign('assignment_id', 'quiz_submissions_assignment_fk')
                ->references('id')
                ->on('course_quiz_assignments')
                ->nullOnDelete();

            $table->index(['quiz_id', 'student_id'], 'quiz_submissions_quiz_student_idx');
            $table->index(['student_id', 'submitted_at'], 'quiz_submissions_student_submitted_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_quiz_submissions');
    }
};
