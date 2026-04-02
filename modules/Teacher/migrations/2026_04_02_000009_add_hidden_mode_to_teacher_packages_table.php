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
            $table->string('hidden_mode', 20)->default('unavailable')->after('status');
        });

        DB::table('teacher_packages')
            ->whereNull('hidden_mode')
            ->update(['hidden_mode' => 'unavailable']);
    }

    public function down(): void
    {
        Schema::table('teacher_packages', function (Blueprint $table) {
            $table->dropColumn('hidden_mode');
        });
    }
};
