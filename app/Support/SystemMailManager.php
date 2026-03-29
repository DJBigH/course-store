<?php

namespace App\Support;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Modules\Settings\src\Models\Setting;

class SystemMailManager
{
    public function keys(): array
    {
        return [
            'mail_enabled',
        ];
    }

    public function configKeys(): array
    {
        return [
            'mail_host',
            'mail_port',
            'mail_encryption',
            'mail_username',
            'mail_password',
            'mail_from_address',
            'mail_from_name',
        ];
    }

    public function settings(): array
    {
        if (!Schema::hasTable('settings')) {
            return $this->withEnvMailConfig([]);
        }

        $settings = Setting::query()
            ->whereIn('key', $this->keys())
            ->pluck('value', 'key')
            ->toArray();

        return $this->withEnvMailConfig($settings);
    }

    public function isEnabled(?array $settings = null): bool
    {
        $settings = $this->withEnvMailConfig($settings ?? $this->settings());

        return (int) ($settings['mail_enabled'] ?? '0') === 1;
    }

    public function isConfigured(?array $settings = null): bool
    {
        $settings = $this->withEnvMailConfig($settings ?? $this->settings());

        foreach (['mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_from_address', 'mail_from_name'] as $key) {
            if (blank($settings[$key] ?? null)) {
                return false;
            }
        }

        return true;
    }

    public function apply(?array $settings = null): void
    {
        $settings = $this->withEnvMailConfig($settings ?? $this->settings());
        $enabled = $this->isEnabled($settings);
        $configured = $this->isConfigured($settings);

        Config::set('mail.runtime_enabled', $enabled);
        Config::set('mail.runtime_configured', $configured);

        if (!$enabled || !$configured) {
            Config::set('mail.default', 'array');
        }
    }

    private function withEnvMailConfig(array $settings): array
    {
        $settings['mail_host'] = (string) (config('mail.mailers.smtp.host') ?? '');
        $settings['mail_port'] = (string) (config('mail.mailers.smtp.port') ?? '');
        $settings['mail_encryption'] = (string) (config('mail.mailers.smtp.encryption') ?? '');
        $settings['mail_username'] = (string) (config('mail.mailers.smtp.username') ?? '');
        $settings['mail_password'] = (string) (config('mail.mailers.smtp.password') ?? '');
        $settings['mail_from_address'] = (string) (config('mail.from.address') ?? '');
        $settings['mail_from_name'] = (string) (config('mail.from.name') ?? '');

        return $settings;
    }
}
