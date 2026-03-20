<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Modules\Categories\src\Models\Category;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        VerifyEmail::createUrlUsing(function ($notifiable) {
            return URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                [
                    'locale' => app()->getLocale(),
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );
        });

        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject(__('auth::clients/email.verify.subject'))
                ->greeting(__('auth::clients/email.verify.greeting', [
                    'name' => $notifiable->name ?? '',
                ]))
                ->line(__('auth::clients/email.verify.intro'))
                ->action(__('auth::clients/email.verify.action'), $url)
                ->line(__('auth::clients/email.verify.outro'));
        });

        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            return URL::route('password.reset', [
                'locale' => app()->getLocale(),
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);
        });

        View::composer('layouts.client', function ($view) {
            $courseCategories = Category::withCount('courses')->get();
            $view->with('courseCategories', $courseCategories);
        });
    }
}
