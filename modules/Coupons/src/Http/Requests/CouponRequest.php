<?php

namespace Modules\Coupons\src\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CouponRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $id = $this->route('id');

        return [
            'code' => 'required|unique:coupons,code,' . $id,
            'discount_type' => 'required',
            'discount_value' => [
                'required',
                'integer',
                function ($attr, $value, $fail) {
                    if ($this->discount_type === 'percent' && ($value <= 0 || $value > 100)) {
                        $fail('Phần trăm giảm phải từ 1 đến 100.');
                    }

                    if ($this->discount_type === 'value' && $value <= 0) {
                        $fail('Giá trị giảm phải lớn hơn 1.');
                    }
                },
            ],
            'total_condition' => 'integer|nullable|min:0',
            'count' => 'integer|nullable|min:0',
            'start_date' => [
                'nullable',
                'date',
                'after_or_equal:today',
                'required_with:end_date',
            ],
            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
                'required_with:start_date',
            ],

        ];
    }

    public function messages()
    {
        return [
            'required' => __('coupons::validation.required'),
            'required_with' => __('coupons::validation.required_with'),

            'unique' => __('coupons::validation.unique'),

            'integer' => __('coupons::validation.integer'),
            'numeric' => __('coupons::validation.numeric'),
            'min' => __('coupons::validation.min'),

            'date' => __('coupons::validation.date'),
            'after' => __('coupons::validation.after'),
            'after_or_equal' => __('coupons::validation.after_or_equal'),

            'in' => __('coupons::validation.in'),
        ];
    }


    public function attributes()
    {
        return __('coupons::validation.attributes');
    }
}
