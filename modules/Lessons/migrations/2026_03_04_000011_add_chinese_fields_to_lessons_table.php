<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->string('name_zh')->nullable()->after('name_ja');
            $table->string('slug_zh')->nullable()->after('slug_ja');
            $table->text('description_zh')->nullable()->after('description_ja');
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn(['name_zh', 'slug_zh', 'description_zh']);
        });
    }
};
