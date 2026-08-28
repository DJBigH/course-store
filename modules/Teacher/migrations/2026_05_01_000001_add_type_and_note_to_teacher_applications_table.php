<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_applications', function (Blueprint $table) {
            $table->string('type', 20)->default('new')->after('package_id');
            $table->text('note')->nullable()->after('admin_note');
        });
    }

    public function down(): void
    {
        Schema::table('teacher_applications', function (Blueprint $table) {
            $table->dropColumn(['type', 'note']);
        });
    }
};
