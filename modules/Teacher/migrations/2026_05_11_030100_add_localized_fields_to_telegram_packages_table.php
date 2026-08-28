<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('telegram_packages', function (Blueprint $table) {
            $table->string('name_ja')->nullable()->after('name_en');
            $table->string('name_ko')->nullable()->after('name_ja');
            $table->string('name_zh')->nullable()->after('name_ko');
            
            $table->text('description_en')->nullable()->after('description');
            $table->text('description_ja')->nullable()->after('description_en');
            $table->text('description_ko')->nullable()->after('description_ja');
            $table->text('description_zh')->nullable()->after('description_ko');
        });
    }

    public function down(): void
    {
        Schema::table('telegram_packages', function (Blueprint $table) {
            $table->dropColumn([
                'name_ja', 'name_ko', 'name_zh',
                'description_en', 'description_ja', 'description_ko', 'description_zh'
            ]);
        });
    }
};
