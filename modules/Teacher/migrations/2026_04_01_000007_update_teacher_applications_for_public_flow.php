<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_applications', function (Blueprint $table) {
            $table->string('applicant_type', 20)->default('student')->after('student_id');
            $table->string('payment_method', 30)->nullable()->after('package_id');
            $table->timestamp('account_created_at')->nullable()->after('reviewed_at');
            $table->timestamp('account_credentials_sent_at')->nullable()->after('account_created_at');
        });

        Schema::table('teacher_applications', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
        });

        DB::statement('ALTER TABLE teacher_applications MODIFY student_id INT UNSIGNED NULL');

        Schema::table('teacher_applications', function (Blueprint $table) {
            $table->foreign('student_id')->references('id')->on('students')->nullOnDelete();
        });

        DB::table('teacher_applications')
            ->whereNull('applicant_type')
            ->update(['applicant_type' => 'student']);
    }

    public function down(): void
    {
        Schema::table('teacher_applications', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
        });

        DB::statement('ALTER TABLE teacher_applications MODIFY student_id INT UNSIGNED NOT NULL');

        Schema::table('teacher_applications', function (Blueprint $table) {
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->dropColumn([
                'applicant_type',
                'payment_method',
                'account_created_at',
                'account_credentials_sent_at',
            ]);
        });
    }
};
