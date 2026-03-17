<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up()
    {
        Schema::table('teacher', function (Blueprint $table) {
            $table->string('name_en', 225)->nullable()->after('name');
            $table->string('name_ko', 225)->nullable()->after('name_en');
            $table->string('slug_en', 225)->nullable()->after('slug');
            $table->string('slug_ko', 225)->nullable()->after('slug_en');
        });
    }

    public function down()
    {
        Schema::table('teacher', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'name_ko', 'slug_en', 'slug_ko']);
        });
    }
};
