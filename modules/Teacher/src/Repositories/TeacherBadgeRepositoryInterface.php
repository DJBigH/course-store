<?php

namespace Modules\Teacher\src\Repositories;

use App\Repositories\RepositoryInterface;

interface TeacherBadgeRepositoryInterface extends RepositoryInterface
{
    public function getAllBadges();
    public function getTrashBadges();
}
