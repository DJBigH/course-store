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
        Schema::create('teacher_package_features', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name_vi')->nullable();
            $table->string('name_en')->nullable();
            $table->string('name_ko')->nullable();
            $table->string('name_ja')->nullable();
            $table->string('name_zh')->nullable();
            $table->text('description_vi')->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_ko')->nullable();
            $table->text('description_ja')->nullable();
            $table->text('description_zh')->nullable();
            $table->string('group')->nullable();
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_package_features');
    }
};
