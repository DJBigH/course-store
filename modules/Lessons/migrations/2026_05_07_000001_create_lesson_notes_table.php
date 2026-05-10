<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lesson_notes', function (Blueprint $column) {
            $column->id();
            $column->unsignedInteger('student_id');
            $column->unsignedInteger('lesson_id');
            $column->integer('time_at')->comment('Time in seconds');
            $column->text('content');
            $column->timestamps();

            $column->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $column->foreign('lesson_id')->references('id')->on('lessons')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_notes');
    }
};
