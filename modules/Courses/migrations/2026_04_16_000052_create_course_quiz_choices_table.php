<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_quiz_choices', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('question_id');
            $table->text('choice_text');
            $table->boolean('is_correct')->default(false);
            $table->boolean('is_exclusive')->default(false)->comment('Dung cho cau true_false hoac single choice');
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->foreign('question_id', 'quiz_choices_question_fk')
                ->references('id')
                ->on('course_quiz_questions')
                ->cascadeOnDelete();

            $table->index(['question_id', 'position'], 'quiz_choices_question_position_idx');
            $table->index('question_id', 'quiz_choices_question_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_quiz_choices');
    }
};
