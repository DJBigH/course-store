<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_payout_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('teacher_id')->unsigned();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('bank_name', 100);
            $table->string('bank_account_name', 120);
            $table->string('bank_account_number', 50);
            $table->text('note')->nullable();
            $table->string('status', 30)->default('requested');
            $table->timestamp('processed_at')->nullable();
            $table->integer('processed_by')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('teacher_id')->references('id')->on('teacher')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_payout_requests');
    }
};
