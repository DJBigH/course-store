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
            'sale_price' => ['nullable', 'numeric', 'min:0', 'max:' . self::MAX_COURSE_PRICE, 'lte:price'],
            'price_en' => ['nullable', 'numeric', 'min:0'],
            'sale_price_en' => ['nullable', 'numeric', 'min:0', 'lte:price_en'],
            'price_ko' => ['nullable', 'numeric', 'min:0'],
            'sale_price_ko' => ['nullable', 'numeric', 'min:0', 'lte:price_ko'],
            'price_ja' => ['nullable', 'numeric', 'min:0'],
            'sale_price_ja' => ['nullable', 'numeric', 'min:0', 'lte:price_ja'],
            'price_zh' => ['nullable', 'numeric', 'min:0'],
            'sale_price_zh' => ['nullable', 'numeric', 'min:0', 'lte:price_zh'],
            'status' => ['required', 'integer', 'in:0,1'],
            'is_document' => ['required', 'integer', 'in:0,1'],
            'is_learning_locked' => ['required', 'integer', 'in:0,1'],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['integer', 'distinct', 'exists:categories,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'price.max' => 'Gia khoa hoc khong duoc vuot qua 99,999,999d.',
            'sale_price.max' => 'Gia khuyen mai khong duoc vuot qua 99,999,999d.',
            'sale_price.lte' => 'Gia khuyen mai phai nho hon hoac bang gia goc.',
        ];
    }
}
