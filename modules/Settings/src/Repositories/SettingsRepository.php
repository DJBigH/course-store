<?php

namespace Modules\Settings\src\Repositories;

use App\Repositories\BaseRepository;
use Modules\Settings\src\Models\Setting;
use Modules\Settings\src\Repositories\SettingsRepositoryInterface;

class SettingsRepository extends BaseRepository implements SettingsRepositoryInterface
{
    public function getModel()
    {
        return Setting::class;
    }
}