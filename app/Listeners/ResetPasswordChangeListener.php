<?php

namespace App\Listeners;

use App\Notifications\AdminPasswordChangedNotification;
use App\Notifications\ResetPasswordChangeNotification;
use Illuminate\Auth\Events\PasswordReset;
use Modules\User\src\Models\User;

class ResetPasswordChangeListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PasswordReset $event): void
    {
        if ($event->user instanceof User) {
            $event->user->notify(new AdminPasswordChangedNotification());

            return;
        }

        $event->user->notify(new ResetPasswordChangeNotification());
    }
}
