<?php

namespace Modules\Courses\seeders;

use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Courses\src\Models\Courses;

class CoursesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create();
        for ($index = 1; $index <=5; $index++){
            $courses = new Courses();
            $courses->name = $faker->name;
            $courses->slug = $faker->slug;
            $courses->detail = '123';
            $courses->teacher_id = 0;
            $courses->thumbnail = '123';
            $courses->price = '123';
            $courses->sale_price = '123';
            $courses->code = '123';
            $courses->durations = '123';
            $courses->is_document = 0;
            $courses->status = 0;
            $courses->supports = '123';
            $courses->created_at = now();
            $courses->updated_at = now();
            $courses->save();
        }
    }
}
