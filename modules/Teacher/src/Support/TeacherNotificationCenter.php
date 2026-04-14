<?php

namespace Modules\Teacher\src\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\Courses\src\Models\CourseComment;
use Modules\Orders\src\Models\OrderDetail;
use Modules\Students\src\Models\Coupons;
use Modules\Students\src\Models\Student;
use Modules\Teacher\src\Models\Teacher;
use Modules\Teacher\src\Models\TeacherAnnouncement;
use Modules\Teacher\src\Models\TeacherNotificationRead;
use Modules\Teacher\src\Models\TeacherPackage;

class TeacherNotificationCenter
{
    public function summary(?Student $student, int $limit = 20): array
    {
        if (!$student) {
            return [
                'count' => 0,
                'items' => collect(),
            ];
        }

        $teacher = Teacher::query()
            ->with(['application.package'])
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->first();

        $databaseItems = $this->buildDatabaseNotifications($student);

        if (!$teacher) {
            return [
                'count' => (int) $student->unreadNotifications()->count(),
                'items' => $databaseItems->take($limit)->values(),
            ];
        }

        $syntheticItems = collect()
            ->merge($this->buildAnnouncementNotifications($teacher))
            ->merge($this->buildCommentNotifications($teacher))
            ->merge($this->buildSaleNotifications($teacher))
            ->merge($this->buildCouponNotifications($teacher))
            ->merge($this->buildPackageNotifications($teacher));

        $syntheticItems = $this->hydrateSyntheticReadState($student, $syntheticItems);

        $items = $syntheticItems
            ->merge($databaseItems)
            ->sortByDesc(fn (array $item) => $item['created_at']?->timestamp ?? 0)
            ->values();

        return [
            'count' => $syntheticItems->where('is_unread', true)->count() + (int) $student->unreadNotifications()->count(),
            'items' => $items->take($limit)->values(),
        ];
    }

