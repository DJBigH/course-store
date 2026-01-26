<?php

namespace Modules\Contacts\src\Repositories;

use App\Repositories\BaseRepository;
use Modules\Contacts\src\Models\Contacts;
use Modules\Contacts\src\Repositories\ContactsRepositoryInterface;

class ContactsRepository extends BaseRepository implements ContactsRepositoryInterface
{
    public function getModel()
    {
        return Contacts::class;
    }
}