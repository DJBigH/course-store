<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_quiz_submission_answers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('submission_id');
            $table->unsignedInteger('question_id');
            $table->unsignedInteger('choice_id')->nullable();
            $table->text('answer_text')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->unsignedSmallInteger('points_earned')->default(0);
            $table->timestamps();

            $table->foreign('submission_id', 'quiz_submission_answers_submission_fk')
                ->references('id')
                ->on('course_quiz_submissions')
                ->cascadeOnDelete();

            $table->foreign('question_id', 'quiz_submission_answers_question_fk')
                ->references('id')
                ->on('course_quiz_questions')
                ->cascadeOnDelete();

            $table->foreign('choice_id', 'quiz_submission_answers_choice_fk')
                ->references('id')
                ->on('course_quiz_choices')
                ->nullOnDelete();

            $table->unique(['submission_id', 'question_id'], 'quiz_submission_answers_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_quiz_submission_answers');
    }
};
