<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_quizzes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('course_id');
            $table->unsignedInteger('lesson_id')->nullable()->comment('Quiz gan voi bai hoc cu the');
            $table->string('title', 255);
            $table->string('slug', 255)->nullable();
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('passing_score')->default(70)->comment('% diem can dat de dau');
            $table->unsignedTinyInteger('max_attempts')->nullable()->comment('So lan lam toi da, null = khong gioi han');
            $table->unsignedInteger('time_limit_minutes')->nullable()->comment('Gioi han thoi gian lam bai');
            $table->boolean('show_answers_after')->default(true)->comment('Hien dap an sau khi nop bai');
            $table->unsignedSmallInteger('position')->default(0);
            $table->tinyInteger('status')->default(1)->comment('1=draft/active, 0=an');
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('created_by')->nullable()->comment('Teacher student id or admin id');
            $table->timestamps();

            $table->foreign('course_id', 'course_quizzes_course_fk')
                ->references('id')
                ->on('courses')
                ->cascadeOnDelete();

            $table->foreign('lesson_id', 'course_quizzes_lesson_fk')
                ->references('id')
                ->on('lessons')
                ->nullOnDelete();

            $table->index(['course_id', 'lesson_id'], 'course_quizzes_course_lesson_idx');
            $table->index('course_id', 'course_quizzes_course_idx');
            $table->index('slug', 'course_quizzes_slug_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_quizzes');
    }
};
