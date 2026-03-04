<?php
return [
    'required' => ':attribute は必須です',
    'string' => ':attribute は文字列である必要があります',
    'select' => ':attribute を選択してください',
    'email' => ':attribute のメール形式が正しくありません',
    'unique' => ':attribute はすでに登録されています',
    'same' => '確認用パスワードが一致しません',
    'min' => ':attribute は :min 文字以上で入力してください',
    'attributes' => [
            'email' => 'メールアドレス',
            'password' => 'パスワード',
            'phone' => '電話番号',
            'confirm_password' => '確認用パスワード',
            'name' => '氏名'
    ]
];
