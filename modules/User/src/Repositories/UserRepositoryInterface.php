<?php

namespace Modules\User\src\Repositories;

use App\Repositories\RepositoryInterface;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function getUser($limit);

    public function getAllUser();

    public function setPassword($password,$id);

    public function checkPassword($password,$id);
}