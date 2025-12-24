<?php

namespace Modules\Courses\src\Repositories;

use App\Repositories\RepositoryInterface;

interface CoursesRepositoryInterface extends RepositoryInterface
{
    public function getAllCourses();

    public function createCoursesCategory($course, $data = []);

    public function updateCoursesCategories($course, $data = []);

    public function deleteCoursesCategories($course);

    public function getRelatedCategories($courses);
}
