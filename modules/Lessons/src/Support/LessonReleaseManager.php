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
                ? __('courses::teacher/messages.lessons.scheduling.locked_messages.after_previous', ['lesson' => $previousLessonName])
                : __('courses::teacher/messages.lessons.scheduling.locked_messages.after_previous_generic');
        }

        $availableAt = $this->resolveAvailableAt($lesson, $enrolledAt, $hasCourse);
        if ($availableAt) {
            return __('courses::teacher/messages.lessons.scheduling.locked_messages.datetime', ['date' => $availableAt->format('d/m/Y H:i')]);
        }

        if ($lesson->release_mode === self::MODE_DAYS_AFTER_ENROLLMENT && $lesson->release_after_days) {
            return __('courses::teacher/messages.lessons.scheduling.locked_messages.days_after', ['days' => (int) $lesson->release_after_days]);
        }

        return __('courses::teacher/messages.lessons.scheduling.locked_messages.not_yet');
    }

    public function describeForTeacher(?Lesson $lesson): string
    {
        if (!$lesson || $lesson->release_mode === self::MODE_IMMEDIATE) {
            return __('courses::teacher/messages.lessons.scheduling.labels.immediate');
        }

        if ($lesson->release_mode === self::MODE_DATETIME && $lesson->release_at) {
            return __('courses::teacher/messages.lessons.scheduling.labels.datetime', ['date' => Carbon::parse($lesson->release_at)->format('d/m/Y H:i')]);
        }

        if ($lesson->release_mode === self::MODE_DAYS_AFTER_ENROLLMENT && $lesson->release_after_days) {
            return __('courses::teacher/messages.lessons.scheduling.labels.days_after', ['count' => (int) $lesson->release_after_days]);
        }

        if ($lesson->release_mode === self::MODE_AFTER_PREVIOUS_COMPLETED) {
            return __('courses::teacher/messages.lessons.scheduling.labels.after_previous');
        }

        return __('courses::teacher/messages.lessons.scheduling.labels.immediate');
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
