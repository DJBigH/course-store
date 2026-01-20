<?php

namespace Modules\Students\src\Repositories;

use App\Repositories\RepositoryInterface;

interface CouponsRepositoryInterface extends RepositoryInterface
{
    public function verifyCoupon($code, $orderId);
}
