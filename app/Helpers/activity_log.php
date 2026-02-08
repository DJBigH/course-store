<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\ActiveLogs\src\Models\ActiveLog;

function activity_log(
    string $action,
    $subject = null,
    array $properties = [],
    string $logName = null,
    string $description = null
) {
    $user = Auth::guard('students')->user()
        ?? Auth::user();

    ActiveLog::create([
        'log_name'     => $logName,
        'action'       => $action,
        'subject_type' => $subject ? get_class($subject) : null,
        'subject_id'   => $subject->id ?? null,
        'causer_type'  => Auth::user()->name,
        'causer_id'    => $user->id ?? null,
        'properties'   => $properties,
        'description'  => $description,
        'ip'           => request()->ip(),
        'user_agent'   => request()->userAgent(),
    ]);
}

if (!function_exists('presentLogProperties')) {
    function presentLogProperties($log)
    {
        $p = $log->properties ?? [];

        // Map tên field -> tiếng Việt (rất quan trọng)
        $fieldLabels = [
            'name'        => 'Tên',
            'price'       => 'Giá',
            'sale_price'  => 'Giá khuyến mãi',
            'status'      => 'Trạng thái',
            'description' => 'Mô tả',
            'thumbnail'   => 'Ảnh đại diện',
            'code' => 'Mã khóa học',
            'view' => 'Lượt xem',
            'is_document' => 'Tài liệu',
            'created_at' => 'Thời gian tạo',
            'updated_at' => 'Thời gian cập nhập',
            'durations' => 'Thời lượng',
            'position' => 'Thứ tự',
            'document' => 'Tài liệu'

        ];

        // === UPDATE ===
        if ($log->action === 'update' && isset($p['old'], $p['new'])) {
            $lines = [];

            foreach ($p['new'] as $key => $newValue) {
                $oldValue = $p['old'][$key] ?? null;

                if ($oldValue != $newValue) {
                    $label = $fieldLabels[$key] ?? ucfirst(str_replace('_', ' ', $key));

                    $lines[] = sprintf(
                        "- %s: %s → %s",
                        $label,
                        formatLogValue($oldValue, $key),
                        formatLogValue($newValue, $key)
                    );
                }
            }

            return !empty($lines)
                ? implode("\n", $lines)
                : 'Không có thay đổi dữ liệu.';
        }

        // === CREATE ===
        if ($log->action === 'create') {
            return 'Tạo mới dữ liệu.';
        }

        // === DELETE ===
        if ($log->action === 'delete') {
            return 'Xóa dữ liệu.';
        }

        // === ASSIGN / OTHER ===
        if (isset($p['categories_new'])) {
            return 'Cập nhật danh mục: ' . implode(', ', $p['categories_new']);
        }

        // ===== COUPON - ASSIGN STUDENTS =====
        if ($log->action === 'assign_students') {
            $code = $p['coupon_code'] ?? ($p['coupon_name'] ?? '');
            $students = collect($p['students'] ?? []);
            $names = $students->pluck('name')->filter()->values();

            $short = $names->take(5)->implode(', ');
            $more = $names->count() > 5 ? '…' : '';

            return "Gán mã {$code} cho học viên: {$short}{$more}";
        }

        if ($log->action === 'revoke_students') {
            $code = $p['coupon_code'] ?? ($p['coupon_name'] ?? '');
            $students = collect($p['students'] ?? []);
            $names = $students->pluck('name')->filter()->values();

            $short = $names->take(5)->implode(', ');
            $more = $names->count() > 5 ? '…' : '';

            return "Hủy mã {$code} khỏi học viên: {$short}{$more}";
        }

        // Trường hợp bạn đang dùng sync_students (attached/detached)
        if ($log->action === 'sync_students') {
            $attached = $p['attached'] ?? [];
            $detached = $p['detached'] ?? [];

            $parts = [];
            if (!empty($attached)) $parts[] = 'Gán mới: ' . count($attached);
            if (!empty($detached)) $parts[] = 'Hủy gán: ' . count($detached);

            return $parts ? ('Cập nhật học viên áp dụng mã (' . implode(' | ', $parts) . ')') : 'Không có thay đổi.';
        }

        // ===== STUDENT SIDE =====
        if ($log->action === 'assigned_coupon') {
            $code = $p['coupon_code'] ?? ($p['coupon_name'] ?? '');
            return "Được gán mã: {$code}";
        }

        if ($log->action === 'revoked_coupon') {
            $code = $p['coupon_code'] ?? ($p['coupon_name'] ?? '');
            return "Bị hủy mã: {$code}";
        }

        // ===== CONTACT =====
        if ($log->action === 'accept') {
            // bạn đang log old/new status
            $old = $p['old']['status'] ?? null;
            $new = $p['new']['status'] ?? null;

            $oldText = ($old === 1 || $old === '1') ? 'Đã tiếp nhận' : 'Chờ xử lý';
            $newText = ($new === 1 || $new === '1') ? 'Đã tiếp nhận' : 'Chờ xử lý';

            // nếu không có old/new thì fallback
            if ($old === null && $new === null) {
                return 'Tiếp nhận liên hệ';
            }

            return "Tiếp nhận liên hệ: {$oldText} → {$newText}";
        }

        if ($log->action === 'view') {
            return 'Xem chi tiết liên hệ';
        }

        // ===== LESSON =====
        if (
            $log->action === 'update'
            && isset($p['old']['is_trial'], $p['new']['is_trial'])
            && $p['old']['is_trial'] != $p['new']['is_trial']
        ) {
            $old = $p['old']['is_trial'];
            $new = $p['new']['is_trial'];

            $oldText = ((int)$old === 1) ? 'Có học thử' : 'Không có học thử';
            $newText = ((int)$new === 1) ? 'Có học thử' : 'Không có học thử';

            return "Học thử: {$oldText} → {$newText}";
        }


        // ===== SETTINGS =====
        if ($log->action === 'update_settings' && isset($p['old'], $p['new'])) {

            // 1) Nhóm field
            $bannerFields = ['logo', 'banner_full', 'banner_slider', 'banner_right'];
            $socialFields = ['facebook', 'instagram', 'youtube', 'tiktok'];
            $showDetailFields = ['site_name', 'email', 'phone', 'address']; // hiện old → new

            // 2) Label
            $labels = [
                'site_name' => 'Tên website',
                'email'     => 'Email',
                'phone'     => 'Số điện thoại',
                'address'   => 'Địa chỉ',

                'facebook'  => 'Facebook',
                'instagram' => 'Instagram',
                'youtube'   => 'Youtube',
                'tiktok'    => 'TikTok',

                'logo'         => 'Logo',
                'banner_slider' => 'Banner slider',
                'banner_right' => 'Banner bên phải',
                'banner_full'  => 'Banner full',
            ];

            $lines = [];

            foreach ($p['new'] as $key => $newValue) {
                $oldValue = $p['old'][$key] ?? null;

                // không đổi thì skip
                if ($oldValue == $newValue) continue;

                $label = $labels[$key] ?? ucfirst(str_replace('_', ' ', $key));

                // ✅ Banner: chỉ báo "đã thay đổi ảnh"
                if (in_array($key, $bannerFields)) {
                    $lines[] = "- {$label}: Đã thay đổi ảnh";
                    continue;
                }

                // ✅ MXH: chỉ báo "đã thay đổi ..."
                if (in_array($key, $socialFields)) {
                    $lines[] = "- Đã thay đổi {$label}";
                    continue;
                }

                // ✅ Thông tin chung: hiện rõ old → new
                if (in_array($key, $showDetailFields)) {
                    $lines[] = sprintf(
                        "- %s: %s → %s",
                        $label,
                        formatLogValue($oldValue, $key),
                        formatLogValue($newValue, $key)
                    );
                    continue;
                }

                // ✅ Field khác: fallback (nếu sau này có thêm key)
                $lines[] = sprintf(
                    "- %s: %s → %s",
                    $label,
                    formatLogValue($oldValue, $key),
                    formatLogValue($newValue, $key)
                );
            }

            return !empty($lines)
                ? implode("\n", $lines)
                : 'Không có thay đổi cấu hình.';
        }

        return 'Có thay đổi dữ liệu.';
    }
}

