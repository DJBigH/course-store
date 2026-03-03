<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Orders\src\Models\Order;

class OrderPaidCustomerMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function build()
    {
        return $this->subject('Thanh toán thành công - Đơn hàng ' . $this->order->code)
            ->view('emails.order-paid-customer');
    }
}
