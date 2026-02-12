<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('name_en', 225)->nullable()->after('name');
            $table->string('slug_en', 225)->nullable()->after('slug');
            $table->text('detail_en')->nullable()->after('detail');
            $table->text('supports_en')->nullable()->after('supports');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'slug_en', 'detail_en', 'supports_en']);
        });
    }
};
