<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_course_bundles', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('teacher_id');
            $table->string('name', 160);
            $table->string('slug', 190)->unique();
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->boolean('status')->default(true);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->foreign('teacher_id')
                ->references('id')
                ->on('teacher')
                ->cascadeOnDelete();
        });

        Schema::create('teacher_course_bundle_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bundle_id');
            $table->unsignedInteger('course_id');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->unique(['bundle_id', 'course_id']);

            $table->foreign('bundle_id')
                ->references('id')
                ->on('teacher_course_bundles')
                ->cascadeOnDelete();

            $table->foreign('course_id')
                ->references('id')
                ->on('courses')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_course_bundle_items');
        Schema::dropIfExists('teacher_course_bundles');
    }
};
