<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
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
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject('[BigK Udemy] - Vui lòng kích hoạt tài khoản')
                ->line('Hay ấn chọn vào nút bên dưới để kích hoạt tài khoản của bạn')
                ->action('Kích hoạt tài khoản', $url)
                ->line('Nếu bạn chưa tạo tài khoản thì không cần làm gì.');
        });
    }
}
