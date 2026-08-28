<?php

namespace Modules\Students\src\Repositories;

use App\Models\Scopes\ActiveScope;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Students\src\Models\Student;
use Modules\Students\src\Repositories\StudentsRepositoryInterface;

class StudentsRepository extends BaseRepository implements StudentsRepositoryInterface
{
    public function getModel()
    {
        return Student::class;
    }

    public function getUser($limit)
    {
        return $this->model->paginate($limit);
    }

    public function getAllStudents()
    {
        return $this->model->with([
            'teacher:id,student_id',
            'courses:id,name,teacher_id',
            'courses.teacher:id,name'
        ])->select(['id', 'name', 'email', 'status', 'email_verified_at', 'two_factor_email_enabled', 'created_at'])->latest();
    }

    public function setPassword($password, $id)
    {
        return $this->update($id, ['password' => Hash::make($password)]);
    }

    public function checkPassword($password, $id)
    {
        $user = $this->find($id);
        if (!empty($user)) {
            $hashPassword = $user->password;
            return Hash::check($password, $hashPassword);
        } else {
            return 'Tài khoản mật khẩu không tồn tại!';
        }
    }

    public function getCourses($studentId, $filters = [], $limit)
    {
        extract($filters);
        $query = $this->find($studentId)->courses();
        if (!empty($teacher_id)) {
            $query->where('teacher_id', $teacher_id);
        }
        if (!empty($keyword)) {
            $query->where(function ($builder) use ($keyword) {
                $builder->where('name', 'like', '%' . $keyword . '%');
                $builder->orWhere('detail', 'like', '%' . $keyword . '%');
            });
        }
        return $query->withoutGlobalScope(ActiveScope::class)->paginate($limit)->withQueryString();
    }

    public function getCoupons($studentId, $filters = [], $limit)
    {
        extract($filters);

        $query = $this->find($studentId)->coupons();

        return $query->orderBy('coupons_students.created_at', 'desc')
            ->paginate($limit)
            ->withQueryString();
    }


    public function getPurchasedCourses(int $studentId, $limit)
    {
        $student = $this->find($studentId);
        $teacher = $student?->teacher;
        $ownTeacherId = ($teacher && $teacher->status === \Modules\Teacher\src\Models\Teacher::STATUS_ACTIVE) 
            ? $teacher->id 
            : null;

        return \Modules\Courses\src\Models\Courses::query()
            ->with('teacher')
            ->where(function($query) use ($studentId, $ownTeacherId) {
                $query->whereHas('students', function($q) use ($studentId) {
                    $q->where('students.id', $studentId)->where('students_courses.status', 1);
                });
                
                if ($ownTeacherId) {
                    $query->orWhere('teacher_id', $ownTeacherId);
                }
            })
            ->latest('created_at')
            ->withoutGlobalScope(ActiveScope::class)
            ->paginate($limit)
            ->withQueryString();
    }
}
