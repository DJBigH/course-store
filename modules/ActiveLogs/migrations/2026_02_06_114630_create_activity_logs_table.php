<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('active_logs', function (Blueprint $table) {
            $table->id();

            $table->string('log_name')->nullable(); // coupon, order, course, auth...
            $table->string('action');               // create, update, delete, assign, revoke...
            $table->string('subject_type')->nullable(); // App\Models\Coupon
            $table->unsignedBigInteger('subject_id')->nullable();

            $table->string('causer_type')->nullable(); // Student / Admin / System
            $table->unsignedBigInteger('causer_id')->nullable();

            $table->json('properties')->nullable(); // dữ liệu trước/sau
            $table->string('description')->nullable();

            $table->ipAddress('ip')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('active_logs');
    }
};
