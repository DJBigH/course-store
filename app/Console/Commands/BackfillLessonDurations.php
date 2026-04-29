<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Courses\src\Models\Courses;
use Modules\Lessons\src\Models\Lesson;

class BackfillLessonDurations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lessons:backfill-durations {--force : Recalculate even when durations already exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill lesson durations and recalculate total course durations';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $updatedLessons = 0;
        $skippedLessons = 0;
        $failedLessons = 0;
        $updatedCourses = 0;
        $touchedCourseIds = [];

        $query = Lesson::query()
            ->with('video')
            ->whereNotNull('video_id')
            ->orderBy('id');

        if (!$force) {
            $query->where('durations', '<=', 0);
        }

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->info('Không có bài giảng nào cần cập nhật thời gian.');
            return self::SUCCESS;
        }

        $this->info("Dang quet {$total} bai giang...");
        $progressBar = $this->output->createProgressBar($total);
        $progressBar->start();

        $query->chunkById(50, function ($lessons) use ($force, &$updatedLessons, &$skippedLessons, &$failedLessons, &$touchedCourseIds, $progressBar) {
            foreach ($lessons as $lesson) {
                $videoUrl = trim((string) ($lesson->video?->url ?? ''));

                if ($videoUrl === '') {
                    $skippedLessons++;
                    $progressBar->advance();
                    continue;
                }

                $newDuration = 0;

                try {
                    $normalizedUrl = normalizeVideoUrl($videoUrl);
                    $isExternal = (bool) preg_match('~^https?://~i', $normalizedUrl);

                    if ($isExternal) {
                        $newDuration = externalVideoDuration($normalizedUrl);
                    } else {
                        $videoInfo = getVideoInfo($videoUrl);
                        $newDuration = (int) ($videoInfo['playtime_seconds'] ?? 0);
                    }
                } catch (\Throwable $exception) {
                    $newDuration = 0;
                }

                if ($newDuration <= 0) {
                    $failedLessons++;
                    $progressBar->advance();
                    continue;
                }

                $currentDuration = (float) $lesson->getRawOriginal('durations');

                if (!$force && $currentDuration > 0) {
                    $skippedLessons++;
                    $progressBar->advance();
                    continue;
                }

                if ((int) round($currentDuration) === (int) round($newDuration)) {
                    $skippedLessons++;
                    $progressBar->advance();
                    continue;
                }

                $lesson->forceFill([
                    'durations' => $newDuration,
                ])->save();

                $updatedLessons++;
                $touchedCourseIds[$lesson->course_id] = true;
                $progressBar->advance();
            }
        });

        $progressBar->finish();
        $this->newLine(2);

        if (!empty($touchedCourseIds)) {
            foreach (array_keys($touchedCourseIds) as $courseId) {
                $course = Courses::withoutGlobalScopes()->find($courseId);

                if (!$course) {
                    continue;
                }

                $duration = $course->lessons()
                    ->whereNotNull('parent_id')
                    ->where('status', 1)
                    ->get()
                    ->sum(fn($lesson) => (float) $lesson->getRawOriginal('durations'));

                $course->forceFill([
                    'durations' => $duration,
                ])->save();

                $updatedCourses++;
            }
        }

        $this->table(
            ['Chi tiết', 'Số lượng'],
            [
                ['Bài giảng đã cập nhật', $updatedLessons],
                ['Bài giảng bỏ qua', $skippedLessons],
                ['Bải giảng không lấy được tổng thời gian', $failedLessons],
                ['Khóa học đã tính lại tổng thời gian', $updatedCourses],
            ]
        );

        return self::SUCCESS;
    }
}
