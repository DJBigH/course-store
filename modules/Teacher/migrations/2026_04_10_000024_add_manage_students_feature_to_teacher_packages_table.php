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
            $table->boolean('can_manage_students')->default(false)->after('can_manage_coupons');
        });

        DB::table('teacher_packages')->where('code', 'free')->update([
            'can_manage_students' => false,
        ]);

        DB::table('teacher_packages')->where('code', 'starter')->update([
            'can_manage_students' => true,
        ]);

        DB::table('teacher_packages')->where('code', 'pro')->update([
            'can_manage_students' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table('teacher_packages', function (Blueprint $table) {
            $table->dropColumn('can_manage_students');
        });
    }
};
