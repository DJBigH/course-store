<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_ratings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('course_id');
            $table->unsignedInteger('student_id');
            $table->decimal('rating', 2, 1);
            $table->timestamps();

            $table->unique(['course_id', 'student_id'], 'course_ratings_unique');
            $table->foreign('course_id', 'course_ratings_course_fk')
                ->references('id')
                ->on('courses')
                ->cascadeOnDelete();
            $table->foreign('student_id', 'course_ratings_student_fk')
                ->references('id')
                ->on('students')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_ratings');
    }
};
