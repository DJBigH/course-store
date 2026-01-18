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
    return request()->is(trim(route($name, [], false), '/'));
}

function money($number, $currency = 'đ')
{
    return !empty($number) ? number_format($number) . ' ' . $currency : "Miễn phí";
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
