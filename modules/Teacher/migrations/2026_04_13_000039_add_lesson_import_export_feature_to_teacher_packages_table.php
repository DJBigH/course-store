<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_packages', function (Blueprint $table) {
            $table->boolean('can_import_export_lessons')->default(false)->after('can_export_students');
        });

        DB::table('teacher_packages')
            ->where('code', 'free')
            ->update([
                'can_import_export_lessons' => false,
            ]);

        DB::table('teacher_packages')
            ->whereIn('code', ['starter', 'pro', 'business', 'premium'])
            ->update([
                'can_import_export_lessons' => true,
            ]);
    }

    public function down(): void
    {
        Schema::table('teacher_packages', function (Blueprint $table) {
            $table->dropColumn('can_import_export_lessons');
        });
    }
};
