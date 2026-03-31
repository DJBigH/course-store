<?php

namespace Modules\User\seeders;

use Illuminate\Database\Seeder;
use Modules\User\src\Models\Group;

class GroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            [
                'id' => 1,
                'name' => 'Super Admin',
                'slug' => 'super_admin',
                'description' => 'Toàn quyền quản trị hệ thống.',
                'is_admin' => true,
            ],
            [
                'id' => 2,
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Quản trị viên vận hành hệ thống.',
                'is_admin' => true,
            ],
            [
                'id' => 3,
                'name' => 'Teacher',
                'slug' => 'teacher',
                'description' => 'Giáo viên phụ trách nội dung khóa học và bài giảng.',
                'is_admin' => true,
            ],
        ];

        foreach ($groups as $group) {
            Group::query()->updateOrCreate(
                ['id' => $group['id']],
                $group
            );
        }
    }
}
