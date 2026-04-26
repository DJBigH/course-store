<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Thêm các trường hỗ trợ luồng "tặng gói chờ nhận":
     * - granted_by: Admin ID đã thực hiện tặng gói
     * - claim_token: Token bảo mật trong link email để giáo viên xác nhận nhận gói
     * - claim_expires_at: Thời hạn để giáo viên xác nhận nhận gói (VD: 30 ngày)
     * - claimed_at: Thời điểm giáo viên đã bấm "Nhận gói"
     */
    public function up(): void
    {
        Schema::table('teacher_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('teacher_applications', 'granted_by')) {
                $table->unsignedBigInteger('granted_by')->nullable()->after('reviewed_by')
                    ->comment('Admin ID đã tặng gói. NULL = đăng ký/nâng cấp thông thường');
            }
            if (!Schema::hasColumn('teacher_applications', 'claim_token')) {
                $table->string('claim_token', 64)->nullable()->unique()->after('granted_by')
                    ->comment('Token bảo mật trong link email để giáo viên xác nhận nhận gói');
            }
            if (!Schema::hasColumn('teacher_applications', 'claim_expires_at')) {
                $table->timestamp('claim_expires_at')->nullable()->after('claim_token')
                    ->comment('Thời hạn xác nhận nhận gói');
            }
            if (!Schema::hasColumn('teacher_applications', 'claimed_at')) {
                $table->timestamp('claimed_at')->nullable()->after('claim_expires_at')
                    ->comment('Thời điểm giáo viên đã bấm Nhận gói');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_applications', function (Blueprint $table) {
            $table->dropForeign(['granted_by']);
            $table->dropColumn(['granted_by', 'claim_token', 'claim_expires_at', 'claimed_at']);
        });
    }
};

