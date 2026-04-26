<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Thêm các trường hỗ trợ khóa tài khoản giáo viên:
     * - is_locked: Trạng thái khóa (1: đã khóa, 0: bình thường)
     * - lock_reason: Lý do khóa
     * - locked_at: Thời điểm khóa
     * - locked_by: ID Admin thực hiện khóa
     */
    public function up(): void
    {
        Schema::table('teacher', function (Blueprint $table) {
            if (!Schema::hasColumn('teacher', 'is_locked')) {
                $table->boolean('is_locked')->default(0)->after('status')
                    ->comment('Trạng thái khóa giáo viên (không khóa học viên)');
            }
            if (!Schema::hasColumn('teacher', 'lock_reason')) {
                $table->text('lock_reason')->nullable()->after('is_locked')
                    ->comment('Lý do khóa tài khoản giáo viên');
            }
            if (!Schema::hasColumn('teacher', 'locked_at')) {
                $table->timestamp('locked_at')->nullable()->after('lock_reason');
            }
            if (!Schema::hasColumn('teacher', 'locked_by')) {
                $table->unsignedBigInteger('locked_by')->nullable()->after('locked_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher', function (Blueprint $table) {
            $table->dropForeign(['locked_by']);
            $table->dropColumn(['is_locked', 'lock_reason', 'locked_at', 'locked_by']);
        });
    }
};
