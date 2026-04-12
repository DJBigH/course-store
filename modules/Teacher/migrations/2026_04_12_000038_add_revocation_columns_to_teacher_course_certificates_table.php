<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_course_certificates', function (Blueprint $table) {
            $table->timestamp('revoked_at')->nullable()->after('issued_at');
            $table->unsignedInteger('revoked_by_student_id')->nullable()->after('revoked_at');
            $table->string('revoke_reason', 500)->nullable()->after('revoked_by_student_id');

            $table->foreign('revoked_by_student_id', 'teacher_course_certificates_revoked_by_fk')
                ->references('id')->on('students')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('teacher_course_certificates', function (Blueprint $table) {
            $table->dropForeign('teacher_course_certificates_revoked_by_fk');
            $table->dropColumn(['revoked_at', 'revoked_by_student_id', 'revoke_reason']);
        });
    }
};
