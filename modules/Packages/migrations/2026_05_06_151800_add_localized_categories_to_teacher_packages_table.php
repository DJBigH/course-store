<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('teacher_packages', function (Blueprint $table) {
            $table->string('category_en')->nullable()->after('category');
            $table->string('category_ko')->nullable()->after('category_en');
            $table->string('category_ja')->nullable()->after('category_ko');
            $table->string('category_zh')->nullable()->after('category_ja');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_packages', function (Blueprint $table) {
            $table->dropColumn(['category_en', 'category_ko', 'category_ja', 'category_zh']);
        });
    }
};
