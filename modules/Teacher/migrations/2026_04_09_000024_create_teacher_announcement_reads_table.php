<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('teacher_announcement_reads')) {
            Schema::create('teacher_announcement_reads', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('announcement_id');
                $table->unsignedInteger('student_id');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->unique(['announcement_id', 'student_id'], 'teacher_announcement_reads_unique');
                $table->foreign('announcement_id', 'teacher_announcement_reads_announcement_fk')
                    ->references('id')
                    ->on('teacher_announcements')
                    ->cascadeOnDelete();
                $table->foreign('student_id', 'teacher_announcement_reads_student_fk')
                    ->references('id')
                    ->on('students')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_announcement_reads');
    }
};
