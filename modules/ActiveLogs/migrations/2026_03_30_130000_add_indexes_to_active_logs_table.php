<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::table('active_logs', function (Blueprint $table) {
            $table->index('log_name', 'active_logs_log_name_index');
            $table->index('action', 'active_logs_action_index');
            $table->index(['subject_type', 'subject_id'], 'active_logs_subject_index');
            $table->index('causer_id', 'active_logs_causer_id_index');
            $table->index('ip', 'active_logs_ip_index');
            $table->index('created_at', 'active_logs_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('active_logs', function (Blueprint $table) {
            $table->dropIndex('active_logs_log_name_index');
            $table->dropIndex('active_logs_action_index');
            $table->dropIndex('active_logs_subject_index');
            $table->dropIndex('active_logs_causer_id_index');
            $table->dropIndex('active_logs_ip_index');
            $table->dropIndex('active_logs_created_at_index');
        });
    }
};
