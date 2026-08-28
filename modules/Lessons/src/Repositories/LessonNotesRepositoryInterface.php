<?php

namespace Modules\Lessons\src\Repositories;

use App\Repositories\RepositoryInterface;

interface LessonNotesRepositoryInterface extends RepositoryInterface
{
    public function getNotesByLesson($lessonId, $studentId);
}
