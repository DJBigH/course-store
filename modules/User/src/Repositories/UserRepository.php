<?php

namespace Modules\User\src\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Hash;
use Modules\User\src\Models\User;
use Modules\User\src\Repositories\UserRepositoryInterface;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function getModel()
    {
        return User::class;
    }

    public function getUser($limit)
    {
        return $this->model->paginate($limit);
    }

    public function getAllUser()
    {
        return $this->model->select(['id','name', 'email', 'group_id', 'created_at']);
    }

    public function setPassword($password, $id)
    {
        return $this->update($id, ['password' => Hash::make($password)]);
    }

    public function checkPassword($password, $id)
    {
        $user = $this->find($id);
        if (!empty($user)) {
            $hashPassword = $user->password;
            return Hash::check($password, $hashPassword);
        } else {
            return 'Tài khoản mật khẩu không tồn tại!';
        }
    }
}
