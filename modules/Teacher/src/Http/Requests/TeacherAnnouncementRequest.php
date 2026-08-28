<?php

namespace Modules\Teacher\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeacherAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'title_ko' => ['nullable', 'string', 'max:255'],
            'title_ja' => ['nullable', 'string', 'max:255'],
            'title_zh' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'message_en' => ['nullable', 'string'],
            'message_ko' => ['nullable', 'string'],
            'message_ja' => ['nullable', 'string'],
            'message_zh' => ['nullable', 'string'],
            'action_url' => ['nullable', 'string', 'max:1000'],
            'action_label' => ['nullable', 'string', 'max:120'],
            'action_label_en' => ['nullable', 'string', 'max:120'],
            'action_label_ko' => ['nullable', 'string', 'max:120'],
            'action_label_ja' => ['nullable', 'string', 'max:120'],
            'action_label_zh' => ['nullable', 'string', 'max:120'],
            'icon' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'boolean'],
            'is_pinned' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'package_ids' => ['nullable', 'array'],
            'package_ids.*' => ['integer', 'exists:teacher_packages,id'],
            'notify_telegram' => ['nullable', 'boolean'],
        ];
    }
}
