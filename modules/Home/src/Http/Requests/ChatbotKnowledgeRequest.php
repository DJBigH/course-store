<?php

namespace Modules\Home\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatbotKnowledgeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'max:255'],
            'keywords' => ['nullable', 'string', 'max:5000'],
            'answer' => ['required', 'string', 'max:20000'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
            'unresolved_id' => ['nullable', 'integer', 'exists:chatbot_unresolved_questions,id'],
        ];
    }
}
