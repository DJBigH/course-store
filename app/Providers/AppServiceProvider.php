<?php

namespace App\Providers;

use App\Support\SystemMailManager;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Modules\Categories\src\Models\Category;
use Modules\Settings\src\Models\Setting;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(SystemMailManager $systemMailManager): void
    {

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
        $systemMailManager->apply();

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
            $announcementKeys = ['global_notice_enabled', 'popup_notice_enabled', 'popup_notice_snooze_minutes'];
            $localizedFields = [
                'global_notice_title',
                'global_notice_content',
                'global_notice_link_label',
                'global_notice_link_url',
                'popup_notice_title',
                'popup_notice_content',
                'popup_notice_link_label',
                'popup_notice_link_url',
            ];
            $localizedSuffixes = ['', '_en', '_ko', '_ja', '_zh'];

            foreach ($localizedFields as $field) {
                foreach ($localizedSuffixes as $suffix) {
                    $announcementKeys[] = $field . $suffix;
                }
            }

            $announcementSettings = Setting::query()
                ->whereIn('key', $announcementKeys)
                ->get(['key', 'value', 'updated_at']);

            $popupAnnouncementVersion = optional(
                $announcementSettings
                    ->whereIn('key', [
                        'popup_notice_enabled',
                        'popup_notice_snooze_minutes',
                        'popup_notice_title',
                        'popup_notice_title_en',
                        'popup_notice_title_ko',
                        'popup_notice_title_ja',
                        'popup_notice_title_zh',
                        'popup_notice_content',
                        'popup_notice_content_en',
                        'popup_notice_content_ko',
                        'popup_notice_content_ja',
                        'popup_notice_content_zh',
                        'popup_notice_link_label',
                        'popup_notice_link_label_en',
                        'popup_notice_link_label_ko',
                        'popup_notice_link_label_ja',
                        'popup_notice_link_label_zh',
                        'popup_notice_link_url',
                        'popup_notice_link_url_en',
                        'popup_notice_link_url_ko',
                        'popup_notice_link_url_ja',
                        'popup_notice_link_url_zh',
                    ])
                    ->sortByDesc('updated_at')
                    ->first()
            )->updated_at?->timestamp;

            $view->with([
                'courseCategories' => $courseCategories,
                'announcementSettings' => $announcementSettings->pluck('value', 'key')->toArray(),
                'popupAnnouncementVersion' => $popupAnnouncementVersion,
            ]);
        });
    }
}
