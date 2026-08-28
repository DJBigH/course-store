<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_packages', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('name');
            $blueprint->string('name_en')->nullable();
            $blueprint->text('description')->nullable();
            $blueprint->decimal('price', 15, 2)->default(0);
            $blueprint->decimal('sale_price', 15, 2)->nullable();
            $blueprint->integer('duration_value')->default(1);
            $blueprint->string('duration_unit')->default('day'); // minute, hour, day, month, year, lifetime
            $blueprint->boolean('is_active')->default(true);
            $blueprint->integer('sort_order')->default(0);
            $blueprint->timestamps();
        });

        // Tạo bảng trung gian để lưu lịch sử mua gói của giảng viên (nếu cần truy vết sau này)
        Schema::create('teacher_telegram_subscriptions', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->unsignedInteger('teacher_id');
            $blueprint->foreign('teacher_id')->references('id')->on('teacher')->onDelete('cascade');
            $blueprint->foreignId('telegram_package_id')->constrained('telegram_packages')->onDelete('cascade');
            $blueprint->dateTime('started_at');
            $blueprint->dateTime('expires_at')->nullable();
            $blueprint->decimal('amount', 15, 2);
            $blueprint->string('status')->default('active'); // active, expired, cancelled
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_telegram_subscriptions');
        Schema::dropIfExists('telegram_packages');
    }
};
