<?php

namespace Modules\User\Src\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Modules\User\Src\Http\Requests\UserRequest;
use Modules\User\src\Repositories\UserRepository;
use Modules\User\src\Repositories\UserRepositoryInterface;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{

    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    public function index()
    {
        $pageTitle = 'Quản lý người dùng';
        return view('user::lists', compact('pageTitle'));
    }

    public function data()
    {
        $users = $this->userRepository->getAllUser();

        return DataTables::of($users)
            ->addColumn('edit', function ($user) {
                return '<a href="' . route('user.edit', $user->id) . '" class="btn btn-warning">Sửa</a>';
            })
            ->addColumn('delete', function ($user) {
                return '<a href="' . route('user.delete', $user->id) . '" class="btn btn-danger delete-action">Xóa</a>';
            })
            ->editColumn('created_at', function ($users) {
                return Carbon::parse($users->created_at)->format('d/m/Y H:i:s');
            })
            ->rawColumns(['edit', 'delete'])
            ->toJson();
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

        return redirect()->route('user.index')->with('msg', __('user::messages.create.success'));
    }

    public function edit($id)
    {
        $pageTitle = 'Cập nhập người dùng';
        $users = $this->userRepository->find($id);
        if (empty($users)) {
            abort(404);
        }

        return view('user::edit', compact('users', 'pageTitle'));
    }

    public function update(UserRequest $request, $id)
    {
        $data = $request->except('_token', 'password');

        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }

        $status = $this->userRepository->update($id, $data);
        if (!empty($status)) {
            return back()->with('msg', __('user::messages.update.success'));
        } else {
            return back()->with('msg_danger', __('user::messages.update.failure'));
        }
    }

    public function delete($id)
    {
        $users = $this->userRepository->find($id);
        if (empty($users)) {
            abort(404);
        }
        $this->userRepository->delete($id);
        return back()->with('msg', __('user::messages.delete.success'));
    }
}
