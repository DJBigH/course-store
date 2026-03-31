<?php

namespace Modules\Settings\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'site_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'facebook' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'youtube' => ['nullable', 'string', 'max:255'],
            'tiktok' => ['nullable', 'string', 'max:255'],
            'currency_rate_usd' => ['nullable', 'numeric', 'gt:0'],
            'currency_rate_krw' => ['nullable', 'numeric', 'gt:0'],
            'currency_rate_jpy' => ['nullable', 'numeric', 'gt:0'],
            'currency_rate_cny' => ['nullable', 'numeric', 'gt:0'],
            'mail_enabled' => ['nullable', 'boolean'],
            'chatbot_widget_enabled' => ['nullable', 'boolean'],
            'checkout_countdown_minutes' => ['nullable', 'integer', 'min:0', 'max:10080'],
            'max_devices' => ['nullable', 'integer', 'min:1', 'max:10'],
            'chatbot_enabled' => ['nullable', 'boolean'],
            'chatbot_message_ttl_minutes' => ['nullable', 'integer', 'min:5', 'max:1440'],
            'student_two_factor_timeout' => ['nullable', 'integer', 'min:60', 'max:86400'],
            'student_two_factor_code_expire' => ['nullable', 'integer', 'min:60', 'max:86400'],
            'student_two_factor_resend_cooldown' => ['nullable', 'integer', 'min:10', 'max:3600'],
            'global_notice_enabled' => ['nullable', 'boolean'],
            'global_notice_title' => ['nullable', 'string', 'max:255'],
            'global_notice_title_en' => ['nullable', 'string', 'max:255'],
            'global_notice_title_ko' => ['nullable', 'string', 'max:255'],
            'global_notice_title_ja' => ['nullable', 'string', 'max:255'],
            'global_notice_title_zh' => ['nullable', 'string', 'max:255'],
            'global_notice_content' => ['nullable', 'string'],
            'global_notice_content_en' => ['nullable', 'string'],
            'global_notice_content_ko' => ['nullable', 'string'],
            'global_notice_content_ja' => ['nullable', 'string'],
            'global_notice_content_zh' => ['nullable', 'string'],
            'global_notice_link_label' => ['nullable', 'string', 'max:255'],
            'global_notice_link_label_en' => ['nullable', 'string', 'max:255'],
            'global_notice_link_label_ko' => ['nullable', 'string', 'max:255'],
            'global_notice_link_label_ja' => ['nullable', 'string', 'max:255'],
            'global_notice_link_label_zh' => ['nullable', 'string', 'max:255'],
            'global_notice_link_url' => ['nullable', 'string', 'max:255'],
            'global_notice_link_url_en' => ['nullable', 'string', 'max:255'],
            'global_notice_link_url_ko' => ['nullable', 'string', 'max:255'],
            'global_notice_link_url_ja' => ['nullable', 'string', 'max:255'],
            'global_notice_link_url_zh' => ['nullable', 'string', 'max:255'],
            'popup_notice_enabled' => ['nullable', 'boolean'],
            'popup_notice_title' => ['nullable', 'string', 'max:255'],
            'popup_notice_title_en' => ['nullable', 'string', 'max:255'],
            'popup_notice_title_ko' => ['nullable', 'string', 'max:255'],
            'popup_notice_title_ja' => ['nullable', 'string', 'max:255'],
            'popup_notice_title_zh' => ['nullable', 'string', 'max:255'],
            'popup_notice_content' => ['nullable', 'string'],
            'popup_notice_content_en' => ['nullable', 'string'],
            'popup_notice_content_ko' => ['nullable', 'string'],
            'popup_notice_content_ja' => ['nullable', 'string'],
            'popup_notice_content_zh' => ['nullable', 'string'],
            'popup_notice_link_label' => ['nullable', 'string', 'max:255'],
            'popup_notice_link_label_en' => ['nullable', 'string', 'max:255'],
            'popup_notice_link_label_ko' => ['nullable', 'string', 'max:255'],
            'popup_notice_link_label_ja' => ['nullable', 'string', 'max:255'],
            'popup_notice_link_label_zh' => ['nullable', 'string', 'max:255'],
            'popup_notice_link_url' => ['nullable', 'string', 'max:255'],
            'popup_notice_link_url_en' => ['nullable', 'string', 'max:255'],
            'popup_notice_link_url_ko' => ['nullable', 'string', 'max:255'],
            'popup_notice_link_url_ja' => ['nullable', 'string', 'max:255'],
            'popup_notice_link_url_zh' => ['nullable', 'string', 'max:255'],
            'popup_notice_snooze_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
            'banner_slider' => ['nullable', 'array'],
            'banner_slider.*' => ['file', 'image', 'max:2048'],
            'banner_right' => ['nullable', 'array', 'max:3'],
            'banner_right.*' => ['file', 'image', 'max:2048'],
            'banner_full' => ['nullable', 'file', 'image', 'max:4096'],
            'logo' => ['nullable', 'file', 'image', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'banner_right.max' => 'Banner ben phai chi duoc toi da 3 anh.',
        ];
    }
}

