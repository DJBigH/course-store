<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_affiliate_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('teacher_id');
            $table->string('name', 120);
            $table->string('code', 40)->unique();
            $table->string('target_type', 20);
            $table->unsignedBigInteger('target_id')->nullable();
            $table->unsignedInteger('clicks_count')->default(0);
            $table->timestamp('last_clicked_at')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->foreign('teacher_id')->references('id')->on('teacher')->cascadeOnDelete();
            $table->index(['teacher_id', 'target_type']);
        });

        Schema::create('teacher_affiliate_link_clicks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('affiliate_link_id');
            $table->unsignedInteger('teacher_id');
            $table->unsignedInteger('student_id')->nullable();
            $table->string('target_type', 20);
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('locale', 5)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referer_url', 500)->nullable();
            $table->string('target_url', 500)->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamps();

            $table->foreign('affiliate_link_id')->references('id')->on('teacher_affiliate_links')->cascadeOnDelete();
            $table->foreign('teacher_id')->references('id')->on('teacher')->cascadeOnDelete();
            $table->index(['teacher_id', 'target_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_affiliate_link_clicks');
        Schema::dropIfExists('teacher_affiliate_links');
    }
};
