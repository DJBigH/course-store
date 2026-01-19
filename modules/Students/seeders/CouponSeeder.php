<?php

namespace Modules\Students\seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Students\src\Models\Coupons;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $coupons = new Coupons;
            $coupons->code = generateUniqueCouponCode();
            $coupons->discount_type = rand(0, 1) ? 'percent' : 'value';
            if ($coupons->discount_type == 'percent') {
                $coupons->discount_value = rand(10, 40);
            } else {
                $coupons->discount_value = rand(100000, 300000);
            }
            $coupons->save();
        }
    }
}
