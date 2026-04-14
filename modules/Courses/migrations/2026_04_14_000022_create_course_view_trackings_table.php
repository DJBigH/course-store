<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_view_trackings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('student_id')->nullable();
            $table->string('visitor_hash', 64);
            $table->date('view_date');
            $table->timestamp('viewed_at');
            $table->timestamps();

            $table->unique(['course_id', 'view_date', 'visitor_hash'], 'course_view_trackings_unique_daily');
            $table->index(['course_id', 'view_date'], 'course_view_trackings_course_date_index');
            $table->index('student_id', 'course_view_trackings_student_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_view_trackings');
    }
};
