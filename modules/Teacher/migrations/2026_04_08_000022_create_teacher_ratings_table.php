<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_ratings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('teacher_id');
            $table->unsignedInteger('student_id');
            $table->decimal('rating', 2, 1);
            $table->timestamps();

            $table->unique(['teacher_id', 'student_id'], 'teacher_ratings_unique');
            $table->foreign('teacher_id', 'teacher_ratings_teacher_fk')
                ->references('id')
                ->on('teacher')
                ->cascadeOnDelete();
            $table->foreign('student_id', 'teacher_ratings_student_fk')
                ->references('id')
                ->on('students')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_ratings');
    }
};
