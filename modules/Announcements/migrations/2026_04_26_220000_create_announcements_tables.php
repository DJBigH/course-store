<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title_en')->nullable();
            $table->string('title_ko')->nullable();
            $table->string('title_ja')->nullable();
            $table->string('title_zh')->nullable();
            
            $table->text('message')->comment('Short summary for notifications');
            $table->text('message_en')->nullable();
            $table->text('message_ko')->nullable();
            $table->text('message_ja')->nullable();
            $table->text('message_zh')->nullable();
            
            $table->longText('content')->comment('Full HTML content for detail page');
            $table->longText('content_en')->nullable();
            $table->longText('content_ko')->nullable();
            $table->longText('content_ja')->nullable();
            $table->longText('content_zh')->nullable();
            
            $table->string('target_type')->default('all')->comment('all, students, teachers, selected');
            
            $table->string('action_url')->nullable();
            $table->string('action_label')->nullable();
            $table->string('action_label_en')->nullable();
            $table->string('action_label_ko')->nullable();
            $table->string('action_label_ja')->nullable();
            $table->string('action_label_zh')->nullable();
            
            $table->boolean('send_email')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('announcement_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained('announcements')->onDelete('cascade');
            $table->unsignedBigInteger('user_id'); // Can be Student or Teacher (Student ID is often the primary User ID)
            $table->timestamps();
        });

        Schema::create('announcement_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained('announcements')->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_reads');
        Schema::dropIfExists('announcement_user');
        Schema::dropIfExists('announcements');
    }
};
