<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_packages', function (Blueprint $table) {
            $table->string('name_en', 100)->nullable()->after('name');
            $table->string('name_ko', 100)->nullable()->after('name_en');
            $table->string('name_ja', 100)->nullable()->after('name_ko');
            $table->string('name_zh', 100)->nullable()->after('name_ja');

            $table->text('description_en')->nullable()->after('description');
            $table->text('description_ko')->nullable()->after('description_en');
            $table->text('description_ja')->nullable()->after('description_ko');
            $table->text('description_zh')->nullable()->after('description_ja');

            $table->string('tagline', 190)->nullable()->after('description_zh');
            $table->string('tagline_en', 190)->nullable()->after('tagline');
            $table->string('tagline_ko', 190)->nullable()->after('tagline_en');
            $table->string('tagline_ja', 190)->nullable()->after('tagline_ko');
            $table->string('tagline_zh', 190)->nullable()->after('tagline_ja');

            $table->string('badge_text', 100)->nullable()->after('tagline_zh');
            $table->string('badge_text_en', 100)->nullable()->after('badge_text');
            $table->string('badge_text_ko', 100)->nullable()->after('badge_text_en');
            $table->string('badge_text_ja', 100)->nullable()->after('badge_text_ko');
            $table->string('badge_text_zh', 100)->nullable()->after('badge_text_ja');

            $table->string('support_level_en', 50)->nullable()->after('support_level');
            $table->string('support_level_ko', 50)->nullable()->after('support_level_en');
            $table->string('support_level_ja', 50)->nullable()->after('support_level_ko');
            $table->string('support_level_zh', 50)->nullable()->after('support_level_ja');

            $table->boolean('is_featured')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('teacher_packages', function (Blueprint $table) {
            $table->dropColumn([
                'name_en',
                'name_ko',
                'name_ja',
                'name_zh',
                'description_en',
                'description_ko',
                'description_ja',
                'description_zh',
                'tagline',
                'tagline_en',
                'tagline_ko',
                'tagline_ja',
                'tagline_zh',
                'badge_text',
                'badge_text_en',
                'badge_text_ko',
                'badge_text_ja',
                'badge_text_zh',
                'support_level_en',
                'support_level_ko',
                'support_level_ja',
                'support_level_zh',
                'is_featured',
            ]);
        });
    }
};
