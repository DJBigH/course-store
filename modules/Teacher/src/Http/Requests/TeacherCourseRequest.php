<?php

namespace Modules\Teacher\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeacherCourseRequest extends FormRequest
{
    protected const MAX_COURSE_PRICE = 99999999;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $normalizeInteger = static function ($value) {
            if ($value === null || $value === '') {
                return 0;
            }

            return (int) preg_replace('/[^\d]/', '', (string) $value);
        };

        $normalizeFloat = static function ($value) {
            if ($value === null || $value === '') {
                return 0;
            }

            return (float) preg_replace('/[^-0-9.]/', '', (string) $value);
        };

        $this->merge([
            'price' => $normalizeInteger($this->input('price')),
            'sale_price' => $normalizeInteger($this->input('sale_price')),
            'price_en' => $normalizeFloat($this->input('price_en')),
            'sale_price_en' => $normalizeFloat($this->input('sale_price_en')),
            'price_ko' => $normalizeFloat($this->input('price_ko')),
            'sale_price_ko' => $normalizeFloat($this->input('sale_price_ko')),
            'price_ja' => $normalizeFloat($this->input('price_ja')),
            'sale_price_ja' => $normalizeFloat($this->input('sale_price_ja')),
            'price_zh' => $normalizeFloat($this->input('price_zh')),
            'sale_price_zh' => $normalizeFloat($this->input('sale_price_zh')),
        ]);
    }

    public function rules(): array
    {
        $courseId = (int) ($this->route('course') ?? 0);

        return [
            'name' => ['required', 'string', 'max:225'],
            'name_en' => ['nullable', 'string', 'max:225'],
            'name_ko' => ['nullable', 'string', 'max:225'],
            'name_ja' => ['nullable', 'string', 'max:225'],
            'name_zh' => ['nullable', 'string', 'max:225'],
            'detail' => ['required', 'string'],
            'detail_en' => ['nullable', 'string'],
            'detail_ko' => ['nullable', 'string'],
            'detail_ja' => ['nullable', 'string'],
            'detail_zh' => ['nullable', 'string'],
            'supports' => ['required', 'string'],
            'supports_en' => ['nullable', 'string'],
            'supports_ko' => ['nullable', 'string'],
            'supports_ja' => ['nullable', 'string'],
            'supports_zh' => ['nullable', 'string'],
            'thumbnail' => ['required', 'string', 'max:225'],
            'code' => ['nullable', 'string', 'max:225', Rule::unique('courses', 'code')->ignore($courseId)],
            'price' => ['required', 'numeric', 'min:0', 'max:' . self::MAX_COURSE_PRICE],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'max:' . self::MAX_COURSE_PRICE, ($this->price > 0 ? 'lt:price' : 'max:0')],
            'price_en' => ['nullable', 'numeric', 'min:0'],
            'sale_price_en' => ['nullable', 'numeric', 'min:0', ($this->price_en > 0 ? 'lt:price_en' : 'max:0')],
            'price_ko' => ['nullable', 'numeric', 'min:0'],
            'sale_price_ko' => ['nullable', 'numeric', 'min:0', ($this->price_ko > 0 ? 'lt:price_ko' : 'max:0')],
            'price_ja' => ['nullable', 'numeric', 'min:0'],
            'sale_price_ja' => ['nullable', 'numeric', 'min:0', ($this->price_ja > 0 ? 'lt:price_ja' : 'max:0')],
            'price_zh' => ['nullable', 'numeric', 'min:0'],
            'sale_price_zh' => ['nullable', 'numeric', 'min:0', ($this->price_zh > 0 ? 'lt:price_zh' : 'max:0')],
            'status' => ['required', 'integer', 'in:0,1'],
            'is_document' => ['required', 'integer', 'in:0,1'],
            'is_learning_locked' => ['required', 'integer', 'in:0,1'],
            'is_coming_soon' => ['nullable'],
            'coming_soon_start_at' => ['nullable', 'date'],
            'sale_type' => ['nullable', 'in:0,1'],
            'end_at' => ['nullable', 'required_if:sale_type,1', 'date', 'after:now'],
            'completion_condition' => ['nullable', 'string', 'in:none,all_lessons,all_quizzes,all'],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['integer', 'distinct', 'exists:categories,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'price.max' => 'Giá khóa học không được vượt quá 99,999,999đ.',
            'sale_price.max' => 'Giá khuyến mãi không được vượt quá 99,999,999đ.',
            'sale_price.lt' => 'Giá khuyến mãi phải nhỏ hơn giá gốc.',
            'quantity.integer' => 'Số lượng phải là số nguyên.',
            'quantity.min' => 'Số lượng không được nhỏ hơn 0.',
            'end_at.required_if' => 'Vui lòng chọn thời gian kết thúc khi sử dụng khuyến mãi có thời hạn.',
            'end_at.after' => 'Thời gian kết thúc khuyến mãi phải ở tương lai.',
        ];
    }
}
