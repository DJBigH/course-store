<?php

namespace Modules\Students\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Modules\Orders\src\Repositories\OrdersRepositoryInterface;

class CheckoutController extends Controller
{

    private $orderRepository;

    public function __construct(OrdersRepositoryInterface $ordersRepository)
    {
        $this->orderRepository = $ordersRepository;
    }

    public function index($id){
        $pageTitle = 'Thanh toán';
        $pageName = 'Thanh toán';
        $order = $this->orderRepository->getOrder($id);
        return view('students::clients.checkout', compact('pageTitle', 'pageName','order'));
    }
}
