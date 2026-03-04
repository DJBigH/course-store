<?php

return [
    'required' => ':attribute 为必填项。',
    'string'   => ':attribute 必须是字符串。',
    'select'   => ':attribute 必须选择。',
    'email'    => ':attribute 格式不正确。',
    'unique'   => ':attribute 已存在。',
    'same'     => '确认密码不匹配。',
    'min'      => ':attribute 至少需要 :min 个字符。',

    'attributes' => [
        'email'            => '邮箱',
        'password'         => '密码',
        'phone'            => '电话号码',
        'confirm_password' => '确认密码',
        'name'             => '姓名',
    ],
];
