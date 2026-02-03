<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\View;
use Modules\Categories\src\Models\Category;

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

        View::composer('layouts.client', function ($view) {
            $courseCategories = Category::withCount('courses') // optional
                ->get();

            $view->with('courseCategories', $courseCategories);
        });
    }
}
