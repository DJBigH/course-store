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
