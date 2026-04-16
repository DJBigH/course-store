<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Modules\Courses\src\Models\CourseComment;

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
        return vnd_to_currency($vnd, 'usd', $precision);
    }
}

if (!function_exists('usd_to_vnd')) {
    function usd_to_vnd(float $usd): int
    {
        $rate = max((float) config('currency.rates.usd', 25000), 1);
        return (int) round($usd * $rate);
    }
}

if (!function_exists('vnd_to_currency')) {
    function vnd_to_currency(int|float $vnd, string $currency, int $precision = 2): float|int
    {
        $settingKey = 'currency_rate_' . strtolower($currency);
        $configRate = config("currency.rates.{$currency}", 0);
        $rate = function_exists('setting')
            ? (float) setting($settingKey, $configRate)
            : (float) $configRate;

        if ($rate <= 0) {
            $rate = 1;
        }

        $value = $vnd / $rate;

        return $precision > 0
            ? round($value, $precision)
            : (int) round($value);
    }
}

if (!function_exists('format_money_value')) {
    function format_money_value(int|float $number, int $precision = 0): string
    {
        return number_format($number, $precision, '.', ',');
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
    return !empty($number) ? format_money_value($number) . ' ' . $currency : $freeText;
}

function moneyUS($number, $currency = '$', $freeText = 'Free')
{
    return !empty($number) ? $currency . format_money_value(vnd_to_usd($number), 2) : $freeText;
}

function moneyKR($number, $currency = '₩', $freeText = '무료')
{
    return !empty($number) ? $currency . format_money_value(vnd_to_currency($number, 'krw', 0)) : $freeText;
}

function moneyJP($number, $currency = '¥', $freeText = '無料')
{
    return !empty($number) ? $currency . format_money_value(vnd_to_currency($number, 'jpy', 0)) : $freeText;
}

function moneyCN($number, $currency = 'CN¥', $freeText = '免费')
{
    return !empty($number) ? $currency . format_money_value(vnd_to_currency($number, 'cny', 2), 2) : $freeText;
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


if (!function_exists('notificationData')) {
    function notificationData($notification, string $key, $default = null)
    {
        return $notification->data[$key] ?? $default;
    }
}

if (!function_exists('notificationSeverityClass')) {
    function notificationSeverityClass($notification, string $default = 'secondary'): string
    {
        $severity = (string) notificationData($notification, 'severity', $default);

        return match ($severity) {
            'success' => 'success',
            'warning' => 'warning',
            'danger', 'error', 'critical' => 'danger',
            'info', 'primary' => 'primary',
            default => $default,
        };
    }
}

if (!function_exists('notificationIconClass')) {
    function notificationIconClass($notification, string $default = 'fas fa-bell'): string
    {
        return (string) notificationData($notification, 'icon', $default);
    }
}

if (!function_exists('notificationTypeLabel')) {
    function notificationTypeLabel(?string $type): string
    {
        $type = trim((string) $type);

        if ($type === '') {
            return 'Thong bao he thong';
        }

        $map = [
            'App\\Notifications\\NewContactNotification' => 'Liên hệ mới',
            'App\\Notifications\\OrderPaidNotification' => 'Đơn hàng đã thanh toán',
            'App\\Notifications\\RegisterNotification' => 'Đăng ký mới',
            'App\\Notifications\\CouponStudentNotification' => 'Mã giảm giá',
            'App\\Notifications\\StudentNotification' => 'Học viên',
            'App\\Notifications\\GeminiHealthNotification' => 'Gemini health',
            'App\\Notifications\\AdminSecurityAlertNotification' => 'Bảo mật admin',
        ];

        if (isset($map[$type])) {
            return $map[$type];
        }

        $basename = class_basename($type);
        $basename = preg_replace('/Notification$/', '', $basename);
        $label = trim((string) preg_replace('/(?<!^)([A-Z])/', ' $1', $basename));

        return $label !== '' ? $label : $type;
    }
}

if (!function_exists('normalizeVideoUrl')) {
    function normalizeVideoUrl($url): string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return '';
        }

        if (preg_match('~^https?://~i', $url)) {
            return $url;
        }

        if (preg_match('~^(?:www\.)?(?:youtu\.be|youtube\.com|vimeo\.com)(?:/|$)~i', $url)) {
            return 'https://' . ltrim($url, '/');
        }

        return $url;
    }
}

