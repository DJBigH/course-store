<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_packages', function (Blueprint $table) {
            $table->unsignedTinyInteger('payout_account_limit')->default(3)->after('course_limit');
            $table->unsignedInteger('coupon_limit')->nullable()->after('can_manage_coupons');
        });

        DB::table('teacher_packages')->update([
            'payout_account_limit' => 3,
            'coupon_limit' => null,
        ]);
    }

    public function down(): void
    {
        Schema::table('teacher_packages', function (Blueprint $table) {
            $table->dropColumn([
                'payout_account_limit',
                'coupon_limit',
            ]);
        });
    }
};
