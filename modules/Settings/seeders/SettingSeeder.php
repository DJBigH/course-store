<?php

namespace Modules\Settings\seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Settings\src\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::insert([
            ['key' => 'site_name', 'value' => 'ABC Udemy'],
            ['key' => 'email', 'value' => 'abcudemy@gmail.com'],
            ['key' => 'phone', 'value' => '0123456789'],
            ['key' => 'address', 'value' => 'Việt Nam'],
            ['key' => 'facebook', 'value' => '#'],
            ['key' => 'instagram', 'value' => '#'],
            ['key' => 'youtube', 'value' => '#'],
            ['key' => 'tiktok', 'value' => '#'],
            ['key' => 'seo_description', 'value' => null],
            ['key' => 'seo_keywords', 'value' => null],
        ]);
    }
}
