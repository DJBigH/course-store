<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('teacher_announcements')) {
            Schema::create('teacher_announcements', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('title_en')->nullable();
                $table->string('title_ko')->nullable();
                $table->string('title_ja')->nullable();
                $table->string('title_zh')->nullable();
                $table->text('message');
                $table->text('message_en')->nullable();
                $table->text('message_ko')->nullable();
                $table->text('message_ja')->nullable();
                $table->text('message_zh')->nullable();
                $table->string('action_url')->nullable();
                $table->string('action_label')->nullable();
                $table->string('action_label_en')->nullable();
                $table->string('action_label_ko')->nullable();
                $table->string('action_label_ja')->nullable();
                $table->string('action_label_zh')->nullable();
                $table->string('icon')->nullable();
                $table->boolean('status')->default(true);
                $table->boolean('is_pinned')->default(false);
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index(['status', 'starts_at', 'ends_at'], 'teacher_announcements_active_idx');
            });
        }

        if (!Schema::hasTable('teacher_announcement_package')) {
            Schema::create('teacher_announcement_package', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('announcement_id');
                $table->unsignedInteger('package_id');
                $table->timestamps();

                $table->unique(['announcement_id', 'package_id'], 'teacher_announcement_package_unique');
                $table->foreign('announcement_id', 'teacher_announcement_package_announcement_fk')
                    ->references('id')
                    ->on('teacher_announcements')
                    ->cascadeOnDelete();
                $table->foreign('package_id', 'teacher_announcement_package_package_fk')
                    ->references('id')
                    ->on('teacher_packages')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_announcement_package');
        Schema::dropIfExists('teacher_announcements');
    }
};
