<?php

namespace Modules\Courses\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CoursesRequest extends FormRequest
{
    /**
     * Determine if the users is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route()->courses;

        $uniqueRule = 'unique:courses,code';

        if ($id) {
            $uniqueRule .= ',' . $id;
        }

        $rules = [
            'name' => 'required|max:225',
            'name_en' => 'nullable|max:225',
            'name_ko' => 'nullable|max:225',
            'name_ja' => 'nullable|max:225',
            'name_zh' => 'nullable|max:225',
            'slug' => 'required|max:225',
            'slug_en' => 'nullable|max:225',
            'slug_ko' => 'nullable|max:225',
            'slug_ja' => 'nullable|max:225',
            'slug_zh' => 'nullable|max:225',
            'detail' => 'required',
            'detail_en' => 'nullable',
            'detail_ko' => 'nullable',
            'detail_ja' => 'nullable',
            'detail_zh' => 'nullable',
            'teacher_id' => ['required', 'integer', function ($attribute, $value, $fail) {
                if ($value == 0) {
                    $fail('Vui lòng chọn giảng viên.');
                }
            }],
            'thumbnail' => 'required|max:225',
            'code' => 'required|max:225|' . $uniqueRule,
            'is_document' => 'required|integer',
            'supports' => 'required',
            'supports_en' => 'nullable',
            'supports_ko' => 'nullable',
            'supports_ja' => 'nullable',
            'supports_zh' => 'nullable',
            'status' => 'required|integer',
            'is_coming_soon' => 'nullable|integer|in:0,1',
            'coming_soon_start_at' => 'nullable|required_if:is_coming_soon,1|date',
            'is_learning_locked' => 'required|integer|in:0,1',
            'completion_condition' => 'nullable|string|in:none,all_lessons,all_quizzes,all',
            'sale_type' => 'nullable|in:0,1',
            'end_at' => 'nullable|required_if:sale_type,1|date|after:now',
            'price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0' . ($this->price > 0 ? '|lt:price' : '|max:0'),
            'price_en' => 'nullable|numeric|min:0',
            'sale_price_en' => 'nullable|numeric|min:0' . ($this->price_en > 0 ? '|lt:price_en' : '|max:0'),
            'price_ko' => 'nullable|numeric|min:0',
            'sale_price_ko' => 'nullable|numeric|min:0' . ($this->price_ko > 0 ? '|lt:price_ko' : '|max:0'),
            'price_ja' => 'nullable|numeric|min:0',
            'sale_price_ja' => 'nullable|numeric|min:0' . ($this->price_ja > 0 ? '|lt:price_ja' : '|max:0'),
            'price_zh' => 'nullable|numeric|min:0',
            'sale_price_zh' => 'nullable|numeric|min:0' . ($this->price_zh > 0 ? '|lt:price_zh' : '|max:0'),
            'categories' => 'required',
        ];
        return $rules;
    }

    public function attributes()
    {
        return [
            'name' => 'Tên khóa học',
            'slug' => 'Đường dẫn (Slug)',
            'detail' => 'Chi tiết khóa học',
            'teacher_id' => 'Giảng viên',
            'thumbnail' => 'Hình thu nhỏ',
            'code' => 'Mã khóa học',
            'is_document' => 'Tài liệu',
            'supports' => 'Hỗ trợ',
            'status' => 'Trạng thái',
            'categories' => 'Danh mục',
        ];
    }
}
