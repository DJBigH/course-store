<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('name_ko', 225)->nullable()->after('name_en');
            $table->string('slug_ko', 225)->nullable()->after('slug_en');
            $table->text('detail_ko')->nullable()->after('detail_en');
            $table->text('supports_ko')->nullable()->after('supports_en');
        });
    }

    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['name_ko', 'slug_ko', 'detail_ko', 'supports_ko']);
        });
    }
};
