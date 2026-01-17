<?php

namespace Modules\Orders\src\Repositories;

use App\Repositories\BaseRepository;
use Modules\Orders\src\Models\Order;
use Modules\Orders\src\Models\OrderStatus;
use Modules\Orders\src\Repositories\OrdersRepositoryInterface;

class OrdersStatusRepository extends BaseRepository implements OrdersStatusRepositoryInterface
{
    public function getModel()
    {
        return OrderStatus::class;
    }

    public function getOrdersStatus(){
        return $this->model->orderBy('name','ASC')->get();
    }

}
