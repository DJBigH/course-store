<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\View;
use Modules\Categories\src\Models\Category;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Notifications\ResetPassword;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1) Tạo URL verify có kèm locale
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

        // 2) Custom nội dung email (giữ nguyên như bạn đang làm)
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject(__('clients/verify.subject'))
                ->line(__('clients/verify.intro'))
                ->action(__('clients/verify.action'), $url)
                ->line(__('clients/verify.outro'));
        });

        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            return URL::route('password.reset', [
                'locale' => app()->getLocale(),
                'token'  => $token,
                'email'  => $notifiable->getEmailForPasswordReset(),
            ]);
        });

        View::composer('layouts.client', function ($view) {
            $courseCategories = Category::withCount('courses')
                ->get();

            $view->with('courseCategories', $courseCategories);
        });
    }
}
