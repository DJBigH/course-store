<?php

namespace Modules\Courses\src\Policies;

use Modules\Courses\src\Models\Courses;
use Modules\Courses\src\Models\CourseQuiz;
use Modules\Teacher\src\Models\Teacher;

class CourseQuizPolicy
{
    public function viewAny(Teacher $teacher, ?Courses $course = null): bool
    {
        return $teacher->packageHasFeature('can_manage_quizzes') && (!$course || (int) $course->teacher_id === (int) $teacher->id);
    }

    public function view(Teacher $teacher, CourseQuiz $quiz): bool
    {
        return $teacher->packageHasFeature('can_manage_quizzes') && (int) $quiz->course?->teacher_id === (int) $teacher->id;
    }

    public function create(Teacher $teacher, Courses $course): bool
    {
        return $teacher->packageHasFeature('can_manage_quizzes') && (int) $course->teacher_id === (int) $teacher->id;
    }

    public function update(Teacher $teacher, CourseQuiz $quiz): bool
    {
        return $teacher->packageHasFeature('can_manage_quizzes') && (int) $quiz->course?->teacher_id === (int) $teacher->id;
    }

    public function delete(Teacher $teacher, CourseQuiz $quiz): bool
    {
        return $teacher->packageHasFeature('can_manage_quizzes') && (int) $quiz->course?->teacher_id === (int) $teacher->id;
    }
}
