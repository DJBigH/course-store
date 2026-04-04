<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('teacher', function (Blueprint $table) {
            $table->dateTime('package_started_at')->nullable()->after('approved_by');
            $table->dateTime('package_expires_at')->nullable()->after('package_started_at');
        });

        Schema::table('teacher_applications', function (Blueprint $table) {
            $table->dateTime('activates_at')->nullable()->after('reviewed_at');
            $table->dateTime('package_started_at')->nullable()->after('activates_at');
            $table->dateTime('package_expires_at')->nullable()->after('package_started_at');
            $table->dateTime('activated_at')->nullable()->after('package_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('teacher_applications', function (Blueprint $table) {
            $table->dropColumn(['activates_at', 'package_started_at', 'package_expires_at', 'activated_at']);
        });

        Schema::table('teacher', function (Blueprint $table) {
            $table->dropColumn(['package_started_at', 'package_expires_at']);
        });
    }
};
