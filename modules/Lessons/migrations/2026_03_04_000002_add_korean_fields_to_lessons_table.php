<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up()
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->string('name_ko')->nullable()->after('name_en');
            $table->string('slug_ko')->nullable()->after('slug_en');
            $table->text('description_ko')->nullable()->after('description_en');
        });
    }

    public function down()
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn(['name_ko', 'slug_ko', 'description_ko']);
        });
    }
};
