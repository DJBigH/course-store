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
                'description' => 'Toan quyen quan tri he thong.',
                'is_admin' => true,
            ],
            [
                'id' => 2,
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Quan tri vien van hanh he thong.',
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
