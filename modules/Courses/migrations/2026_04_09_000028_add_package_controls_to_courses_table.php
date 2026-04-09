<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (!Schema::hasColumn('courses', 'package_locked_at')) {
                $table->timestamp('package_locked_at')->nullable()->after('status');
            }

            if (!Schema::hasColumn('courses', 'package_lock_reason')) {
                $table->string('package_lock_reason', 50)->nullable()->after('package_locked_at');
            }

            if (!Schema::hasColumn('courses', 'is_package_priority')) {
                $table->boolean('is_package_priority')->default(false)->after('package_lock_reason');
            }
        });
    }

    public function down(): void
    {
        $columns = [];

        if (Schema::hasColumn('courses', 'is_package_priority')) {
            $columns[] = 'is_package_priority';
        }

        if (Schema::hasColumn('courses', 'package_lock_reason')) {
            $columns[] = 'package_lock_reason';
        }

        if (Schema::hasColumn('courses', 'package_locked_at')) {
            $columns[] = 'package_locked_at';
        }

        if (!empty($columns)) {
            Schema::table('courses', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
