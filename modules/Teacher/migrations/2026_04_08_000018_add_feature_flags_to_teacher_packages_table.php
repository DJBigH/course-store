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
            $table->boolean('can_manage_comments')->default(false)->after('priority_review');
            $table->boolean('can_manage_coupons')->default(false)->after('can_manage_comments');
            $table->boolean('can_grant_courses')->default(false)->after('can_manage_coupons');
            $table->boolean('can_export_orders')->default(false)->after('can_grant_courses');
            $table->boolean('can_export_students')->default(false)->after('can_export_orders');
        });

        DB::table('teacher_packages')->where('code', 'free')->update([
            'can_manage_comments' => false,
            'can_manage_coupons' => false,
            'can_grant_courses' => false,
            'can_export_orders' => false,
            'can_export_students' => false,
        ]);

        DB::table('teacher_packages')->where('code', 'starter')->update([
            'can_manage_comments' => true,
            'can_manage_coupons' => true,
            'can_grant_courses' => false,
            'can_export_orders' => true,
            'can_export_students' => true,
        ]);

        DB::table('teacher_packages')->where('code', 'pro')->update([
            'can_manage_comments' => true,
            'can_manage_coupons' => true,
            'can_grant_courses' => true,
            'can_export_orders' => true,
            'can_export_students' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table('teacher_packages', function (Blueprint $table) {
            $table->dropColumn([
                'can_manage_comments',
                'can_manage_coupons',
                'can_grant_courses',
                'can_export_orders',
                'can_export_students',
            ]);
        });
    }
};
