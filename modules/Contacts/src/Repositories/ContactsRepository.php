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

    public function getContacts()
    {
        return $this->model->select([
            'id',
            'name',
            'phone',
            'email',
            'subject',
            'submission_type',
            'category',
            'message',
            'status',
            'workflow_status',
            'source',
            'student_id',
            'teacher_id',
            'created_at',
        ])->latest();
    }
}
