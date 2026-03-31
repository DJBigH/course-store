<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_applications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('student_id')->unsigned();
            $table->integer('teacher_id')->unsigned()->nullable();
            $table->integer('package_id')->unsigned()->nullable();
            $table->string('status', 30)->default('draft');
            $table->string('full_name', 100);
            $table->string('display_name', 100)->nullable();
            $table->string('headline', 255)->nullable();
            $table->text('bio')->nullable();
            $table->integer('experience_years')->nullable();
            $table->json('specialties')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 100);
            $table->string('portfolio_url', 255)->nullable();
            $table->string('facebook_url', 255)->nullable();
            $table->string('youtube_url', 255)->nullable();
            $table->string('linkedin_url', 255)->nullable();
            $table->string('intro_video_url', 255)->nullable();
            $table->string('cv_file', 255)->nullable();
            $table->string('identity_file', 255)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->integer('reviewed_by')->unsigned()->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('teacher')->onDelete('set null');
            $table->foreign('package_id')->references('id')->on('teacher_packages')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_applications');
    }
};