    private function buildDatabaseNotifications(Student $student): Collection
    {
        return $student->notifications()
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => 'database-' . $notification->id,
                    'title' => notificationText($notification, 'title', notificationTypeLabel($notification->type)),
                    'message' => notificationText($notification, 'message', ''),
                    'type_label' => __('teacher::dashboard.notifications.types.system'),
                    'icon' => notificationIconClass($notification),
                    'severity' => notificationSeverityClass($notification),
                    'url' => route('students.notifications.read', [
                        'locale' => app()->getLocale(),
                        'id' => $notification->id,
                    ]),
                    'created_at' => $notification->created_at,
                    'is_unread' => is_null($notification->read_at),
                ];
            });
    }

    private function buildCommentNotifications(Teacher $teacher): Collection
    {
        $teacherStudentId = (int) ($teacher->student_id ?? 0);

        return CourseComment::query()
            ->with(['course', 'student'])
            ->whereNull('parent_id')
            ->whereNotNull('student_id')
            ->where('student_id', '<>', $teacherStudentId)
            ->whereHas('course', function ($query) use ($teacher) {
                $query->withoutGlobalScopes()->withTrashed()->where('teacher_id', $teacher->id);
            })
            ->whereDoesntHave('replies', function ($query) use ($teacherStudentId) {
                $query->where('student_id', $teacherStudentId);
            })
            ->latest('created_at')
            ->take(4)
            ->get()
            ->map(function (CourseComment $comment) {
                $courseName = localizedModelField($comment->course, 'name', app()->getLocale()) ?: __('teacher::dashboard.common.unknown_course');

                return [
                    'id' => 'comment-' . $comment->id,
                    'title' => __('teacher::dashboard.notifications.types.comment_title'),
                    'message' => __('teacher::dashboard.notifications.comment_message', [
                        'student' => $comment->student?->name ?: $comment->author_name,
                        'course' => $courseName,
                    ]),
                    'type_label' => __('teacher::dashboard.notifications.types.comments'),
                    'icon' => 'fas fa-comments',
                    'severity' => 'primary',
                    'notification_key' => 'comment:' . $comment->id,
                    'target_url' => route('teacher.dashboard.comments', ['course_id' => $comment->course_id]),
                    'created_at' => $comment->created_at,
                ];
            });
    }

    private function buildSaleNotifications(Teacher $teacher): Collection
    {
        return OrderDetail::query()
            ->with(['courses', 'order.students'])
            ->whereHas('courses', function ($query) use ($teacher) {
                $query->withoutGlobalScopes()->withTrashed()->where('teacher_id', $teacher->id);
            })
            ->whereHas('order', function ($query) {
                $query->where('status_id', 2);
            })
            ->latest('id')
            ->take(4)
            ->get()
            ->map(function (OrderDetail $detail) {
                $courseName = localizedModelField($detail->courses, 'name', app()->getLocale()) ?: __('teacher::dashboard.common.unknown_course');
                $studentName = $detail->order?->customer_name_display ?: ($detail->order?->students?->name ?: 'Học viên');

                return [
                    'id' => 'sale-' . $detail->id,
                    'title' => __('teacher::dashboard.notifications.types.sale_title'),
                    'message' => __('teacher::dashboard.notifications.sale_message', [
                        'student' => $studentName,
                        'course' => $courseName,
                    ]),
                    'type_label' => __('teacher::dashboard.notifications.types.sales'),
                    'icon' => 'fas fa-bag-shopping',
                    'severity' => 'success',
                    'notification_key' => 'sale:' . $detail->id,
                    'target_url' => $detail->order_id ? route('teacher.dashboard.orders.show', $detail->order_id) : route('teacher.dashboard.orders'),
                    'created_at' => $detail->order?->payment_complete_date ?: $detail->created_at,
                ];
            });
    }

    private function buildCouponNotifications(Teacher $teacher): Collection
    {
        if (!$teacher->packageHasFeature('can_manage_coupons')) {
            return collect();
        }

        return Coupons::query()
            ->withCount('usagescoupon')
            ->where('teacher_id', $teacher->id)
            ->latest('updated_at')
            ->get()
            ->filter(function (Coupons $coupon) {
                $remaining = $coupon->count ? max((int) $coupon->count - (int) $coupon->usagescoupon_count, 0) : null;
                $daysLeft = $coupon->end_date ? now()->startOfDay()->diffInDays($coupon->end_date->copy()->startOfDay(), false) : null;

                return ($daysLeft !== null && $daysLeft >= 0 && $daysLeft <= 7)
                    || ($remaining !== null && $remaining >= 0 && $remaining <= 3);
            })
            ->sortBy(function (Coupons $coupon) {
                $remaining = $coupon->count ? max((int) $coupon->count - (int) $coupon->usagescoupon_count, 0) : 999;
                $daysLeft = $coupon->end_date ? max(now()->startOfDay()->diffInDays($coupon->end_date->copy()->startOfDay(), false), 0) : 999;

                return min($remaining, $daysLeft);
            })
            ->take(4)
            ->values()
            ->map(function (Coupons $coupon) {
                $remaining = $coupon->count ? max((int) $coupon->count - (int) $coupon->usagescoupon_count, 0) : null;
                $daysLeft = $coupon->end_date ? now()->startOfDay()->diffInDays($coupon->end_date->copy()->startOfDay(), false) : null;
                $messageParts = [];

                if ($daysLeft !== null && $daysLeft >= 0) {
                    $messageParts[] = $daysLeft === 0
                        ? __('teacher::dashboard.notifications.coupon_expires_today', ['code' => $coupon->code])
                        : __('teacher::dashboard.notifications.coupon_expiring_message', [
                            'code' => $coupon->code,
                            'days' => $daysLeft,
                        ]);
                }

                if ($remaining !== null && $remaining <= 3) {
                    $messageParts[] = __('teacher::dashboard.notifications.coupon_remaining_message', ['count' => $remaining]);
                }

                return [
                    'id' => 'coupon-' . $coupon->id,
                    'title' => __('teacher::dashboard.notifications.types.coupon_title'),
                    'message' => trim(implode(' ', array_filter($messageParts))),
                    'type_label' => __('teacher::dashboard.notifications.types.coupons'),
                    'icon' => 'fas fa-ticket',
                    'severity' => (($daysLeft !== null && $daysLeft <= 1) || ($remaining !== null && $remaining <= 1)) ? 'danger' : 'warning',
                    'notification_key' => 'coupon:' . $coupon->id . ':' . ($coupon->updated_at?->timestamp ?? 0) . ':' . ($remaining ?? 'na') . ':' . ($daysLeft ?? 'na'),
                    'target_url' => route('teacher.dashboard.coupons.edit', $coupon->id),
                    'created_at' => $coupon->end_date ?: $coupon->updated_at,
                ];
            });
    }

    private function buildPackageNotifications(Teacher $teacher): Collection
    {
        $items = collect();
        $currentPackage = $teacher->currentPackage();

        if ($teacher->package_expires_at) {
            $daysLeft = max(now()->startOfDay()->diffInDays($teacher->package_expires_at->copy()->startOfDay(), false), 0);

            if ($daysLeft <= 14) {
                $items->push([
                    'id' => 'package-expiring-' . $teacher->id,
                    'title' => __('teacher::dashboard.notifications.types.package_expiring_title'),
                    'message' => __('teacher::dashboard.notifications.package_expiring_message', [
                        'package' => $currentPackage?->name_locale ?: __('teacher::dashboard.profile.basic.no_package'),
                        'date' => $teacher->package_expires_at->format('d/m/Y'),
                        'days' => $daysLeft,
                    ]),
                    'type_label' => __('teacher::dashboard.notifications.types.package'),
                    'icon' => 'fas fa-hourglass-half',
                    'severity' => $daysLeft <= 3 ? 'danger' : 'warning',
                    'notification_key' => 'package-expiring:' . $teacher->id . ':' . ($teacher->package_expires_at?->timestamp ?? 0),
                    'target_url' => route('teacher.dashboard.package.upgrade'),
                    'created_at' => $teacher->package_expires_at,
                ]);
            }
        }

        if ($currentPackage) {
            $featureHighlights = $this->packageFeatureHighlights($currentPackage);

            if ($featureHighlights->isNotEmpty()) {
                $packageName = $currentPackage->name_locale ?: $currentPackage->name;
                $updatedRecently = $currentPackage->updated_at instanceof Carbon
                    && $currentPackage->updated_at->greaterThanOrEqualTo(now()->subDays(21));

                $items->push([
                    'id' => 'package-features-' . $currentPackage->id,
                    'title' => $updatedRecently
                        ? __('teacher::dashboard.notifications.types.package_feature_updated_title')
                        : __('teacher::dashboard.notifications.types.package_feature_title'),
                    'message' => $updatedRecently
                        ? __('teacher::dashboard.notifications.package_feature_updated_message', [
                            'package' => $packageName,
                            'features' => $featureHighlights->implode(', '),
                        ])
                        : __('teacher::dashboard.notifications.package_feature_message', [
                            'package' => $packageName,
                            'features' => $featureHighlights->implode(', '),
                        ]),
                    'type_label' => __('teacher::dashboard.notifications.types.package'),
                    'icon' => 'fas fa-sparkles',
                    'severity' => 'info',
                    'notification_key' => 'package-features:' . $currentPackage->id . ':' . ($currentPackage->updated_at?->timestamp ?? 0),
                    'target_url' => route('teacher.dashboard.package.upgrade'),
                    'created_at' => $currentPackage->updated_at ?: $teacher->updated_at,
                ]);
            }
        }

        return $items;
    }

    private function buildAnnouncementNotifications(Teacher $teacher): Collection
    {
        $currentPackageId = $teacher->currentPackage()?->id;

        $announcements = TeacherAnnouncement::query()
            ->with('packages:id,name,code')
            ->active()
            ->where(function ($query) use ($currentPackageId) {
                $query->whereDoesntHave('packages');

                if ($currentPackageId) {
                    $query->orWhereHas('packages', function ($packageQuery) use ($currentPackageId) {
                        $packageQuery->where('teacher_packages.id', $currentPackageId);
                    });
                }
            })
            ->orderByDesc('is_pinned')
            ->latest('id')
            ->take(4)
            ->get();

        return $announcements->map(function (TeacherAnnouncement $announcement) {
            return [
                'id' => 'announcement-' . $announcement->id,
                'title' => $announcement->title_locale,
                'message' => $announcement->message_locale,
                'type_label' => __('teacher::dashboard.notifications.types.announcement'),
                'icon' => $announcement->icon ?: 'fas fa-bullhorn',
                'severity' => $announcement->is_pinned ? 'primary' : 'info',
                'notification_key' => 'announcement:' . $announcement->id . ':' . ($announcement->updated_at?->timestamp ?? 0),
                'target_url' => $announcement->action_url ?: route('teacher.dashboard.notifications'),
                'action_label' => $announcement->action_label_locale,
                'created_at' => $announcement->updated_at ?: $announcement->created_at,
            ];
        });
    }

    private function hydrateSyntheticReadState(Student $student, Collection $items): Collection
    {
        $keys = $items
            ->pluck('notification_key')
            ->filter(fn ($key) => is_string($key) && $key !== '')
            ->values()
            ->all();

        if (empty($keys)) {
            return $items->values();
        }

        $readMap = TeacherNotificationRead::query()
            ->where('student_id', $student->id)
            ->whereIn('notification_key', $keys)
            ->pluck('read_at', 'notification_key');

        return $items->map(function (array $item) use ($readMap) {
            $key = $item['notification_key'] ?? null;
            $targetUrl = trim((string) ($item['target_url'] ?? route('teacher.dashboard.notifications')));
            $item['is_unread'] = $key ? empty($readMap[$key] ?? null) : true;
            $item['url'] = $key
                ? route('teacher.dashboard.notifications.read', [
                    'key' => $key,
                    'redirect' => $targetUrl,
                ])
                : $targetUrl;

            unset($item['target_url']);

            return $item;
        })->values();
    }

    private function packageFeatureHighlights(TeacherPackage $package): Collection
    {
        $features = collect();

        if ($package->effective_course_limit) {
            $features->push(__('teacher::dashboard.package_features.labels.course_limit') . ': ' . $package->effective_course_limit);
        }

        if ($package->effective_coupon_limit) {
            $features->push(__('teacher::dashboard.package_features.labels.coupon_limit') . ': ' . $package->effective_coupon_limit);
        }

        foreach ([
            'can_manage_comments',
            'can_manage_coupons',
            'can_manage_students',
            'can_view_student_progress',
            'can_view_activity_logs',
            'can_duplicate_courses',
            'can_grant_courses',
            'can_export_orders',
            'can_export_students',
            'can_import_export_lessons',
            'can_sell_bundles',
            'can_schedule_content',
            'can_send_promotions',
            'can_issue_certificates',
            'can_customize_teacher_landing',
            'can_use_affiliate_links',
        ] as $featureKey) {
            if ($package->hasFeature($featureKey)) {
                $features->push(__('teacher::dashboard.package_features.labels.' . $featureKey));
            }
        }

        return $features->take(3)->values();
    }
}
