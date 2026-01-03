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
    $hours = floor($totalSeconds / 3600);
    $minutes = floor(($totalSeconds % 3600) / 60);
    $seconds = $totalSeconds % 60;

    // < 1 phút
    if ($totalSeconds < 60) {
        return sprintf('00:%02d giây', $seconds);
    }

    // < 1 tiếng
    if ($totalSeconds < 3600) {
        return sprintf('%02d:%02d phút', $minutes, $seconds);
    }

    // >= 1 tiếng
    return sprintf('%02d:%02d:%02d tiếng', $hours, $minutes, $seconds);
}

function getLessonCount($course){
    $lessonRepository = app(LessonsRepositoryInterface::class);
    return $lessonRepository->getLessonCount($course);
}
