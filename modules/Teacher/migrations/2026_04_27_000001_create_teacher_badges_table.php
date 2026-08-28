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
        Schema::create('teacher_badges', function (Blueprint $table) {
            $table->increments('id');
            $table->json('name'); // Store translations: {"vi": "Xác minh", "en": "Verified"}
            $table->string('code')->unique();
            $table->string('icon')->nullable();
            $table->string('color_bg')->default('#f1f5f9');
            $table->string('color_text')->default('#0f172a');
            $table->json('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('teacher_has_badges', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('teacher_id');
            $table->unsignedInteger('badge_id');
            $table->timestamps();

            $table->foreign('teacher_id')->references('id')->on('teacher')->onDelete('cascade');
            $table->foreign('badge_id')->references('id')->on('teacher_badges')->onDelete('cascade');
            $table->unique(['teacher_id', 'badge_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_has_badges');
        Schema::dropIfExists('teacher_badges');
    }
};
