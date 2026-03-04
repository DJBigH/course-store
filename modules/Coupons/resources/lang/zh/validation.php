<?php

return [
    'required' => ':attribute は必須です',
    'required_with' => ':values がある場合、:attribute は必須です',

    'max' => ':attribute は :max 文字以内で入力してください',
    'min' => ':attribute は :min 以上である必要があります',

    'integer' => ':attribute は整数である必要があります',
    'numeric' => ':attribute は数値である必要があります',

    'unique' => ':attribute はすでに存在します',

    'date' => ':attribute の日付形式が正しくありません',
    'after' => ':attribute は :date より後の日付にしてください',
    'after_or_equal' => ':attribute は :date 以降の日付にしてください',

    'in' => ':attribute が無効です',

    'attributes' => [
        'code' => 'クーポンコード',
        'discount_type' => '割引タイプ',
        'discount_value' => '割引額',
        'total_condition' => '最低注文金額',
        'count' => '利用回数',
        'start_date' => '開始日',
        'end_date' => '終了日',
    ],
];
