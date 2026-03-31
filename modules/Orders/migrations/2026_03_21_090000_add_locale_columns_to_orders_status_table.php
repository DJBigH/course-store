<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders_status', function (Blueprint $table) {
            if (! Schema::hasColumn('orders_status', 'name_en')) {
                $table->string('name_en', 200)->nullable()->after('name');
            }

            if (! Schema::hasColumn('orders_status', 'name_ko')) {
                $table->string('name_ko', 200)->nullable()->after('name_en');
            }

            if (! Schema::hasColumn('orders_status', 'name_ja')) {
                $table->string('name_ja', 200)->nullable()->after('name_ko');
            }

            if (! Schema::hasColumn('orders_status', 'name_zh')) {
                $table->string('name_zh', 200)->nullable()->after('name_ja');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders_status', function (Blueprint $table) {
            $columns = [];

            foreach (['name_en', 'name_ko', 'name_ja', 'name_zh'] as $column) {
                if (Schema::hasColumn('orders_status', $column)) {
                    $columns[] = $column;
                }
            }

            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
