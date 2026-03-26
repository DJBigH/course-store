<?php

namespace Modules\Courses\src\Repositories;

use App\Models\Scopes\ActiveScope;
use App\Repositories\BaseRepository;
use Modules\Courses\src\Models\Courses;
use Modules\Courses\src\Repositories\CoursesRepositoryInterface;

class CoursesRepository extends BaseRepository implements CoursesRepositoryInterface
{
    public function getModel()
    {
        return Courses::class;
    }

    public function getAllCourses()
    {
        return $this->model
            ->withoutGlobalScope(ActiveScope::class)
            ->with(['teacher:id,name'])
            ->select(['id', 'name', 'price', 'status', 'sale_price', 'created_at', 'teacher_id', 'view'])
            ->withCount(['lessons', 'students'])
            ->latest();
    }

    public function getAdminCourseStats(): array
    {
        $query = $this->model->withoutGlobalScope(ActiveScope::class);

        return [
            'total' => (clone $query)->count(),
            'published' => (clone $query)->where('status', 1)->count(),
            'draft' => (clone $query)->where('status', 0)->count(),
            'free' => (clone $query)->where('price', 0)->count(),
        ];
    }

    public function getCourse($id)
    {
        return $this->model->withoutGlobalScope(ActiveScope::class)->find($id);
    }

    public function getCourseActive($slug)
    {
        return $this->model
            ->withCount('students')
            ->where(function ($query) use ($slug) {
                $query->where('slug', $slug)
                    ->orWhere('slug_en', $slug)
                    ->orWhere('slug_ko', $slug)
                    ->orWhere('slug_ja', $slug)
                    ->orWhere('slug_zh', $slug);
            })
            ->first();
    }


    public function createCoursesCategory($course, $data = [])
    {
        return $course->categories()->attach($data);
    }

    public function updateCoursesCategories($course, $data = [])
    {
        return $course->categories()->sync($data);
    }

    public function deleteCoursesCategories($course)
    {
        return $course->categories()->detach();
    }

    public function getRelatedCategories($courses)
    {
        $categoryId = $courses->categories()->allRelatedIds()->toArray();
        return $categoryId;
    }

    public function getCourses($limit)
    {
        return $this->model->withCount('students')->limit($limit)->latest()->paginate($limit);
    }

    public function updateCourse($id, $data = [])
    {
        $result = $this->getCourse($id);
        if ($result) {
            return $result->update($data);
        }
        return false;
    }

    public function deleteCourse($id)
    {
        return $this->model->withoutGlobalScope(ActiveScope::class)->where('id', $id)->delete($id);
    }

    public function createOrder($data = [])
    {
        return $this->model->orders()->create($data);
    }

    public function getCourseFree()
    {
        return $this->model->where('price', 0)->where('sale_price', 0)->where('status', 1)->limit(5)->get();
    }

    public function getCourseView()
    {
        return $this->model->orderBy('view', 'DESC')->where('status', 1)->limit(5)->get();
    }

    public function getCourseCreateUpdate()
    {
        return $this->model->orderBy('created_at', 'DESC')->orderBy('updated_at', 'DESC')->where('status', 1)->limit(5)->get();
    }

    public function getAllCoursesHome()
    {
        return $this->model->where('status', 1)->get();
    }

    public function getCourseForYou($studentId)
    {
        return $this->model
            ->where('status', 1)
            ->when($studentId, function ($query) use ($studentId) {
                $query->whereDoesntHave('students', function ($q) use ($studentId) {
                    $q->where('student_id', $studentId);
                });
            })
            ->get();
    }
}
