<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountDeletedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public array $account;
    protected string $mailLocale;

    public function __construct(array $account, string $locale)
    {
        $this->account = $account;
        $this->mailLocale = $locale;
        $this->locale = $locale;
    }

    public function build()
    {
        app()->setLocale($this->mailLocale);

        return $this->subject(__('students::clients/email.account_deleted.subject'))
            ->view('emails.account-deleted', [
                'account' => $this->account,
                'homeUrl' => route('home', ['locale' => $this->mailLocale]),
                'loginUrl' => route('clients-login', ['locale' => $this->mailLocale]),
            ]);
    }
}
