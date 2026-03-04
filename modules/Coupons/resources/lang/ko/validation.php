<?php

return [
    'required' => ':attribute 항목은 필수입니다.',
    'required_with' => ':values가 있을 때 :attribute 항목도 필수입니다.',

    'max' => ':attribute 항목은 :max자를 초과할 수 없습니다.',
    'min' => ':attribute 항목은 최소 :min 이상이어야 합니다.',

    'integer' => ':attribute 항목은 정수여야 합니다.',
    'numeric' => ':attribute 항목은 숫자여야 합니다.',

    'unique' => ':attribute 항목은 이미 존재합니다.',

    'date' => ':attribute 항목의 날짜 형식이 올바르지 않습니다.',
    'after' => ':attribute 항목은 :date 이후여야 합니다.',
    'after_or_equal' => ':attribute 항목은 :date 이후 또는 같은 날짜여야 합니다.',

    'in' => ':attribute 항목이 유효하지 않습니다.',

    'attributes' => [
        'code' => '쿠폰 코드',
        'discount_type' => '할인 유형',
        'discount_value' => '할인 값',
        'total_condition' => '최소 주문 금액',
        'count' => '사용 횟수',
        'start_date' => '시작일',
        'end_date' => '종료일',
    ],
];
