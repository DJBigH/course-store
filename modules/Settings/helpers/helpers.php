<?php

use Modules\Settings\src\Models\Setting;

function setting($key,$defaul='')
{
    $value = Setting::getValue($key);
    return $value ?? $defaul;
}

function setting_url($key)
{
    $value = setting($key);

    return (!empty($value) && $value !== '#') ? $value : '#';
}

function setting_target($key)
{
    $value = setting($key);

    return (!empty($value) && $value !== '#') ? 'target="_blank"' : '';
}
