<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_quiz_questions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('quiz_id');
            $table->text('question');
            $table->enum('question_type', ['single_choice', 'multiple_choice', 'true_false', 'short_answer'])
                ->default('single_choice');
            $table->unsignedSmallInteger('position')->default(0);
            $table->unsignedTinyInteger('points')->default(1)->comment('Diem moi cau');
            $table->boolean('is_required')->default(true);
            $table->text('explanation')->nullable();
            $table->timestamps();

            $table->foreign('quiz_id', 'quiz_questions_quiz_fk')
                ->references('id')
                ->on('course_quizzes')
                ->cascadeOnDelete();

            $table->index(['quiz_id', 'position'], 'quiz_questions_quiz_position_idx');
            $table->index('quiz_id', 'quiz_questions_quiz_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_quiz_questions');
    }
};
