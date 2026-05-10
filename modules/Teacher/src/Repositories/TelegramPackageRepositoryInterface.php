<?php

namespace Modules\Teacher\src\Repositories;

use App\Repositories\RepositoryInterface;

interface TelegramPackageRepositoryInterface extends RepositoryInterface
{
    public function getAllPackages();
    public function getActivePackages();
}
