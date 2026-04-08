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
            $table->boolean('can_duplicate_courses')->default(false)->after('priority_review');
        });

        DB::table('teacher_packages')->where('code', 'free')->update([
            'can_duplicate_courses' => false,
        ]);

        DB::table('teacher_packages')->where('code', 'starter')->update([
            'can_duplicate_courses' => true,
        ]);

        DB::table('teacher_packages')->where('code', 'pro')->update([
            'can_duplicate_courses' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table('teacher_packages', function (Blueprint $table) {
            $table->dropColumn('can_duplicate_courses');
        });
    }
};
