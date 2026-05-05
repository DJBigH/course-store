<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'orderable_id')) {
                $table->unsignedBigInteger('orderable_id')->nullable()->after('student_id');
            }
            if (!Schema::hasColumn('orders', 'orderable_type')) {
                $table->string('orderable_type')->nullable()->after('orderable_id');
            }
            if (!Schema::hasColumn('orders', 'type')) {
                $table->string('type')->default('course_purchase')->after('orderable_type');
            }
            
            $table->index(['orderable_id', 'orderable_type']);
            $table->index('type');
        });
    }
 
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['orderable_id', 'orderable_type']);
            $table->dropIndex(['type']);
            $table->dropColumn(['orderable_id', 'orderable_type', 'type']);
        });
    }
};
