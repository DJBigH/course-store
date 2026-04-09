<?php

namespace Modules\Teacher\src\Repositories;

use App\Repositories\BaseRepository;
use Modules\Teacher\src\Models\Teacher;
use Modules\Teacher\src\Repositories\TeacherRepositoryInterface;

class TeacherRepository extends BaseRepository implements TeacherRepositoryInterface
{
    public function getModel()
    {
        return Teacher::class;
    }

    public function getAllTeacher()
    {
        return $this->model->select([
            'id',
            'name',
            'name_en',
            'name_ko',
            'name_ja',
            'name_zh',
            'slug',
            'slug_en',
            'slug_ko',
            'slug_ja',
            'slug_zh',
            'exp',
            'image',
            'status',
            'last_active_at',
            'package_expires_at',
            'created_at',
        ])->latest();
    }

    public function getTeachers(){
        return $this->getAll();
    }
}
