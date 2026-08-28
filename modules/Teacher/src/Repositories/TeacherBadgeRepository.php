<?php

namespace Modules\Teacher\src\Repositories;

use App\Repositories\BaseRepository;
use Modules\Teacher\src\Models\TeacherBadge;

class TeacherBadgeRepository extends BaseRepository implements TeacherBadgeRepositoryInterface
{
    public function getModel()
    {
        return TeacherBadge::class;
    }

    public function getAllBadges()
    {
        return $this->model->query();
    }

    public function getTrashBadges()
    {
        return $this->model->query()->onlyTrashed();
    }
}
