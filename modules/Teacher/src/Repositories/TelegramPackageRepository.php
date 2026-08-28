<?php

namespace Modules\Teacher\src\Repositories;

use App\Repositories\BaseRepository;
use Modules\Teacher\src\Models\TelegramPackage;

class TelegramPackageRepository extends BaseRepository implements TelegramPackageRepositoryInterface
{
    public function getModel()
    {
        return TelegramPackage::class;
    }

    public function getAllPackages()
    {
        return $this->model->orderBy('sort_order')->orderBy('id', 'desc');
    }

    public function getActivePackages()
    {
        return $this->model->where('is_active', true)->orderBy('sort_order')->get();
    }
}
