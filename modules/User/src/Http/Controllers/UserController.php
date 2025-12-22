<?php

namespace Modules\User\Src\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\User\Src\Http\Requests\UserRequest;
use Modules\User\src\Repositories\UserRepository;

class UserController extends Controller
{

    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    public function index()
    {
        $pageTitle = 'Quản lý người dùng';
        return view('user::lists', compact('pageTitle'));
    }

    public function create()
    {
        $pageTitle = 'Thêm mới người dùng';
        return view('user::create', compact('pageTitle'));
    }

    public function store(UserRequest $request)
    {
        $dataInsert = [
            'name' => $request->name,
            'email' => $request->email,
            'group_id' => $request->group_id,
            'password' => bcrypt($request->password),
        ];
        $this->userRepository->create($dataInsert);

        return redirect()->route('user.index')->with('msg', __('user::messages.success'));
    }
}
