<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher', function (Blueprint $table) {
            $table->string('name_ja', 225)->nullable()->after('name_ko');
            $table->string('slug_ja', 225)->nullable()->after('slug_ko');
            $table->text('description_ja')->nullable()->after('description_ko');
        });
    }

    public function down(): void
    {
        Schema::table('teacher', function (Blueprint $table) {
            $table->dropColumn(['name_ja', 'slug_ja', 'description_ja']);
        });
    }
};
