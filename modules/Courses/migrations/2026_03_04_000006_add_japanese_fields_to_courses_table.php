<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('name_ja', 225)->nullable()->after('name_ko');
            $table->string('slug_ja', 225)->nullable()->after('slug_ko');
            $table->text('detail_ja')->nullable()->after('detail_ko');
            $table->text('supports_ja')->nullable()->after('supports_ko');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['name_ja', 'slug_ja', 'detail_ja', 'supports_ja']);
        });
    }
};
