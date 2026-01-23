<?php

namespace Modules\Orders\seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Orders\src\Models\OrderStatus;

class OrderStatusSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $data = [
      [
        'name' => 'Chờ thanh toán',
        'color' => 'warning',
        'is_success' => false,
        'created_at' => now(),
        'updated_at' => now(),
      ],

      [
        'name' => 'Đã thanh toán',
        'color' => 'success',
        'is_success' => true,
        'created_at' => now(),
        'updated_at' => now(),
      ],

      [
        'name' => 'Thanh toán thất bại',
        'color' => 'danger',
        'is_success' => false,
        'created_at' => now(),
        'updated_at' => now(),
      ],

      [
        'name' => 'Hủy thanh toán',
        'color' => 'danger',
        'is_success' => false,
        'created_at' => now(),
        'updated_at' => now(),
      ],
    ];
    OrderStatus::insert($data);
  }
}
