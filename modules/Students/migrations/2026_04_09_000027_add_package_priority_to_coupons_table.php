<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            if (!Schema::hasColumn('coupons', 'is_package_priority')) {
                $table->boolean('is_package_priority')->default(false)->after('package_lock_reason');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('coupons', 'is_package_priority')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->dropColumn('is_package_priority');
            });
        }
    }
};
