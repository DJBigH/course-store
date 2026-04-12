<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons_teacher_course_bundles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bundle_id');
            $table->unsignedInteger('coupon_id');
            $table->timestamps();

            $table->unique(['coupon_id', 'bundle_id'], 'coupon_bundle_unique');
            $table->foreign('bundle_id')
                ->references('id')
                ->on('teacher_course_bundles')
                ->cascadeOnDelete();
            $table->foreign('coupon_id')
                ->references('id')
                ->on('coupons')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons_teacher_course_bundles');
    }
};
