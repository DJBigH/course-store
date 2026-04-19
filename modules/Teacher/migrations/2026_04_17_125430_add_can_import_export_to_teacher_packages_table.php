<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('teacher_packages', 'can_import_export')) {
            Schema::table('teacher_packages', function (Blueprint $table) {
                $table->boolean('can_import_export')->default(false)->after('can_view_activity_logs');
            });
        }

        // Sync data from old columns
        DB::table('teacher_packages')->update([
            'can_import_export' => DB::raw('can_export_orders OR can_export_students OR can_import_export_lessons')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('teacher_packages', 'can_import_export')) {
            Schema::table('teacher_packages', function (Blueprint $table) {
                $table->dropColumn('can_import_export');
            });
        }
    }
};
