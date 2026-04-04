<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('teacher_applications', 'locale')) {
                $table->string('locale', 5)->nullable()->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('teacher_applications', function (Blueprint $table) {
            if (Schema::hasColumn('teacher_applications', 'locale')) {
                $table->dropColumn('locale');
            }
        });
    }
};
