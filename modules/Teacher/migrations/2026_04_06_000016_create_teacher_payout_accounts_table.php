<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_payout_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('teacher_id');
            $table->string('bank_name', 100);
            $table->string('bank_account_name', 120);
            $table->string('bank_account_number', 50);
            $table->timestamps();

            $table->foreign('teacher_id')->references('id')->on('teacher')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_payout_accounts');
    }
};
