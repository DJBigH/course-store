<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('subject', 150)->nullable()->after('email');
            $table->string('submission_type', 30)->default('contact')->after('subject');
            $table->string('category', 50)->nullable()->after('submission_type');
            $table->string('workflow_status', 30)->default('new')->after('status');
            $table->string('source', 30)->default('public')->after('workflow_status');
            $table->string('page_url', 500)->nullable()->after('source');
            $table->unsignedInteger('student_id')->nullable()->after('page_url');
            $table->unsignedInteger('teacher_id')->nullable()->after('student_id');
            $table->text('admin_note')->nullable()->after('message');

            $table->foreign('student_id', 'contacts_student_fk')
                ->references('id')
                ->on('students')
                ->nullOnDelete();

            $table->foreign('teacher_id', 'contacts_teacher_fk')
                ->references('id')
                ->on('teacher')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropForeign('contacts_student_fk');
            $table->dropForeign('contacts_teacher_fk');

            $table->dropColumn([
                'subject',
                'submission_type',
                'category',
                'workflow_status',
                'source',
                'page_url',
                'student_id',
                'teacher_id',
                'admin_note',
            ]);
        });
    }
};
