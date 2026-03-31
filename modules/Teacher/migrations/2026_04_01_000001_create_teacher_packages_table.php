<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_packages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->string('billing_cycle', 20)->default('one_time');
            $table->integer('course_limit')->nullable();
            $table->decimal('commission_rate', 5, 2)->default(50);
            $table->boolean('priority_review')->default(false);
            $table->string('support_level', 50)->nullable();
            $table->boolean('status')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('teacher_packages')->insert([
            [
                'code' => 'free',
                'name' => 'Goi Free',
                'description' => 'Bat dau voi quy trinh duyet thu cong va gioi han so khoa hoc.',
                'price' => 0,
                'billing_cycle' => 'one_time',
                'course_limit' => 2,
                'commission_rate' => 50,
                'priority_review' => false,
                'support_level' => 'co ban',
                'status' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'starter',
                'name' => 'Goi Starter',
                'description' => 'Mo rong quyen dang khoa hoc, nhan muc chia doanh thu tot hon.',
                'price' => 499000,
                'billing_cycle' => 'one_time',
                'course_limit' => 5,
                'commission_rate' => 60,
                'priority_review' => false,
                'support_level' => 'email',
                'status' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'pro',
                'name' => 'Goi Pro',
                'description' => 'Duyet uu tien, khong gioi han dang khoa hoc va commission cao nhat.',
                'price' => 990000,
                'billing_cycle' => 'one_time',
                'course_limit' => null,
                'commission_rate' => 70,
                'priority_review' => true,
                'support_level' => 'uu tien',
                'status' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_packages');
    }
};