if (!function_exists('videoEmbedUrl')) {
    function videoEmbedUrl($url): ?string
    {
        $url = normalizeVideoUrl($url);

        if ($url === '' || !preg_match('~^https?://~i', $url)) {
            return null;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = (string) parse_url($url, PHP_URL_PATH);

        // Youtube
        if (str_contains($host, 'youtube.com') || str_contains($host, 'youtu.be')) {
            $id = null;

            if (str_contains($host, 'youtu.be')) {
                $id = trim($path, '/');
            } elseif (str_contains($path, '/embed/')) {
                $id = trim(str_replace('/embed/', '', $path), '/');
            } elseif (str_contains($path, '/shorts/')) {
                $id = trim(str_replace('/shorts/', '', $path), '/');
            } elseif (str_contains($path, '/v/')) {
                $id = trim(str_replace('/v/', '', $path), '/');
            } elseif (str_contains($path, '/watch')) {
                $query = (string) parse_url($url, PHP_URL_QUERY);
                parse_str($query, $params);
                $id = $params['v'] ?? null;
            } else {
                // Trường hợp m.youtube.com hoặc các link khác không có /watch
                $query = (string) parse_url($url, PHP_URL_QUERY);
                parse_str($query, $params);
                $id = $params['v'] ?? null;
            }

            // Clean up ID if there are extra segments
            if ($id && str_contains($id, '/')) {
                $id = explode('/', $id)[0];
            }

            return $id ? "https://www.youtube.com/embed/{$id}" : null;
        }

        if (str_contains($host, 'vimeo.com')) {
            $id = trim($path, '/');

            if (preg_match('~(\d+)$~', $id, $matches)) {
                return "https://player.vimeo.com/video/{$matches[1]}";
            }
        }

        return null;
    }
}

if (!function_exists('videoPlaybackMeta')) {
    function videoPlaybackMeta($url, ?string $locale = null): array
    {
        $url = normalizeVideoUrl($url);

        if ($url === '') {
            return [
                'type' => 'none',
                'url' => null,
            ];
        }

        $embedUrl = videoEmbedUrl($url);

        if ($embedUrl) {
            return [
                'type' => 'embed',
                'url' => $embedUrl,
            ];
        }

        if (preg_match('~^https?://~i', $url)) {
            return [
                'type' => 'file',
                'url' => $url,
            ];
        }

        return [
            'type' => 'file',
            'url' => route('courses.data.stream', ['locale' => $locale ?: app()->getLocale()]) . '?video=' . urlencode(ltrim($url, '/')),
        ];
    }
}

if (!function_exists('parseIso8601DurationToSeconds')) {
    function parseIso8601DurationToSeconds(?string $value): int
    {
        $value = trim((string) $value);

        if ($value === '' || !preg_match('/^P/i', $value)) {
            return 0;
        }

        try {
            $interval = new DateInterval($value);
        } catch (Throwable $exception) {
            return 0;
        }

        return ($interval->d * 86400)
            + ($interval->h * 3600)
            + ($interval->i * 60)
            + $interval->s;
    }
}

if (!function_exists('fetchExternalVideoDuration')) {
    function fetchExternalVideoDuration($url): int
    {
        $normalizedUrl = normalizeVideoUrl($url);

        if ($normalizedUrl === '' || !preg_match('~^https?://~i', $normalizedUrl)) {
            return 0;
        }

        $host = strtolower((string) parse_url($normalizedUrl, PHP_URL_HOST));

        try {
            if (str_contains($host, 'vimeo.com')) {
                $response = Http::timeout(5)
                    ->acceptJson()
                    ->get('https://vimeo.com/api/oembed.json', ['url' => $normalizedUrl]);

                if ($response->successful()) {
                    return (int) ($response->json('duration') ?? 0);
                }
            }

            if (str_contains($host, 'youtube.com') || str_contains($host, 'youtu.be')) {
                $watchUrl = $normalizedUrl;

                if (preg_match('~youtube\.com/embed/([^?&/]+)~i', $normalizedUrl, $matches)) {
                    $watchUrl = 'https://www.youtube.com/watch?v=' . $matches[1];
                } elseif (preg_match('~youtube\.com/shorts/([^?&/]+)~i', $normalizedUrl, $matches)) {
                    $watchUrl = 'https://www.youtube.com/watch?v=' . $matches[1];
                } elseif (preg_match('~youtu\.be/([^?&/]+)~i', $normalizedUrl, $matches)) {
                    $watchUrl = 'https://www.youtube.com/watch?v=' . $matches[1];
                }

                $response = Http::timeout(5)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0',
                    ])
                    ->get($watchUrl);

                if ($response->successful()) {
                    $body = (string) $response->body();

                    if (preg_match('/"approxDurationMs":"(\d+)"/', $body, $matches)) {
                        return (int) round(((int) $matches[1]) / 1000);
                    }

                    if (preg_match('/"lengthSeconds":"(\d+)"/', $body, $matches)) {
                        return (int) $matches[1];
                    }

                    if (preg_match('/itemprop="duration"\s+content="([^"]+)"/i', $body, $matches)) {
                        return parseIso8601DurationToSeconds($matches[1]);
                    }
                }
            }
        } catch (Throwable $exception) {
            return 0;
        }

        return 0;
    }
}

