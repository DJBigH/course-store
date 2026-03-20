<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_name_snapshot')->nullable()->after('student_id');
            $table->string('customer_email_snapshot')->nullable()->after('customer_name_snapshot');
            $table->string('customer_phone_snapshot')->nullable()->after('customer_email_snapshot');
            $table->string('customer_address_snapshot')->nullable()->after('customer_phone_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'customer_name_snapshot',
                'customer_email_snapshot',
                'customer_phone_snapshot',
                'customer_address_snapshot',
            ]);
        });
    }
};
