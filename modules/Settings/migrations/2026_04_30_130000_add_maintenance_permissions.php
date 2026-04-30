<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('permissions')) {
            $permissions = [
                [
                    'slug' => 'settings.maintenance',
                    'name' => 'Bảo trì hệ thống',
                    'module' => 'Settings',
                    'description' => 'Cho phép xóa Cache, dọn dẹp và bảo trì hệ thống',
                ],
                [
                    'slug' => 'settings.health',
                    'name' => 'Sức khỏe hệ thống',
                    'module' => 'Settings',
                    'description' => 'Cho phép xem trạng thái ổ đĩa, database, mail server',
                ],
                [
                    'slug' => 'media.manage',
                    'name' => 'Quản lý Media',
                    'module' => 'Settings',
                    'description' => 'Cho phép truy cập và quản lý thư viện ảnh/video',
                ],
            ];

            foreach ($permissions as $permission) {
                DB::table('permissions')->updateOrInsert(
                    ['slug' => $permission['slug']],
                    array_merge($permission, [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                );
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->whereIn('slug', [
                'settings.maintenance',
                'settings.health',
                'media.manage'
            ])->delete();
        }
    }
};
