<?php

namespace Modules\Teacher\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeacherLessonRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $parentId = (int) $this->input('parent_id', 0);

        $this->merge([
            'parent_id' => $parentId > 0 ? $parentId : null,
            'remove_video' => (int) $this->input('remove_video', 0),
            'remove_document' => (int) $this->input('remove_document', 0),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'name_ko' => ['nullable', 'string', 'max:255'],
            'name_ja' => ['nullable', 'string', 'max:255'],
            'name_zh' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'min:0', 'exists:lessons,id'],
            'is_trial' => ['required', 'integer', 'in:0,1'],
            'position' => ['nullable', 'integer', 'min:1'],
            'video' => ['nullable', 'string', 'max:500'],
            'document' => ['nullable', 'string', 'max:500'],
            'remove_video' => ['nullable', 'integer', 'in:0,1'],
            'remove_document' => ['nullable', 'integer', 'in:0,1'],
            'description' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'description_ko' => ['nullable', 'string'],
            'description_ja' => ['nullable', 'string'],
            'description_zh' => ['nullable', 'string'],
            'status' => ['required', 'integer', 'in:0,1'],
        ];
    }
}
