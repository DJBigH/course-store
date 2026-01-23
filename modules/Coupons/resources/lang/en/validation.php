<?php

return [
    'required' => ':attribute bắt buộc phải nhập',
    'required_with' => ':attribute bắt buộc phải nhập khi có :values',

    'max' => ':attribute không được vượt quá :max ký tự',
    'min' => ':attribute phải lớn hơn hoặc bằng :min',

    'integer' => ':attribute phải là số nguyên',
    'numeric' => ':attribute phải là số',

    'unique' => ':attribute đã tồn tại',

    'date' => ':attribute không đúng định dạng ngày tháng',
    'after' => ':attribute phải sau :date',
    'after_or_equal' => ':attribute phải từ ngày :date trở đi',

    'in' => ':attribute không hợp lệ',

    'attributes' => [
        'code' => 'Mã khuyến mãi',
        'discount_type' => 'Loại giảm',
        'discount_value' => 'Giá trị giảm',
        'total_condition' => 'Giá trị đơn hàng tối thiểu',
        'count' => 'Số lượt sử dụng',
        'start_date' => 'Ngày bắt đầu',
        'end_date' => 'Ngày kết thúc',
    ],
];
