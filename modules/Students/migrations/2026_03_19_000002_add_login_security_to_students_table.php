<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->timestamp('last_login_at')->nullable()->after('two_factor_email_code_sent_at');
            $table->string('last_login_ip', 64)->nullable()->after('last_login_at');
            $table->text('last_login_user_agent')->nullable()->after('last_login_ip');
            $table->string('last_login_browser')->nullable()->after('last_login_user_agent');
            $table->string('last_login_platform')->nullable()->after('last_login_browser');
            $table->string('last_login_device')->nullable()->after('last_login_platform');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'last_login_at',
                'last_login_ip',
                'last_login_user_agent',
                'last_login_browser',
                'last_login_platform',
                'last_login_device',
            ]);
        });
    }
};
