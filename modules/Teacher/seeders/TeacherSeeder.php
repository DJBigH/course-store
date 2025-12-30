<?php

namespace Modules\Teacher\Seeders;

use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Teacher\Src\Models\Teacher;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create();
        for ($index = 1; $index <=5; $index++){
            $teacher = new Teacher();
            $teacher->name = $faker->name;
            $teacher->slug = $faker->slug;
            $teacher->description = "Demo test";
            $teacher->exp = 1;
            $teacher->image = null;
            $teacher->save();
        }
    }
}
