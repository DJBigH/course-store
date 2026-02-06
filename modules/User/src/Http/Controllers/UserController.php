<?php

namespace Modules\User\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\ActiveLogs\src\Models\ActiveLog;
use Modules\User\src\Http\Requests\UpdateProfileRequest;
use Modules\User\src\Http\Requests\UserRequest;
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
            ->addColumn('logs', function ($user) {
                return '<a href="' . route('user.logs', $user->id) . '" class="btn btn-info">Lịch sử</a>';
            })
            ->addColumn('edit', function ($user) {
                return '<a href="' . route('user.edit', $user->id) . '" class="btn btn-warning">Sửa</a>';
            })
            ->addColumn('delete', function ($user) {
                return '<a href="' . route('user.delete', $user->id) . '" class="btn btn-danger delete-action">Xóa</a>';
            })
            ->editColumn('created_at', function ($users) {
                return Carbon::parse($users->created_at)->format('d/m/Y H:i:s');
            })
            ->rawColumns(['edit', 'delete', 'logs'])
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
        $user = $this->userRepository->create($dataInsert);
        activity_log(
            action: 'create',
            subject: $user,
            properties: ['data' => $dataInsert],
            logName: 'Thêm mới',
            description: 'Tạo mới người dùng'
        );
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

        $old = $this->userRepository->find($id);

        $status = $this->userRepository->update($id, $data);

        if (!empty($status)) {
            $new = $this->userRepository->find($id);

            activity_log(
                action: 'update',
                subject: $new,
                properties: [
                    'old' => $old?->toArray(),
                    'new' => $new?->toArray(),
                ],
                logName: "Cập nhập",
                description: 'Cập nhật người dùng'
            );
            if (!empty($status)) {
                return back()->with('msg', __('user::messages.update.success'));
            } else {
                return back()->with('msg_danger', __('user::messages.update.failure'));
            }
        }
    }

    public function delete($id)
    {
        $users = $this->userRepository->find($id);
        if (empty($users)) {
            abort(404);
        }
        $user = $this->userRepository->delete($id);
        activity_log(
            action: 'delete',
            subject: $user,
            properties: ['data' => $user->toArray()],
            logName: 'Xóa',
            description: 'Xóa người dùng'
        );
        return back()->with('msg', __('user::messages.delete.success'));
    }

    public function show()
    {
        $pageTitle = 'Thông tin người dùng';
        $user = auth()->user();

        return view('user::show', compact('pageTitle', 'user'));
    }

    public function showUpdate(UpdateProfileRequest $request)
    {
        $userId = auth()->id();

        $data = $request->except('_token', 'password');

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $status =  $this->userRepository->update($userId, $data);
        return back()->with('msg', __('user::messages.update.success'));
    }

    public function logs(Request $request, $id)
    {
        $user = $this->userRepository->find($id);
        if (empty($user)) abort(404);

        $pageTitle = "Lịch sử: {$user->name}";

        $query = ActiveLog::query()
            ->where('subject_type', get_class($user))
            ->where('subject_id', $user->id);

        // 🔹 Filter theo action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // 🔹 Filter theo khoảng thời gian
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        // 🔹 Filter theo keyword (description hoặc log_name)
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('description', 'like', "%{$q}%")
                    ->orWhere('log_name', 'like', "%{$q}%");
            });
        }

        $logs = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('user::logs', compact('pageTitle', 'user', 'logs'));
    }
}
