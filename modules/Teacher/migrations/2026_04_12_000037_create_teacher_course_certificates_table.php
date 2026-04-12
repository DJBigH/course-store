<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_course_certificates', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('teacher_id');
            $table->unsignedInteger('student_id');
            $table->unsignedInteger('course_id');
            $table->unsignedInteger('issued_by_student_id')->nullable();
            $table->string('code', 40)->unique();
            $table->string('student_name_snapshot', 225);
            $table->string('course_name_snapshot', 255);
            $table->string('teacher_name_snapshot', 225);
            $table->unsignedInteger('completed_lessons')->default(0);
            $table->unsignedInteger('total_lessons')->default(0);
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->text('note')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();

            $table->unique(['teacher_id', 'student_id', 'course_id'], 'teacher_course_certificates_unique');
            $table->foreign('teacher_id')->references('id')->on('teacher')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('issued_by_student_id', 'teacher_course_certificates_issuer_fk')
                ->references('id')->on('students')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_course_certificates');
    }
};
