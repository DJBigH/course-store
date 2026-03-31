<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('teacher_packages')) {
            return;
        }

        DB::table('teacher_packages')
            ->where('code', 'free')
            ->update([
                'course_limit' => 2,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('teacher_packages')) {
            return;
        }

        DB::table('teacher_packages')
            ->where('code', 'free')
            ->where('course_limit', 2)
            ->update([
                'course_limit' => 1,
                'updated_at' => now(),
            ]);
    }
};