if (!function_exists('externalVideoDuration')) {
    function externalVideoDuration($url): int
    {
        $normalizedUrl = normalizeVideoUrl($url);

        if ($normalizedUrl === '' || !preg_match('~^https?://~i', $normalizedUrl)) {
            return 0;
        }

        return Cache::remember(
            'external_video_duration_' . md5($normalizedUrl),
            now()->addHours(12),
            static fn() => fetchExternalVideoDuration($normalizedUrl)
        );
    }
}

if (!function_exists('courseCommentAvatar')) {
    function courseCommentAvatar(int|string|null $seed = null): string
    {
        $avatars = [
            'clients/assets/avatar-1.svg',
            'clients/assets/avatar-2.svg',
            'clients/assets/avatar-3.svg',
        ];

        $index = abs((int) crc32((string) ($seed ?? '0'))) % count($avatars);

        return asset($avatars[$index]);
    }
}

if (!function_exists('teacherAvatarUrl')) {
    function teacherAvatarUrl($teacher = null): string
    {
        $default = asset('resources/assets/teacher.png');

        if (!$teacher) {
            return $default;
        }

        $image = trim((string) ($teacher->image ?? ''));

        if ($image === '') {
            return $default;
        }

        if (\Illuminate\Support\Str::startsWith($image, ['http://', 'https://'])) {
            return $image;
        }

        return asset(ltrim($image, '/'));
    }
}

if (!function_exists('courseCommentModeration')) {
    function courseCommentModeration(string $content): array
    {
        $normalized = mb_strtolower(trim($content), 'UTF-8');
        $blockedTerms = [
            'dm',
            'đm',
            'địt',
            'dit me',
            'ditme',
            'clm',
            'vcl',
            'vl',
            'fuck',
            'fucking',
            'shit',
            'bitch',
            'asshole',
            'idiot',
            'ngu',
            'lon',
            'cc',
        ];

        $matched = [];

        foreach ($blockedTerms as $term) {
            if ($term !== '' && str_contains($normalized, $term)) {
                $matched[] = $term;
            }
        }

        $matched = array_values(array_unique($matched));

        return [
            'is_flagged' => !empty($matched),
            'matched_terms' => $matched,
        ];
    }
}

if (!function_exists('courseCommentThreads')) {
    function courseCommentThreads($courseId, bool $includeHidden = false)
    {
        return CourseComment::query()
            ->where('course_id', $courseId)
            ->roots()
            ->when(!$includeHidden, fn($query) => $query->visible())
            ->with([
                'student',
                'admin',
                'replies' => function ($query) use ($includeHidden) {
                    $query->with(['student', 'admin'])
                        ->when(!$includeHidden, fn($replyQuery) => $replyQuery->visible())
                        ->oldest();
                },
            ])
            ->latest()
            ->get();
    }
}
