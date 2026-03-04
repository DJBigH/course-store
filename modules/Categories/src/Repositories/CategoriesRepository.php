<?php

namespace Modules\Categories\src\Repositories;

use App\Repositories\BaseRepository;
use Modules\Categories\src\Models\Category;
use Modules\Categories\src\Repositories\CategoriesRepositoryInterface;

class CategoriesRepository extends BaseRepository implements CategoriesRepositoryInterface
{
    public function getModel()
    {
        return Category::class;
    }

    public function getCategories()
    {
        return $this->model->with('subCategories')->whereParentId(0)->select(['id', 'name', 'name_en', 'name_ko', 'name_ja', 'name_zh', 'slug', 'slug_en', 'slug_ko', 'slug_ja', 'slug_zh', 'parent_id', 'created_at'])->latest();
    }

    public function getAllCategories()
    {
        return $this->getAll();
    }

}
