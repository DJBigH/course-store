<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('affiliate_link_id')->nullable()->after('bundle_id');
            $table->foreign('affiliate_link_id')
                ->references('id')
                ->on('teacher_affiliate_links')
                ->nullOnDelete();
            $table->index('affiliate_link_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['affiliate_link_id']);
            $table->dropIndex(['affiliate_link_id']);
            $table->dropColumn('affiliate_link_id');
        });
    }
};
