<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

function deleteFileStorage($image)
{
    $imageThumb = dirname($image) . '/thumbs/' . basename($image);
    File::delete(public_path($image));
    File::delete(public_path($imageThumb));
}

function isRoute($routeList)
{
    if (!empty($routeList)) {
        foreach ($routeList as $route) {
            if (request()->is(trim($route, '/'))) {
                return true;
            }
        }
    }

    return false;
}

function activeSidebar($name, $routeList)
{
    return request()->is(trim(route($name . '.index', [], false), '/') . '/*') || request()->is(trim(route($name . '.index', [], false), '/')) || isRoute($routeList);
}

function activeMenu($name)
{
    $url = route($name, ['locale' => app()->getLocale()], false);

    return request()->is(trim($url, '/'));
}


if (!function_exists('vnd_to_usd')) {
    function vnd_to_usd(int $vnd, int $precision = 2): float
    {
        $rate = config('currency.usd_vnd_rate');
        return round($vnd / $rate, $precision);
    }
}

if (!function_exists('usd_to_vnd')) {
    function usd_to_vnd(float $usd): int
    {
        $rate = config('currency.usd_vnd_rate');
        return (int) round($usd * $rate);
    }
}

function moneyLocale($number)
{
    $locale = app()->getLocale();

    if ($locale === 'en') {
        return moneyUS($number);
    }

    if ($locale === 'ko') {
        return moneyKR($number);
    }

    if ($locale === 'ja') {
        return moneyJP($number);
    }

    if ($locale === 'zh') {
        return moneyCN($number);
    }

    // mặc định VI
    return money($number);
}


function money($number, $currency = 'đ', $freeText = 'Miễn phí')
{
    return !empty($number) ? number_format($number) . ' ' . $currency : $freeText;
}

function moneyUS($number, $currency = '$', $freeText = 'Free')
{
    return !empty($number) ? vnd_to_usd($number) . ' ' . $currency : $freeText;
}

function moneyKR($number, $currency = '₫', $freeText = '무료')
{
    return !empty($number) ? number_format($number) . ' ' . $currency : $freeText;
}

function moneyJP($number, $currency = '₫', $freeText = '無料')
{
    return !empty($number) ? number_format($number) . ' ' . $currency : $freeText;
}

function moneyCN($number, $currency = '₫', $freeText = '免费')
{
    return !empty($number) ? number_format($number) . ' ' . $currency : $freeText;
}

function getHour($secounds)
{
    $value = round($secounds / 60, 1);
    return $value . 'h';
}

function getSize($bytes, $precision = 2)
{
    if ($bytes <= 0) return '0 B';

    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $power = floor(log($bytes, 1024));
    $power = min($power, count($units) - 1);

    $value = $bytes / pow(1024, $power);

    return round($value, $precision) . ' ' . $units[$power];
}

function queryActive($query)
{
    $tableName = $query->getModel()->getTable();
    return $query->where($tableName . '.status', 1);
}

function queryPosition($query)
{
    $query->orderBy('position');
}

if (!function_exists('format_date_dmy')) {
    function format_date_dmy($date)
    {
        if (empty($date)) {
            return '';
        }

        try {
            return Carbon::parse($date)->format('j/n/Y');
        } catch (\Exception $e) {
            return '';
        }
    }
}

if (!function_exists('localizedValue')) {
    function localizedValue($data, ?string $locale = null, $fallback = '')
    {
        if (!is_array($data) || empty($data)) {
            return $fallback;
        }

        $locale = $locale ?: app()->getLocale();
        $priority = [$locale, 'vi', 'en', 'ko', 'ja', 'zh'];
        $priority = array_values(array_unique($priority));

        foreach ($priority as $key) {
            $value = $data[$key] ?? null;

            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return $fallback;
    }
}

if (!function_exists('localizedModelField')) {
    function localizedModelField($model, string $field, ?string $locale = null): string
    {
        if (!$model) {
            return '';
        }

        $locale = $locale ?: app()->getLocale();
        $priority = [$locale, 'vi', 'en', 'ko', 'ja', 'zh'];
        $priority = array_values(array_unique($priority));

        foreach ($priority as $lang) {
            $attribute = $lang === 'vi' ? $field : $field . '_' . $lang;
            $value = $model->{$attribute} ?? null;

            if ($value !== null && $value !== '') {
                return (string) $value;
            }
        }

        return (string) ($model->{$field} ?? '');
    }
}

if (!function_exists('notificationText')) {
    function notificationText($notification, string $key, ?string $default = ''): string
    {
        $data = $notification->data ?? [];
        $translations = $data[$key . '_translations'] ?? null;

        if (is_array($translations)) {
            return (string) localizedValue($translations, app()->getLocale(), $data[$key] ?? $default);
        }

        return (string) ($data[$key] ?? $default);
    }
}
