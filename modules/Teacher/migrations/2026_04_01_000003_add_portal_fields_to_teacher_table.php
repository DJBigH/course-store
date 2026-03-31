<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher', function (Blueprint $table) {
            if (!Schema::hasColumn('teacher', 'student_id')) {
                $table->integer('student_id')->unsigned()->nullable()->unique()->after('id');
            }

            if (!Schema::hasColumn('teacher', 'application_id')) {
                $table->integer('application_id')->unsigned()->nullable()->after('student_id');
            }

            if (!Schema::hasColumn('teacher', 'status')) {
                $table->string('status', 30)->default('pending')->after('image');
            }

            if (!Schema::hasColumn('teacher', 'commission_rate')) {
                $table->decimal('commission_rate', 5, 2)->default(50)->after('status');
            }

            if (!Schema::hasColumn('teacher', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('commission_rate');
            }

            if (!Schema::hasColumn('teacher', 'approved_by')) {
                $table->integer('approved_by')->unsigned()->nullable()->after('approved_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('teacher', function (Blueprint $table) {
            if (Schema::hasColumn('teacher', 'student_id')) {
                $table->dropUnique('teacher_student_id_unique');
            }

            foreach (['approved_by', 'approved_at', 'commission_rate', 'status', 'application_id', 'student_id'] as $column) {
                if (Schema::hasColumn('teacher', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
