<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Students\src\Models\CourseGrant;

class TeacherCourseGiftInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $localeCode;

    public function __construct(
        protected CourseGrant $grant,
        string $locale
    ) {
        $this->localeCode = $locale;
        $this->locale($locale);
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        app()->setLocale($this->localeCode);

        return (new MailMessage)
            ->subject(__('teacher::gifts.mail.subject'))
            ->greeting(__('teacher::gifts.mail.greeting', ['name' => $notifiable->name]))
            ->line(__('teacher::gifts.mail.line_1', [
                'teacher' => $this->grant->teacher?->name ?? 'Teacher',
                'course' => $this->grant->course?->name_locale ?: $this->grant->course?->name ?: __('teacher::gifts.common.course'),
            ]))
            ->line(__('teacher::gifts.mail.line_2'))
            ->action(__('teacher::gifts.mail.action'), $this->giftUrl())
            ->line(__('teacher::gifts.mail.reason', [
                'reason' => $this->reasonLabel(),
            ]))
            ->line(__('teacher::gifts.mail.outro'));
    }

    public function toDatabase($notifiable): array
    {
        app()->setLocale($this->localeCode);

        return [
            'type' => 'student.course_gift',
            'title' => __('teacher::gifts.notification.title'),
            'title_translations' => $this->translated('notification.title'),
            'message' => __('teacher::gifts.notification.message', [
                'teacher' => $this->grant->teacher?->name ?? 'Teacher',
                'course' => $this->grant->course?->name_locale ?: $this->grant->course?->name ?: __('teacher::gifts.common.course'),
            ]),
            'message_translations' => $this->translated('notification.message', [
                'teacher' => $this->grant->teacher?->name ?? 'Teacher',
                'course' => $this->grant->course?->name_locale ?: $this->grant->course?->name ?: 'Course',
            ]),
            'url' => $this->giftUrl(),
            'severity' => 'info',
            'icon' => 'fas fa-gift',
            'entity_type' => CourseGrant::class,
            'entity_id' => $this->grant->id,
            'meta' => [
                'grant_id' => $this->grant->id,
                'course_id' => $this->grant->course_id,
                'teacher_id' => $this->grant->teacher_id,
                'reason' => $this->grant->reason,
            ],
        ];
    }

    protected function giftUrl(): string
    {
        return route('students.account.gifts.show', [
            'locale' => $this->localeCode,
            'token' => $this->grant->token,
        ]);
    }

    protected function reasonLabel(): string
    {
        return __('teacher::gifts.reasons.' . $this->grant->reason);
    }

    protected function translated(string $key, array $replace = []): array
    {
        $translations = [];

        foreach (['vi', 'en', 'ko', 'ja', 'zh'] as $locale) {
            $translations[$locale] = trans('teacher::gifts.' . $key, $replace, $locale);
        }

        return $translations;
    }
}
