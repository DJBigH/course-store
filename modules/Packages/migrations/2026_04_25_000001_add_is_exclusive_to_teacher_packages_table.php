<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Thêm cờ is_exclusive để đánh dấu gói đặc quyền (tặng riêng cho giáo viên cụ thể).
     * Gói này sẽ không hiển thị trên trang bảng giá công khai dành cho giáo viên.
     */
    public function up(): void
    {
        Schema::table('teacher_packages', function (Blueprint $table) {
            $table->boolean('is_exclusive')->default(false)->after('is_featured')
                ->comment('Gói đặc quyền tặng riêng – không hiển thị trên trang bảng giá công khai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_packages', function (Blueprint $table) {
            $table->dropColumn('is_exclusive');
        });
    }
};
