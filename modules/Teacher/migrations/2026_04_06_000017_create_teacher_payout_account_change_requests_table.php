<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_payout_account_change_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('teacher_id');
            $table->unsignedInteger('replace_payout_account_id')->nullable();
            $table->string('replace_bank_name', 100)->nullable();
            $table->string('replace_bank_account_name', 120)->nullable();
            $table->string('replace_bank_account_number', 50)->nullable();
            $table->string('bank_name', 100);
            $table->string('bank_account_name', 120);
            $table->string('bank_account_number', 50);
            $table->text('note')->nullable();
            $table->text('admin_note')->nullable();
            $table->string('status', 30)->default('pending');
            $table->timestamp('processed_at')->nullable();
            $table->unsignedInteger('processed_by')->nullable();
            $table->timestamps();

            $table->foreign('teacher_id', 'tpacr_teacher_fk')->references('id')->on('teacher')->onDelete('cascade');
            $table->foreign('replace_payout_account_id', 'tpacr_replace_account_fk')->references('id')->on('teacher_payout_accounts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_payout_account_change_requests');
    }
};
