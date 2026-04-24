<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $blueprint) {
            $blueprint->decimal('price_en', 15, 2)->nullable()->after('sale_price');
            $blueprint->decimal('sale_price_en', 15, 2)->nullable()->after('price_en');
            
            $blueprint->decimal('price_ko', 20, 2)->nullable()->after('sale_price_en');
            $blueprint->decimal('sale_price_ko', 20, 2)->nullable()->after('price_ko');
            
            $blueprint->decimal('price_ja', 20, 2)->nullable()->after('sale_price_ko');
            $blueprint->decimal('sale_price_ja', 20, 2)->nullable()->after('price_ja');
            
            $blueprint->decimal('price_zh', 20, 2)->nullable()->after('sale_price_ja');
            $blueprint->decimal('sale_price_zh', 20, 2)->nullable()->after('price_zh');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $blueprint) {
            $blueprint->dropColumn([
                'price_en', 'sale_price_en',
                'price_ko', 'sale_price_ko',
                'price_ja', 'sale_price_ja',
                'price_zh', 'sale_price_zh',
            ]);
        });
    }
};
