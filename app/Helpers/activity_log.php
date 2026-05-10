<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\Categories\src\Models\Category;
use Modules\ActiveLogs\src\Models\ActiveLog;
use Modules\Courses\src\Models\Courses;
use Modules\Document\src\Models\Document;
use Modules\Orders\src\Models\Order;
use Modules\Students\src\Models\Coupons;
use Modules\Students\src\Models\Student;
use Modules\Teacher\src\Models\Teacher;
use Modules\User\src\Models\User;
use Modules\Video\src\Models\Video;

function activity_log(
    string $action,
    $subject = null,
    array $properties = [],
    string $logName = null,
    string $description = null
) {
    $student = Auth::guard('students')->user();
    $admin = Auth::user();
    $user = $student ?? $admin;
    if ($student) {
        $causerLabel = buildLogCauserLabel($student, 'student');
    } elseif ($admin) {
        $causerLabel = buildLogCauserLabel($admin, 'admin');
    } else {
        $causerLabel = buildLogCauserLabel(null);
    }

    $log = ActiveLog::create([
        'log_name'     => $logName,
        'action'       => $action,
        'subject_type' => $subject ? get_class($subject) : null,
        'subject_id'   => $subject->id ?? null,
        'causer_type'  => $causerLabel,
        'causer_id'    => $user->id ?? null,
        'properties'   => $properties,
        'description'  => $description,
        'ip'           => request()->ip(),
        'user_agent'   => request()->userAgent(),
    ]);

    // Gửi log admin qua Telegram
    if ($admin) {
        try {
            $isEnabled = \Modules\Settings\src\Models\Setting::where('key', 'telegram_bot_enabled')->value('value');
            $botToken = config('services.telegram.bot_token');
            $chatId = config('services.telegram.chat_id');

            if ($isEnabled && $botToken && $chatId) {
                $actionLabel = logActionLabel($action);
                
                $message = "🔔 *[Admin Log]*\n";
                $message .= "👤 *Người thực hiện:* " . str_replace(['_', '*', '`'], ' ', $causerLabel) . "\n";
                $message .= "🎯 *Hành động:* {$actionLabel}\n";
                if ($description) {
                    $message .= "📝 *Mô tả:* " . str_replace(['_', '*', '`'], ' ', $description) . "\n";
                }

                $propertiesText = presentLogProperties($log);
                if ($propertiesText && $propertiesText !== 'Không có chi tiết thay đổi.') {
                    $cleanProps = str_replace(['_', '*', '`'], ' ', $propertiesText);
                    $message .= "🔍 *Chi tiết:*\n{$cleanProps}";
                }

                \App\Jobs\SendTelegramAlertJob::dispatch($message);
            }
        } catch (\Throwable $e) {
            // Đảm bảo không làm sập tiến trình chính
        }
    }
}

if (!function_exists('buildLogCauserLabel')) {
    function buildLogCauserLabel($user = null, ?string $type = null): string
    {
        if (!$user) {
            return 'Hệ thống';
        }

        if ($type === 'student' || $user instanceof Student) {
            $isTeacher = $user->teacher && $user->teacher->status === 'active';
            $roleSuffix = $isTeacher ? '(giang-vien)' : '(hoc-vien)';
            return trim(($user->name ?? 'Không rõ') . $roleSuffix);
        }

        if ($type === 'admin' || $user instanceof User) {
            if ($user instanceof User && !$user->relationLoaded('group')) {
                $user->loadMissing('group');
            }

            $role = $user->group->slug ?? $user->group->name ?? 'admin';
            $role = str_replace('_', '-', trim((string) $role));

            return trim(($user->name ?? 'Không rõ') . '(' . $role . ')');
        }

        return (string) ($user->name ?? 'Hệ thống');
    }
}

if (!function_exists('logCauserDisplay')) {
    function logCauserDisplay($log): string
    {
        if (!$log) {
            return 'Hệ thống';
        }

        if (!$log->causer_id) {
            return $log->causer_type ?: 'Hệ thống';
        }

        $storedLabel = (string) ($log->causer_type ?? '');
        $normalizedLabel = mb_strtolower($storedLabel, 'UTF-8');

        if (
            str_contains($normalizedLabel, 'hoc-vien') ||
            str_contains($normalizedLabel, 'giang-vien') ||
            str_contains($normalizedLabel, '(student)') ||
            str_contains($normalizedLabel, '(teacher)') ||
            str_contains($normalizedLabel, '(hoc-vien)') ||
            str_contains($normalizedLabel, '(giang-vien)')
        ) {
            $student = Student::query()->find($log->causer_id);

            if ($student) {
                return buildLogCauserLabel($student, 'student');
            }
        }

        $admin = User::query()->with('group')->find($log->causer_id);

        if ($admin) {
            return buildLogCauserLabel($admin, 'admin');
        }

        return $log->causer_type ?: 'Hệ thống';
    }
}

