<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up()
    {
        Schema::table('teacher', function (Blueprint $table) {
            $table->text('description_ko')->nullable()->after('description_en');
        });
    }

    public function down()
    {
        Schema::table('teacher', function (Blueprint $table) {
            $table->dropColumn('description_ko');
        });
    }
};
