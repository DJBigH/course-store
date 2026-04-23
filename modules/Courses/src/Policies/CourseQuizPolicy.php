<?php

namespace Modules\Courses\src\Policies;

use Modules\Courses\src\Models\Courses;
use Modules\Courses\src\Models\CourseQuiz;
use Modules\Teacher\src\Models\Teacher;

class CourseQuizPolicy
{
    public function viewAny(Teacher $teacher, ?Courses $course = null): bool
    {
        // Cho phép vào xem danh sách ngay cả khi bảo trì (nhưng sẽ không tương tác được)
        $package = $teacher->currentPackage();
        return $package && $package->can_manage_quizzes && (!$course || (int) $course->teacher_id === (int) $teacher->id);
    }

    public function view(Teacher $teacher, CourseQuiz $quiz): bool
    {
        // Xem chi tiết/kết quả vẫn cho phép
        $package = $teacher->currentPackage();
        return $package && $package->can_manage_quizzes && (int) $quiz->course?->teacher_id === (int) $teacher->id;
    }

    public function create(Teacher $teacher, Courses $course): bool
    {
        $package = $teacher->currentPackage();
        if (!$package || !$package->can_manage_quizzes || (int) $course->teacher_id !== (int) $teacher->id) {
            return false;
        }

        // Chặn tạo mới nếu đang bảo trì
        return !$package->isFeatureInMaintenance('can_manage_quizzes');
    }

    public function update(Teacher $teacher, CourseQuiz $quiz): bool
    {
        $package = $teacher->currentPackage();
        if (!$package || !$package->can_manage_quizzes || (int) $quiz->course?->teacher_id !== (int) $teacher->id) {
            return false;
        }

        // Chặn cập nhật nếu đang bảo trì
        return !$package->isFeatureInMaintenance('can_manage_quizzes');
    }

    public function delete(Teacher $teacher, CourseQuiz $quiz): bool
    {
        $package = $teacher->currentPackage();
        if (!$package || !$package->can_manage_quizzes || (int) $quiz->course?->teacher_id !== (int) $teacher->id) {
            return false;
        }

        // Chặn xóa nếu đang bảo trì
        return !$package->isFeatureInMaintenance('can_manage_quizzes');
    }
}