if (!function_exists('presentLogProperties')) {
    function presentLogProperties($log)
    {
        $rows = logDetailRows($log);

        if (empty($rows)) {
            return 'Không có chi tiết thay đổi.';
        }

        return collect($rows)
            ->map(function ($row) {
                if (array_key_exists('old', $row) || array_key_exists('new', $row)) {
                    return sprintf(
                        "- %s: %s -> %s",
                        $row['label'],
                        $row['old'] ?? '—',
                        $row['new'] ?? '—'
                    );
                }

                return sprintf(
                    "- %s: %s",
                    $row['label'],
                    $row['value'] ?? '—'
                );
            })
            ->implode("\n");
    }
}

if (!function_exists('logFieldLabels')) {
    function logFieldLabels(): array
    {
        return [
            'name' => 'Tên',
            'name_en' => 'Tên (EN)',
            'name_ko' => 'Tên (KO)',
            'name_ja' => 'Tên (JA)',
            'name_zh' => 'Tên (ZH)',
            'slug' => 'Slug',
            'slug_en' => 'Slug (EN)',
            'slug_ko' => 'Slug (KO)',
            'slug_ja' => 'Slug (JA)',
            'slug_zh' => 'Slug (ZH)',
            'price' => 'Giá',
            'sale_price' => 'Giá khuyến mãi',
            'status' => 'Trạng thái',
            'description' => 'Mô tả',
            'description_en' => 'Mô tả (EN)',
            'description_ko' => 'Mô tả (KO)',
            'description_ja' => 'Mô tả (JA)',
            'description_zh' => 'Mô tả (ZH)',
            'detail' => 'Nội dung',
            'detail_en' => 'Nội dung (EN)',
            'detail_ko' => 'Nội dung (KO)',
            'detail_ja' => 'Nội dung (JA)',
            'detail_zh' => 'Nội dung (ZH)',
            'supports' => 'Hỗ trợ',
            'supports_en' => 'Hỗ trợ (EN)',
            'supports_ko' => 'Hỗ trợ (KO)',
            'supports_ja' => 'Hỗ trợ (JA)',
            'supports_zh' => 'Hỗ trợ (ZH)',
            'thumbnail' => 'Ảnh đại diện',
            'image' => 'Hình ảnh',
            'code' => 'Mã',
            'view' => 'Lượt xem',
            'is_document' => 'Tài liệu',
            'is_trial' => 'Học thử',
            'created_at' => 'Thời gian tạo',
            'updated_at' => 'Thời gian cập nhật',
            'durations' => 'Thời lượng',
            'position' => 'Thứ tự',
            'document' => 'Tài liệu',
            'video' => 'Video',
            'site_name' => 'Tên website',
            'email' => 'Email',
            'phone' => 'Số điện thoại',
            'address' => 'Địa chỉ',
            'facebook' => 'Facebook',
            'instagram' => 'Instagram',
            'youtube' => 'Youtube',
            'tiktok' => 'TikTok',
            'logo' => 'Logo',
            'banner_slider' => 'Banner slider',
            'banner_right' => 'Banner bên phải',
            'banner_full' => 'Banner full',
            'global_notice_enabled' => 'Bật thông báo tổng',
            'global_notice_title' => 'Tiêu đề thông báo tổng',
            'global_notice_content' => 'Nội dung thông báo tổng',
            'global_notice_link_label' => 'Nút thông báo tổng',
            'global_notice_link_url' => 'Liên kết thông báo tổng',
            'popup_notice_enabled' => 'Bật popup thông báo',
            'popup_notice_title' => 'Tiêu đề popup',
            'popup_notice_content' => 'Nội dung popup',
            'popup_notice_link_label' => 'Nút popup',
            'popup_notice_link_url' => 'Liên kết popup',
            'popup_notice_snooze_minutes' => 'Số phút tắt popup tạm thời',
            'mail_enabled' => 'Gửi mail',
            'chatbot_widget_enabled' => 'Chatbot widget',
            'chatbot_enabled' => 'Chatbot',
            'two_factor_email_enabled' => 'Xác thực 2 lớp email',
            'is_locked' => 'Khóa tài khoản',
            'is_admin' => 'Quyền admin',
            'per_student_once' => 'Dùng 1 lần / học viên',
            'is_active' => 'Kích hoạt',
            'teacher_id' => 'Giảng viên',
            'parent_id' => 'Mục cha',
            'exp' => 'Kinh nghiệm',
            'course_id' => 'Khóa học',
            'student_id' => 'Học viên',
            'coupon_id' => 'Mã giảm giá',
            'order_id' => 'Đơn hàng',
            'document_id' => 'Tài liệu',
            'video_id' => 'Video',
            // Finances
            'amount' => 'Số tiền',
            'bank_name' => 'Ngân hàng',
            'bank_account_name' => 'Chủ tài khoản',
            'bank_account_number' => 'Số tài khoản',
            'admin_note' => 'Ghi chú admin',
            // Promotions
            'recipient_count' => 'Số người nhận',
            'audience_type' => 'Loại đối tượng',
            'title' => 'Tiêu đề',
            // Comments
            'is_visible' => 'Hiển thị',
            // Packages
            'commission_rate' => 'Tỷ lệ hoa hồng (%)',
            'billing_cycle' => 'Chu kỳ thanh toán',
            'price' => 'Giá gói',
        ];
    }
}