if (!function_exists('formatLogValue')) {
    function formatLogValue($value, string $field = null)
    {
        if (is_null($value)) return '—';

        // ===== STATUS =====
        if ($field === 'status') {
            return $value == 1 ? 'Đã ra mắt' : 'Chưa ra mắt';
        }

        // ===== IS_DOCUMENT =====
        if ($field === 'is_document') {
            return $value == 1 ? 'Có tài liệu' : 'Không có tài liệu';
        }

        if ($field === 'created_at' ||  $field === 'updated_at') {
            return Carbon::parse($value)
                ->timezone('Asia/Ho_Chi_Minh')
                ->format('j/n/Y H:i:s');
        }

        // ===== PRICE =====
        if (in_array($field, ['price', 'sale_price']) && is_numeric($value)) {
            return money($value) . 'đ';
        }

        // ===== ARRAY =====
        if (is_array($value)) {
            return implode(', ', $value);
        }

        return (string) $value;
    }
}

if (!function_exists('formatHtmlForLog')) {
    function formatHtmlForLog(?string $html, int $limit = 160): string
    {
        if (empty($html)) {
            return '—';
        }

        // 1. Bỏ HTML tag
        $text = strip_tags($html);

        // 2. Decode entity (&nbsp; &lt; ...)
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // 3. Gộp khoảng trắng
        $text = preg_replace('/\s+/u', ' ', trim($text));

        // 4. Cắt ngắn
        if (mb_strlen($text) > $limit) {
            $text = mb_substr($text, 0, $limit) . '…';
        }

        return $text;
    }
}

if (!function_exists('subjectLabel')) {
    function subjectLabel($subjectType): string
    {
        if (!$subjectType) return '—';

        return config('activity-log.subjects')[$subjectType]
            ?? class_basename($subjectType);
    }
}
