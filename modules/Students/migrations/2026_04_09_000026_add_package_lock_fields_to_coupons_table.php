<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            if (!Schema::hasColumn('coupons', 'package_locked_at')) {
                $table->timestamp('package_locked_at')->nullable()->after('end_date');
            }

            if (!Schema::hasColumn('coupons', 'package_lock_reason')) {
                $table->string('package_lock_reason', 50)->nullable()->after('package_locked_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('coupons', 'package_lock_reason')) {
                $columns[] = 'package_lock_reason';
            }

            if (Schema::hasColumn('coupons', 'package_locked_at')) {
                $columns[] = 'package_locked_at';
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
