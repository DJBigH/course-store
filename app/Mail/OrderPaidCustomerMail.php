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

    public Order $order;

    public function __construct(Order $order, string $locale)
    {
        $this->order = $order;
        $this->locale = $locale;
    }

    public function build()
    {
        app()->setLocale($this->locale);

        return $this->subject(__('students::clients/email.order_paid.subject', ['code' => $this->order->code]))
            ->view('emails.order-paid-customer');
    }
}
