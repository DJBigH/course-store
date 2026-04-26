<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            if (!Schema::hasColumn('courses', 'quantity')) {
                $table->integer('quantity')->nullable()->after('price');
            }
            if (!Schema::hasColumn('courses', 'end_at')) {
                $table->timestamp('end_at')->nullable()->after('coming_soon_start_at');
            }
        });
    }

    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'end_at']);
        });
    }
};
