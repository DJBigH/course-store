<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_applications', function (Blueprint $table) {
            $table->string('cv_file_path', 255)->nullable()->after('cv_file');
            $table->string('identity_file_path', 255)->nullable()->after('identity_file');
        });
    }

    public function down(): void
    {
        Schema::table('teacher_applications', function (Blueprint $table) {
            $table->dropColumn(['cv_file_path', 'identity_file_path']);
        });
    }
};
