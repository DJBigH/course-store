<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher', function (Blueprint $table) {
            if (!Schema::hasColumn('teacher', 'last_active_at')) {
                $table->timestamp('last_active_at')->nullable()->after('package_expires_at');
            }

            if (!Schema::hasColumn('teacher', 'inactive_teacher_notified_at')) {
                $table->timestamp('inactive_teacher_notified_at')->nullable()->after('last_active_at');
            }

            if (!Schema::hasColumn('teacher', 'inactive_admin_notified_at')) {
                $table->timestamp('inactive_admin_notified_at')->nullable()->after('inactive_teacher_notified_at');
            }
        });
    }

    public function down(): void
    {
        $columns = [];

        foreach (['inactive_admin_notified_at', 'inactive_teacher_notified_at', 'last_active_at'] as $column) {
            if (Schema::hasColumn('teacher', $column)) {
                $columns[] = $column;
            }
        }

        if (!empty($columns)) {
            Schema::table('teacher', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
