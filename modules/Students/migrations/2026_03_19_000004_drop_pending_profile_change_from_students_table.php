<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('students')) {
            return;
        }

        $columns = [
            'pending_profile_name',
            'pending_profile_email',
            'pending_profile_phone',
            'pending_profile_address',
            'pending_profile_token',
            'pending_profile_expires_at',
        ];

        $existingColumns = array_values(array_filter($columns, fn($column) => Schema::hasColumn('students', $column)));

        if (empty($existingColumns)) {
            return;
        }

        Schema::table('students', function (Blueprint $table) use ($existingColumns) {
            $table->dropColumn($existingColumns);
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('students')) {
            return;
        }

        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'pending_profile_name')) {
                $table->string('pending_profile_name')->nullable()->after('last_login_device');
            }
            if (!Schema::hasColumn('students', 'pending_profile_email')) {
                $table->string('pending_profile_email')->nullable()->after('pending_profile_name');
            }
            if (!Schema::hasColumn('students', 'pending_profile_phone')) {
                $table->string('pending_profile_phone')->nullable()->after('pending_profile_email');
            }
            if (!Schema::hasColumn('students', 'pending_profile_address')) {
                $table->string('pending_profile_address')->nullable()->after('pending_profile_phone');
            }
            if (!Schema::hasColumn('students', 'pending_profile_token')) {
                $table->string('pending_profile_token', 64)->nullable()->after('pending_profile_address');
            }
            if (!Schema::hasColumn('students', 'pending_profile_expires_at')) {
                $table->timestamp('pending_profile_expires_at')->nullable()->after('pending_profile_token');
            }
        });
    }
};
