<?php
return [
    'required' => ':attribute 为必填项',
    'max' => ':attribute 不能超过 :max 个字符',
    'min' => ':attribute 至少需要 :min 个字符',
    'integer' => ':attribute 必须是数字',
    'select' => '必须选择 :attribute',
    'attributes' => [
            'name' => '分类名称',
            'name_en' => '英文名称',
            'name_ko' => '韩文名称',
            'name_ja' => '日文名称',
            'name_zh' => '中文名称',
            'slug' => '别名',
            'slug_en' => '英文别名',
            'slug_ko' => '韩文别名',
            'slug_ja' => '日文别名',
            'slug_zh' => '中文别名',
            'parent_id' => '上级分类',
    ]
];
