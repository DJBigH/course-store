<?php

namespace Modules\Categories\seeders;

use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Categories\src\Models\Category;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create();
        for ($index = 1; $index <=5; $index++){
            $cate = new Category();
            $cate->name = $faker->name;
            $cate->slug = $faker->slug;
            $cate->parent_id = 0;
            $cate->save();
        }
    }
}
