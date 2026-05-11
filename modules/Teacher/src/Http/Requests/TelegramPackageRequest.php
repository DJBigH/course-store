<?php

namespace Modules\Teacher\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Teacher\src\Models\TelegramPackage;

class TelegramPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $units = array_keys(TelegramPackage::getUnits());
        
        return [
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'name_ja' => 'nullable|string|max:255',
            'name_ko' => 'nullable|string|max:255',
            'name_zh' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'duration_value' => 'required|integer|min:1',
            'duration_unit' => 'required|in:' . implode(',', $units),
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'description_ja' => 'nullable|string',
            'description_ko' => 'nullable|string',
            'description_zh' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên gói.',
            'price.required' => 'Vui lòng nhập giá tiền.',
            'price.numeric' => 'Giá tiền phải là con số.',
            'sale_price.lt' => 'Giá khuyến mãi phải nhỏ hơn giá gốc.',
            'duration_value.required' => 'Vui lòng nhập thời lượng.',
            'duration_unit.required' => 'Vui lòng chọn đơn vị thời gian.',
        ];
    }
}
