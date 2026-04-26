<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->boolean('is_coming_soon')->default(false)->after('status');
            $table->timestamp('coming_soon_start_at')->nullable()->after('is_coming_soon');
        });
    }

    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['is_coming_soon', 'coming_soon_start_at']);
        });
    }
};
