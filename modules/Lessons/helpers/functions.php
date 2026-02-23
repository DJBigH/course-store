<?php

use Modules\Lessons\src\Repositories\LessonsRepository;
use Modules\Lessons\src\Repositories\LessonsRepositoryInterface;

function getLessons($lessons, $old = '', $parentId = 0, $char = '')
{
    $id = request()->route()->lessonId;
    if ($lessons) {
        foreach ($lessons as $key => $lesson) {
            if ($lesson->parent_id == $parentId && $id != $lesson->id) {
                echo '<option value="' . $lesson->id . '"';
                if ($old == $lesson->id) {
                    echo ' selected';
                }
                echo '>' . $char . $lesson->name . '</option>';
                unset($lessons[$key]);
                getLessons($lessons, $old, $lesson->id, $char . ' |- ');
            }
        }
    }
}


function getTime($totalSeconds)
{
    $hours   = floor($totalSeconds / 3600);
    $minutes = floor(($totalSeconds % 3600) / 60);
    $seconds = $totalSeconds % 60;

    $tSecond = __('clients/common.second');
    $tMinute = __('clients/common.minute');
    $tHour   = __('clients/common.hour');

    // < 1 phút
    if ($totalSeconds < 60) {
        return sprintf('00:%02d %s', $seconds, $tSecond);
    }

    // < 1 giờ
    if ($totalSeconds < 3600) {
        return sprintf('%02d:%02d %s', $minutes, $seconds, $tMinute);
    }

    // >= 1 giờ
    return sprintf('%02d:%02d:%02d %s', $hours, $minutes, $seconds, $tHour);
}


function getLessonCount($course)
{
    $lessonRepository = app(LessonsRepositoryInterface::class);
    return $lessonRepository->getLessonCount($course);
}

function getModuleByPosition($course)
{
    $lessonRepository = app(LessonsRepositoryInterface::class);
    return $lessonRepository->getModuleByPosition($course);
}

function getLessonByPosition($course, $moduleId = null, $isDocument = false)
{
    $lessonRepository = app(LessonsRepositoryInterface::class);
    return $lessonRepository->getLessonByPosition($course, $moduleId, $isDocument);
}
