<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereNull('group_id')
            ->orWhere('group_id', 0)
            ->update(['group_id' => 1]);

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('group_id')->default(1)->change();
            $table->index('group_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('group_id')->default(0)->change();
        });
    }
};
