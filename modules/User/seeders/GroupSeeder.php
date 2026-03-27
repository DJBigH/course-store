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
                'name' => 'Content',
                'slug' => 'content',
                'description' => 'Quản lý nội dung khóa học, danh mục và bình luận.',
                'is_admin' => true,
            ],
            [
                'id' => 3,
                'name' => 'Sale',
                'slug' => 'sale',
                'description' => 'Theo dõi đơn hàng, khách hàng, coupon và liên hệ.',
                'is_admin' => true,
            ],
            [
                'id' => 4,
                'name' => 'Support',
                'slug' => 'support',
                'description' => 'Chăm sóc khách hàng, xử lý liên hệ và hỗ trợ học viên.',
                'is_admin' => true,
            ],
            [
                'id' => 5,
                'name' => 'Teacher Manager',
                'slug' => 'teacher_manager',
                'description' => 'Quản lý hồ sơ giảng viên và nội dung liên quan đến giảng viên.',
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
