<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('teacher_notification_reads')) {
            Schema::create('teacher_notification_reads', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('student_id');
                $table->string('notification_key', 191);
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->unique(['student_id', 'notification_key'], 'teacher_notification_reads_unique');
                $table->foreign('student_id', 'teacher_notification_reads_student_fk')
                    ->references('id')
                    ->on('students')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_notification_reads');
    }
};
