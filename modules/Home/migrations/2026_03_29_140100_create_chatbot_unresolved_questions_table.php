<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_unresolved_questions', function (Blueprint $table) {
            $table->id();
            $table->text('message');
            $table->string('normalized_message', 191)->nullable();
            $table->text('resolved_message')->nullable();
            $table->json('intent_tags')->nullable();
            $table->string('source', 50)->nullable();
            $table->string('fallback_reason', 100)->nullable();
            $table->unsignedInteger('student_id')->nullable();
            $table->foreign('student_id')->references('id')->on('students')->nullOnDelete();
            $table->foreignId('knowledge_id')->nullable()->constrained('chatbot_knowledge')->nullOnDelete();
            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('hit_count')->default(1);
            $table->timestamp('last_asked_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->string('locale', 10)->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['status', 'last_asked_at']);
            $table->index(['normalized_message', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_unresolved_questions');
    }
};
