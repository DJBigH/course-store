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
        return $this->model->withoutGlobalScope(ActiveScope::class)->select(['id', 'name', 'price', 'status', 'sale_price', 'created_at'])->latest();
    }

    public function getCourse($id)
    {
        return $this->model->withoutGlobalScope(ActiveScope::class)->find($id);
    }

    public function getCourseActive($slug)
    {
        return $this->model->withCount('students')->whereSlug($slug)->first();
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
}
