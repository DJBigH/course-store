<?php

namespace Modules\Students\src\Repositories;

use App\Repositories\RepositoryInterface;

interface StudentsRepositoryInterface extends RepositoryInterface
{
    public function getUser($limit);

    public function getAllStudents();

    public function setPassword($password, $id);

    public function checkPassword($password, $id);
    public function getCourses($studentId, $filters = [], $limit);
    public function getPurchasedCourses(int $studentId);
    public function getCoupons($studentId, $filters = [], $limit);
}
