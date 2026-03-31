<?php

namespace Modules\Courses\src\Repositories;

use App\Repositories\RepositoryInterface;

interface CoursesRepositoryInterface extends RepositoryInterface
{
    //function for admin
    public function getAllCourses();
    public function getAdminCourseStats(): array;

    public function createCoursesCategory($course, $data = []);

    public function updateCoursesCategories($course, $data = []);

    public function deleteCoursesCategories($course);

    public function getRelatedCategories($courses);

    public function getCourse($id);

    public function updateCourse($id, $data = []);
    public function deleteCourse($id);

    //function for clients
    public function getCourses($limit);
    public function getCourseActive($slug);
    public function getCourseForClientAccess($slug, ?int $studentId = null);
    public function createOrder($data = []);
    public function getCourseFree();
    public function getCourseView();
    public function getCourseCreateUpdate();
    public function getAllCoursesHome();
    public function getCourseForYou($studentId);
}
