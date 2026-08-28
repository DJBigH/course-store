<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->addIndexIfNotExists('orders', ['payment_complete_date'], 'orders_payment_complete_date_index');
        $this->addIndexIfNotExists('orders', ['status_id'], 'orders_status_id_index');
        $this->addIndexIfNotExists('orders', ['currency'], 'orders_currency_index');
        $this->addIndexIfNotExists('orders', ['created_at'], 'orders_created_at_index');

        $this->addIndexIfNotExists('students', ['status'], 'students_status_index');
        $this->addIndexIfNotExists('students', ['created_at'], 'students_created_at_index');

        $this->addIndexIfNotExists('teacher', ['status'], 'teachers_status_index');
        $this->addIndexIfNotExists('teacher', ['created_at'], 'teachers_created_at_index');

        $this->addIndexIfNotExists('courses', ['status'], 'courses_status_index');
        $this->addIndexIfNotExists('courses', ['created_at'], 'courses_created_at_index');

        $this->addIndexIfNotExists('lessons', ['created_at'], 'lessons_created_at_index');
    }

    private function addIndexIfNotExists($table, $columns, $indexName)
    {
        $conn = Schema::getConnection();
        $dbName = $conn->getDatabaseName();
        
        $exists = DB::select("
            SELECT COUNT(*) as count 
            FROM information_schema.statistics 
            WHERE table_schema = ? 
            AND table_name = ? 
            AND index_name = ?
        ", [$dbName, $table, $indexName])[0]->count > 0;

        if (!$exists) {
            Schema::table($table, function (Blueprint $table) use ($columns, $indexName) {
                $table->index($columns, $indexName);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_payment_complete_date_index');
            $table->dropIndex('orders_status_id_index');
            $table->dropIndex('orders_currency_index');
            $table->dropIndex('orders_created_at_index');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('students_status_index');
            $table->dropIndex('students_created_at_index');
        });

        Schema::table('teacher', function (Blueprint $table) {
            $table->dropIndex('teachers_status_index');
            $table->dropIndex('teachers_created_at_index');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex('courses_status_index');
            $table->dropIndex('courses_created_at_index');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropIndex('lessons_created_at_index');
        });
    }
};
