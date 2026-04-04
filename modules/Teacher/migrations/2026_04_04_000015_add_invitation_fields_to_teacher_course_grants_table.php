<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::table('teacher_course_grants', function (Blueprint $table) {
            $table->string('status', 30)->default('accepted')->after('note');
            $table->string('token', 100)->nullable()->unique()->after('status');
            $table->string('locale', 10)->nullable()->after('token');
            $table->timestamp('invited_at')->nullable()->after('locale');
            $table->timestamp('accepted_at')->nullable()->after('invited_at');
            $table->timestamp('revoked_at')->nullable()->after('accepted_at');
        });

        DB::table('teacher_course_grants')->update([
            'status' => 'accepted',
            'invited_at' => DB::raw('created_at'),
            'accepted_at' => DB::raw('created_at'),
        ]);
    }

    public function down()
    {
        Schema::table('teacher_course_grants', function (Blueprint $table) {
            $table->dropUnique(['token']);
            $table->dropColumn([
                'status',
                'token',
                'locale',
                'invited_at',
                'accepted_at',
                'revoked_at',
            ]);
        });
    }
};
