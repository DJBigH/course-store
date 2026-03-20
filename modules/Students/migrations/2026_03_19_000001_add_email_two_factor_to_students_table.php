<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->boolean('two_factor_email_enabled')->default(false)->after('email_verified_at');
            $table->timestamp('two_factor_email_enabled_at')->nullable()->after('two_factor_email_enabled');
            $table->string('two_factor_email_code')->nullable()->after('two_factor_email_enabled_at');
            $table->string('two_factor_email_purpose', 40)->nullable()->after('two_factor_email_code');
            $table->timestamp('two_factor_email_code_expires_at')->nullable()->after('two_factor_email_purpose');
            $table->timestamp('two_factor_email_code_sent_at')->nullable()->after('two_factor_email_code_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_email_enabled',
                'two_factor_email_enabled_at',
                'two_factor_email_code',
                'two_factor_email_purpose',
                'two_factor_email_code_expires_at',
                'two_factor_email_code_sent_at',
            ]);
        });
    }
};
