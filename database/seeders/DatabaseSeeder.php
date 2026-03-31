<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Categories\seeders\CategoriesSeeder;
use Modules\Courses\seeders\CoursesSeeder;
use Modules\Orders\src\Models\OrderStatus;
use Modules\Teacher\seeders\TeacherSeeder;
use Modules\User\seeders\UserSeeder;
use Modules\Orders\seeders\OrderStatusSeeder;
use Modules\Settings\seeders\SettingSeeder;
use Modules\Settings\src\Http\Requests\SettingRequest;
use Modules\Settings\src\Models\Setting;
use Modules\User\seeders\GroupSeeder;
use Modules\User\seeders\PermissionSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call([
            GroupSeeder::class,
            PermissionSeeder::class,
            UserSeeder::class,
            TeacherSeeder::class,
            OrderStatusSeeder::class,
            SettingSeeder::class,
            CategoriesSeeder::class,
            CoursesSeeder::class,
        ]);
    }
}
