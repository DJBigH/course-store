<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_payout_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('teacher_payout_requests', 'admin_note')) {
                $table->text('admin_note')->nullable()->after('note');
            }
        });
    }

    public function down(): void
    {
        Schema::table('teacher_payout_requests', function (Blueprint $table) {
            if (Schema::hasColumn('teacher_payout_requests', 'admin_note')) {
                $table->dropColumn('admin_note');
            }
        });
    }
};
