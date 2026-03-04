<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('name_zh', 225)->nullable()->after('name_ja');
            $table->string('slug_zh', 225)->nullable()->after('slug_ja');
            $table->text('detail_zh')->nullable()->after('detail_ja');
            $table->text('supports_zh')->nullable()->after('supports_ja');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['name_zh', 'slug_zh', 'detail_zh', 'supports_zh']);
        });
    }
};
