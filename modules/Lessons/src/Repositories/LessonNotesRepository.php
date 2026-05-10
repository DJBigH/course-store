<?php

namespace Modules\Lessons\src\Repositories;

use App\Repositories\BaseRepository;
use Modules\Lessons\src\Models\LessonNote;

class LessonNotesRepository extends BaseRepository implements LessonNotesRepositoryInterface
{
    public function getModel()
    {
        return LessonNote::class;
    }

    public function getNotesByLesson($lessonId, $studentId)
    {
        return $this->model
            ->where('lesson_id', $lessonId)
            ->where('student_id', $studentId)
            ->orderBy('time_at', 'ASC')
            ->get();
    }
}
