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
            $table->boolean('can_sell_bundles')->default(false)->after('can_export_students');
            $table->boolean('can_schedule_content')->default(false)->after('can_sell_bundles');
            $table->boolean('can_send_promotions')->default(false)->after('can_schedule_content');
            $table->boolean('can_issue_certificates')->default(false)->after('can_send_promotions');
            $table->boolean('can_customize_teacher_landing')->default(false)->after('can_issue_certificates');
            $table->boolean('can_use_affiliate_links')->default(false)->after('can_customize_teacher_landing');
        });

        DB::table('teacher_packages')->where('code', 'free')->update([
            'can_sell_bundles' => false,
            'can_schedule_content' => false,
            'can_send_promotions' => false,
            'can_issue_certificates' => false,
            'can_customize_teacher_landing' => false,
            'can_use_affiliate_links' => false,
        ]);

        DB::table('teacher_packages')->where('code', 'starter')->update([
            'can_sell_bundles' => false,
            'can_schedule_content' => false,
            'can_send_promotions' => true,
            'can_issue_certificates' => false,
            'can_customize_teacher_landing' => true,
            'can_use_affiliate_links' => false,
        ]);

        DB::table('teacher_packages')->whereIn('code', ['pro', 'business', 'premium'])->update([
            'can_sell_bundles' => true,
            'can_schedule_content' => true,
            'can_send_promotions' => true,
            'can_issue_certificates' => true,
            'can_customize_teacher_landing' => true,
            'can_use_affiliate_links' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table('teacher_packages', function (Blueprint $table) {
            $table->dropColumn([
                'can_sell_bundles',
                'can_schedule_content',
                'can_send_promotions',
                'can_issue_certificates',
                'can_customize_teacher_landing',
                'can_use_affiliate_links',
            ]);
        });
    }
};
