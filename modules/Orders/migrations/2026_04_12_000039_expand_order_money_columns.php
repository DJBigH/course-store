<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up()
    {
        DB::statement("ALTER TABLE `orders` MODIFY `total` DECIMAL(15,2) NOT NULL DEFAULT 0");

        if ($this->hasDiscountColumn()) {
            DB::statement("ALTER TABLE `orders` MODIFY `discount` DECIMAL(15,2) NOT NULL DEFAULT 0");
        }

        DB::statement("ALTER TABLE `orders_detail` MODIFY `price` DECIMAL(15,2) NOT NULL DEFAULT 0");
    }

    public function down()
    {
        DB::statement("ALTER TABLE `orders` MODIFY `total` FLOAT(10) NOT NULL DEFAULT 0");

        if ($this->hasDiscountColumn()) {
            DB::statement("ALTER TABLE `orders` MODIFY `discount` INT NOT NULL DEFAULT 0");
        }

        DB::statement("ALTER TABLE `orders_detail` MODIFY `price` FLOAT(10) NOT NULL DEFAULT 0");
    }

    protected function hasDiscountColumn(): bool
    {
        return DB::getSchemaBuilder()->hasColumn('orders', 'discount');
    }
};
