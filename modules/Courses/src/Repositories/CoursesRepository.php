<?php

namespace Modules\Courses\src\Repositories;

use App\Repositories\BaseRepository;
use Modules\Courses\Src\Models\Courses;
use Modules\Courses\src\Repositories\CoursesRepositoryInterface;

class CoursesRepository extends BaseRepository implements CoursesRepositoryInterface
{
    public function getModel()
    {
        return Courses::class;
    }

    public function getAllCourses()
    {
        return $this->model->select(['id', 'name', 'price', 'status', 'sale_price', 'created_at'])->latest();
    }

    public function createCoursesCategory($course, $data = [])
    {
        return $course->categories()->attach($data);
    }

    public function updateCoursesCategories($course, $data = [])
    {
        return $course->categories()->sync($data);
    }

    public function deleteCoursesCategories($course){
        return $course->categories()->detach();
    }

    public function getRelatedCategories($courses)
    {
        $categoryId = $courses->categories()->allRelatedIds()->toArray();
        return $categoryId;
    }

    
}
