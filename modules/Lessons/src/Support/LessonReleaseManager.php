<?php

namespace Modules\Lessons\src\Support;

use Illuminate\Support\Carbon;
use Modules\Lessons\src\Models\Lesson;

class LessonReleaseManager
{
    public const MODE_IMMEDIATE = 'immediate';
    public const MODE_DATETIME = 'datetime';
    public const MODE_DAYS_AFTER_ENROLLMENT = 'days_after_enrollment';
    public const MODE_AFTER_PREVIOUS_COMPLETED = 'after_previous_completed';

    public function normalizePayload(array $data, ?Lesson $lesson = null, bool $canScheduleContent = true): array
    {
        if (!$canScheduleContent) {
            return [
                'release_mode' => $lesson?->release_mode ?: self::MODE_IMMEDIATE,
                'release_at' => $lesson?->release_at,
                'release_after_days' => $lesson?->release_after_days,
            ];
        }

        $mode = (string) ($data['release_mode'] ?? self::MODE_IMMEDIATE);
        if (!in_array($mode, [
            self::MODE_IMMEDIATE,
            self::MODE_DATETIME,
            self::MODE_DAYS_AFTER_ENROLLMENT,
            self::MODE_AFTER_PREVIOUS_COMPLETED,
        ], true)) {
            $mode = self::MODE_IMMEDIATE;
        }

        if ($mode === self::MODE_DATETIME) {
            return [
                'release_mode' => $mode,
                'release_at' => !empty($data['release_at']) ? Carbon::parse($data['release_at']) : null,
                'release_after_days' => null,
            ];
        }

        if ($mode === self::MODE_DAYS_AFTER_ENROLLMENT) {
            return [
                'release_mode' => $mode,
                'release_at' => null,
                'release_after_days' => max(1, (int) ($data['release_after_days'] ?? 1)),
            ];
        }

        if ($mode === self::MODE_AFTER_PREVIOUS_COMPLETED) {
            return [
                'release_mode' => $mode,
                'release_at' => null,
                'release_after_days' => null,
            ];
        }

        return [
            'release_mode' => self::MODE_IMMEDIATE,
            'release_at' => null,
            'release_after_days' => null,
        ];
    }

    public function resolveAvailableAt(Lesson $lesson, ?Carbon $enrolledAt = null, bool $hasCourse = false): ?Carbon
    {
        if (!$hasCourse) {
            return null;
        }

        if ($lesson->release_mode === self::MODE_DATETIME) {
            return $lesson->release_at ? Carbon::parse($lesson->release_at) : null;
        }

        if ($lesson->release_mode === self::MODE_DAYS_AFTER_ENROLLMENT) {
            if (!$enrolledAt || !$lesson->release_after_days) {
                return null;
            }

            return $enrolledAt->copy()->addDays((int) $lesson->release_after_days);
        }

        return null;
    }

    public function isAvailableForStudent(
        Lesson $lesson,
        bool $hasCourse = false,
        ?Carbon $enrolledAt = null,
        array $context = []
    ): bool {
        if (!$hasCourse) {
            return true;
        }

        if ($lesson->release_mode === self::MODE_AFTER_PREVIOUS_COMPLETED) {
            $hasPrevious = (bool) ($context['has_previous'] ?? false);
            $previousCompleted = (bool) ($context['previous_completed'] ?? false);

            return !$hasPrevious || $previousCompleted;
        }

        $availableAt = $this->resolveAvailableAt($lesson, $enrolledAt, $hasCourse);
        if (!$availableAt) {
            return true;
        }

        return $availableAt->lte(now());
    }

    public function buildLockedMessage(
        Lesson $lesson,
        ?Carbon $enrolledAt = null,
        bool $hasCourse = false,
        array $context = []
    ): ?string {
        if ($this->isAvailableForStudent($lesson, $hasCourse, $enrolledAt, $context)) {
            return null;
        }

        if ($lesson->release_mode === self::MODE_AFTER_PREVIOUS_COMPLETED) {
            $previousLessonName = trim((string) ($context['previous_lesson_name'] ?? ''));

            return $previousLessonName !== ''
                ? 'Bài học này sẽ mở sau khi bạn hoàn thành "' . $previousLessonName . '".'
                : 'Bài học này sẽ mở sau khi bạn học xong bài trước.';
        }

        $availableAt = $this->resolveAvailableAt($lesson, $enrolledAt, $hasCourse);
        if ($availableAt) {
            return 'Bài học này sẽ mở vào ' . $availableAt->format('d/m/Y H:i') . '.';
        }

        if ($lesson->release_mode === self::MODE_DAYS_AFTER_ENROLLMENT && $lesson->release_after_days) {
            return 'Bài học này sẽ mở sau ' . (int) $lesson->release_after_days . ' ngày kể từ lúc bạn đăng ký khóa học.';
        }

        return 'Bài học này chưa tới lịch mở.';
    }

    public function describeForTeacher(?Lesson $lesson): string
    {
        if (!$lesson || $lesson->release_mode === self::MODE_IMMEDIATE) {
            return 'Mở ngay';
        }

        if ($lesson->release_mode === self::MODE_DATETIME && $lesson->release_at) {
            return 'Mở vào ' . Carbon::parse($lesson->release_at)->format('d/m/Y H:i');
        }

        if ($lesson->release_mode === self::MODE_DAYS_AFTER_ENROLLMENT && $lesson->release_after_days) {
            return 'Mở sau ' . (int) $lesson->release_after_days . ' ngày';
        }

        if ($lesson->release_mode === self::MODE_AFTER_PREVIOUS_COMPLETED) {
            return 'Mở sau khi học xong bài trước';
        }

        return 'Mở ngay';
    }

    public function hasPendingRelease(?Lesson $lesson): bool
    {
        if (!$lesson) {
            return false;
        }

        return $lesson->release_mode === self::MODE_DATETIME
            && $lesson->release_at !== null
            && Carbon::parse($lesson->release_at)->isFuture();
    }
}
