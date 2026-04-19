<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('teacher_packages', function (Blueprint $blueprint) {
            $blueprint->boolean('can_verify_certificates')->default(false)->after('can_issue_certificates');
        });

        // Tự động bật quyền xác thực cho các gói cao cấp đã tồn tại (nếu cần)
        // Ví dụ: Gói 'pro', 'expert', hoặc các gói có can_issue_certificates = true
        \Illuminate\Support\Facades\DB::table('teacher_packages')
            ->where('can_issue_certificates', true)
            ->where('code', '!=', 'free')
            ->update(['can_verify_certificates' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_packages', function (Blueprint $blueprint) {
            $blueprint->dropColumn('can_verify_certificates');
        });
    }
};