if (!function_exists('logFieldLabel')) {
    function logFieldLabel(string $key): string
    {
        return logFieldLabels()[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }
}

if (!function_exists('logActionLabel')) {
    function logActionLabel(?string $action): string
    {
        $labels = [
            'create' => 'Tạo mới',
            'update' => 'Cập nhật',
            'delete' => 'Xóa',
            'view' => 'Xem',
            'accept' => 'Tiếp nhận',
            'assign_students' => 'Gán học viên',
            'revoke_students' => 'Hủy gán học viên',
            'sync_students' => 'Đồng bộ học viên',
            'assigned_coupon' => 'Được gán mã',
            'revoked_coupon' => 'Bị hủy mã',
            'update_settings' => 'Cập nhật cấu hình',
            'send_promotion' => 'Gửi khuyến mãi',
            'toggle_visibility' => 'Ẩn/hiện bình luận',
            'payout_status_update' => 'Cập nhật trạng thái rút tiền',
            'create_payout' => 'Tạo yêu cầu rút tiền',
            'account_change_update' => 'Cập nhật yêu cầu đổi tài khoản',
        ];

        return $labels[$action] ?? ($action ?: 'Khác');
    }
}

if (!function_exists('logActionBadgeClass')) {
    function logActionBadgeClass(?string $action): string
    {
        return match ($action) {
            'create', 'assign_students', 'assigned_coupon', 'accept', 'send_promotion', 'create_payout' => 'success',
            'update', 'update_settings', 'sync_students', 'payout_status_update', 'account_change_update' => 'primary',
            'delete', 'revoke_students', 'revoked_coupon' => 'danger',
            'view' => 'secondary',
            'toggle_visibility' => 'warning',
            default => 'dark',
        };
    }
}

if (!function_exists('logDetailRows')) {
    function logDetailRows($log): array
    {
        $p = $log->properties ?? [];
        $rows = [];
        $hiddenFields = ['created_at', 'updated_at'];

        if ($log->action === 'update_settings' && isset($p['old'], $p['new'])) {
            $bannerFields = ['logo', 'banner_full', 'banner_slider', 'banner_right'];
            $socialFields = ['facebook', 'instagram', 'youtube', 'tiktok'];

            foreach (array_keys(array_merge($p['old'], $p['new'])) as $key) {
                if (in_array($key, $hiddenFields, true)) {
                    continue;
                }

                $oldValue = $p['old'][$key] ?? null;
                $newValue = $p['new'][$key] ?? null;

                if ($oldValue == $newValue) {
                    continue;
                }

                if (in_array($key, $bannerFields, true)) {
                    $rows[] = [
                        'label' => logFieldLabel($key),
                        'value' => 'Đã thay đổi ảnh',
                    ];
                    continue;
                }

                if (in_array($key, $socialFields, true)) {
                    $rows[] = [
                        'label' => logFieldLabel($key),
                        'value' => 'Đã thay đổi liên kết',
                    ];
                    continue;
                }

                $rows[] = [
                    'label' => logFieldLabel($key),
                    'old' => formatLogValue($oldValue, $key),
                    'new' => formatLogValue($newValue, $key),
                ];
            }

            return $rows;
        }

        if (isset($p['old'], $p['new']) && is_array($p['old']) && is_array($p['new'])) {
            $keys = array_unique(array_merge(array_keys($p['old']), array_keys($p['new'])));

            foreach ($keys as $key) {
                if (in_array($key, $hiddenFields, true)) {
                    continue;
                }

                $oldValue = $p['old'][$key] ?? null;
                $newValue = $p['new'][$key] ?? null;

                if ($oldValue == $newValue) {
                    continue;
                }

                $rows[] = [
                    'label' => logFieldLabel($key),
                    'old' => formatLogValue($oldValue, $key),
                    'new' => formatLogValue($newValue, $key),
                ];
            }

            if (!empty($rows)) {
                return $rows;
            }
        }

        if (isset($p['data']) && is_array($p['data'])) {
            foreach ($p['data'] as $key => $value) {
                if (in_array($key, array_merge(['password'], $hiddenFields), true) || is_array($value) || is_object($value)) {
                    continue;
                }

                $rows[] = [
                    'label' => logFieldLabel($key),
                    'value' => formatLogValue($value, $key),
                ];
            }

            if (!empty($rows)) {
                return $rows;
            }
        }

        if (isset($p['categories_old']) || isset($p['categories_new'])) {
            $rows[] = [
                'label' => 'Chuyên mục cũ',
                'value' => formatLogValue($p['categories_old'] ?? [], 'categories_old'),
            ];
            $rows[] = [
                'label' => 'Chuyên mục mới',
                'value' => formatLogValue($p['categories_new'] ?? [], 'categories_new'),
            ];

            return $rows;
        }

        if (isset($p['students']) && is_array($p['students'])) {
            $studentNames = collect($p['students'])->pluck('name')->filter()->implode(', ');
            $rows[] = [
                'label' => 'Học viên',
                'value' => $studentNames ?: '—',
            ];

            return $rows;
        }

        foreach ($p as $key => $value) {
            if (in_array((string) $key, $hiddenFields, true)) {
                continue;
            }

            if (is_array($value) || is_object($value)) {
                continue;
            }

            $rows[] = [
                'label' => logFieldLabel((string) $key),
                'value' => formatLogValue($value, (string) $key),
            ];
        }

        return $rows;
    }
}

if (!function_exists('logSummaryText')) {
    function logSummaryText($log): string
    {
        $rows = logDetailRows($log);

        if (empty($rows)) {
            return $log->description ?: 'Không có chi tiết.';
        }

        $first = $rows[0];

        if (array_key_exists('old', $first) || array_key_exists('new', $first)) {
            return sprintf(
                '%s: %s -> %s',
                $first['label'],
                $first['old'] ?? '—',
                $first['new'] ?? '—'
            );
        }

        return sprintf('%s: %s', $first['label'], $first['value'] ?? '—');
    }
}

if (!function_exists('logDiffCount')) {
    function logDiffCount($log): int
    {
        return count(logDetailRows($log));
    }
}

if (!function_exists('formatLogValue')) {
    function formatLogValue($value, string $field = null)
    {
        if (is_null($value)) return '—';

        if (is_bool($value)) {
            return $value ? 'Có' : 'Không';
        }

        // ===== STATUS =====
        if ($field === 'status') {
            return $value == 1 ? 'Đã ra mắt' : 'Chưa ra mắt';
        }

        // ===== IS_DOCUMENT =====
        if ($field === 'is_document') {
            return $value == 1 ? 'Có tài liệu' : 'Không có tài liệu';
        }

        if ($field === 'is_trial') {
            return $value == 1 ? 'Có học thử' : 'Không học thử';
        }

        if ($field === 'parent_id') {
            if (empty($value) || (int) $value === 0) {
                return 'Không có';
            }

            $category = Category::query()->find((int) $value);

            if ($category) {
                return $category->name . ' (#' . $category->id . ')';
            }

            return 'Chuyên mục #' . (int) $value;
        }

        if ($field === 'teacher_id') {
            if (empty($value) || (int) $value === 0) {
                return 'Không có';
            }

            $teacher = Teacher::query()->find((int) $value);

            if ($teacher) {
                return $teacher->name . ' (#' . $teacher->id . ')';
            }

            return 'Giảng viên #' . (int) $value;
        }

        if ($field === 'course_id') {
            if (empty($value) || (int) $value === 0) {
                return 'Không có';
            }

            $course = Courses::withoutGlobalScopes()->find((int) $value);

            if ($course) {
                $label = $course->name ?: $course->code ?: ('Khóa học #' . $course->id);

                return $label . ' (#' . $course->id . ')';
            }

            return 'Khóa học #' . (int) $value;
        }

        if ($field === 'student_id') {
            if (empty($value) || (int) $value === 0) {
                return 'Không có';
            }

            $student = Student::query()->find((int) $value);

            if ($student) {
                return $student->name . ' (#' . $student->id . ')';
            }

            return 'Học viên #' . (int) $value;
        }

        if ($field === 'coupon_id') {
            if (empty($value) || (int) $value === 0) {
                return 'Không có';
            }

            $coupon = Coupons::query()->find((int) $value);

            if ($coupon) {
                return ($coupon->code ?: 'Mã giảm giá') . ' (#' . $coupon->id . ')';
            }

            return 'Mã giảm giá #' . (int) $value;
        }

        if ($field === 'order_id') {
            if (empty($value) || (int) $value === 0) {
                return 'Không có';
            }

            $order = Order::query()->find((int) $value);

            if ($order) {
                return ($order->code ?: 'Đơn hàng') . ' (#' . $order->id . ')';
            }

            return 'Đơn hàng #' . (int) $value;
        }

        if ($field === 'document_id') {
            if (empty($value) || (int) $value === 0) {
                return 'Không có';
            }

            $document = Document::query()->find((int) $value);

            if ($document) {
                return ($document->name ?: 'Tài liệu') . ' (#' . $document->id . ')';
            }

            return 'Tài liệu #' . (int) $value;
        }

        if ($field === 'video_id') {
            if (empty($value) || (int) $value === 0) {
                return 'Không có';
            }

            $video = Video::query()->find((int) $value);

            if ($video) {
                return ($video->name ?: 'Video') . ' (#' . $video->id . ')';
            }

            return 'Video #' . (int) $value;
        }

        if ($field === 'created_at' ||  $field === 'updated_at') {
            return Carbon::parse($value)
                ->timezone('Asia/Ho_Chi_Minh')
                ->format('j/n/Y H:i:s');
        }

        if (in_array($field, ['logo', 'banner_slider', 'banner_right', 'banner_full'], true)) {
            return formatLogFileValue($value);
        }

        // ===== PRICE =====
        if (in_array($field, ['price', 'sale_price']) && is_numeric($value)) {
            return money($value) . 'đ';
        }

        if (in_array($field, [
            'description',
            'description_en',
            'description_ko',
            'description_ja',
            'description_zh',
            'detail',
            'detail_en',
            'detail_ko',
            'detail_ja',
            'detail_zh',
            'supports',
            'supports_en',
            'supports_ko',
            'supports_ja',
            'supports_zh',
            'global_notice_content',
            'popup_notice_content',
        ], true)) {
            return formatHtmlForLog((string) $value, 220);
        }

        // ===== ARRAY =====
        if (is_array($value)) {
            return implode(', ', $value);
        }

        if (is_string($value)) {
            $trimmed = trim($value);

            if ($trimmed === '') {
                return '—';
            }
        }

        return (string) $value;
    }
}

if (!function_exists('formatLogFileValue')) {
    function formatLogFileValue($value): string
    {
        if (is_null($value)) {
            return '—';
        }

        if (is_string($value)) {
            $trimmed = trim($value);

            if ($trimmed === '') {
                return '—';
            }

            $decoded = json_decode($trimmed, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $value = $decoded;
            } else {
                return basename(str_replace('\\', '/', $trimmed));
            }
        }

        if (is_array($value)) {
            $files = collect($value)
                ->flatten()
                ->filter(fn($item) => filled($item))
                ->map(function ($item) {
                    if (!is_string($item)) {
                        return null;
                    }

                    return basename(str_replace('\\', '/', $item));
                })
                ->filter()
                ->values()
                ->all();

            return empty($files) ? '—' : implode(', ', $files);
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
