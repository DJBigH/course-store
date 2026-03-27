<?php

namespace Modules\User\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Modules\ActiveLogs\src\Models\ActiveLog;
use Modules\User\src\Http\Requests\UpdateProfileRequest;
use Modules\User\src\Http\Requests\UserRequest;
use Modules\User\src\Models\Group;
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
        $groupOptions = Group::query()->orderBy('id')->get(['id', 'name', 'slug']);

        return view('user::lists', compact('pageTitle', 'groupOptions'));
    }

    public function data(Request $request)
    {
        $users = $this->userRepository->getAllUser();
        $canLogs = auth()->user()?->hasPermission('users.logs');
        $canEdit = auth()->user()?->hasPermission('users.edit');
        $canDelete = auth()->user()?->hasPermission('users.delete');

        if ($request->filled('q')) {
            $keyword = trim((string) $request->input('q'));

            $users->where(function ($query) use ($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('email', 'like', '%' . $keyword . '%');
            });
        }

        if ($request->filled('group_filter')) {
            $users->where('group_id', (int) $request->input('group_filter'));
        }

        if ($request->filled('from_date')) {
            $users->whereDate('created_at', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $users->whereDate('created_at', '<=', $request->input('to_date'));
        }

        return DataTables::of($users)
            ->addColumn('select', function ($user) {
                return '<div class="form-check m-0 d-flex justify-content-center"><input type="checkbox" class="form-check-input bulk-row-checkbox" value="' . $user->id . '"></div>';
            })
            ->addColumn('logs', function ($user) use ($canLogs) {
                return $canLogs
                    ? '<a href="' . route('user.logs', $user->id) . '" class="btn btn-light border">Lịch sử</a>'
                    : '<span class="text-muted small">Không có quyền</span>';
            })
            ->addColumn('edit', function ($user) use ($canEdit) {
                return $canEdit
                    ? '<a href="' . route('user.edit', $user->id) . '" class="btn btn-warning">Sửa</a>'
                    : '<span class="text-muted small">Không có quyền</span>';
            })
            ->addColumn('delete', function ($user) use ($canDelete) {
                return $canDelete
                    ? '<a href="' . route('user.delete', $user->id) . '" class="btn btn-outline-danger delete-action">Xóa</a>'
                    : '<span class="text-muted small">Không có quyền</span>';
            })
            ->editColumn('group_id', function ($user) {
                return $user->group?->name ?: ('Nhóm #' . $user->group_id);
            })
            ->editColumn('created_at', function ($user) {
                return Carbon::parse($user->created_at)->format('d/m/Y H:i:s');
            })
            ->rawColumns(['select', 'edit', 'delete', 'logs'])
            ->toJson();
    }

    public function bulkAction(Request $request)
    {
        $action = $request->input('bulk_action');
        $selectedIds = collect(explode(',', (string) $request->input('selected_ids', '')))
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($selectedIds->isEmpty()) {
            throw ValidationException::withMessages([
                'bulk_action' => 'Vui lòng chọn ít nhất một người dùng.',
            ]);
        }

        $users = $selectedIds
            ->map(fn($id) => $this->userRepository->find($id))
            ->filter();

        if ($users->isEmpty()) {
            return back()->with('msg_danger', 'Không tìm thấy người dùng để xử lý.');
        }

        if ($action === 'delete') {
            foreach ($users as $user) {
                $snapshot = $user->toArray();
                unset($snapshot['password']);

                $this->userRepository->delete($user->id);

                activity_log(
                    action: 'delete',
                    subject: $user,
                    properties: ['data' => $snapshot],
                    logName: 'Xóa hàng loạt',
                    description: 'Xóa người dùng'
                );
            }

            return back()->with('msg', 'Đã xóa ' . $users->count() . ' người dùng.');
        }

        return back()->with('msg_danger', 'Thao tác hàng loạt không hợp lệ.');
    }

    public function create()
    {
        $pageTitle = 'Thêm mới người dùng';
        $groups = Group::query()->orderBy('id')->get(['id', 'name']);

        return view('user::create', compact('pageTitle', 'groups'));
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

        return redirect()->route('user.index')->with('msg', 'Đã tạo người dùng thành công.');
    }

    public function edit($id)
    {
        $pageTitle = 'Cập nhật người dùng';
        $users = $this->userRepository->find($id);
        $groups = Group::query()->orderBy('id')->get(['id', 'name']);

        if (empty($users)) {
            abort(404);
        }

        return view('user::edit', compact('users', 'pageTitle', 'groups'));
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
                logName: 'Cập nhật',
                description: 'Cập nhật người dùng'
            );

            return back()->with('msg', 'Đã cập nhật người dùng thành công.');
        }

        return back()->with('msg_danger', 'Cập nhật người dùng thất bại.');
    }

    public function delete($id)
    {
        $users = $this->userRepository->find($id);

        if (empty($users)) {
            abort(404);
        }

        $snapshot = $users->toArray();
        unset($snapshot['password']);

        $this->userRepository->delete($id);

        activity_log(
            action: 'delete',
            subject: $users,
            properties: ['data' => $snapshot],
            logName: 'Xóa',
            description: 'Xóa người dùng'
        );

        return back()->with('msg', 'Đã xóa người dùng thành công.');
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
        $passwordChanged = $request->filled('password');
        $newPassword = (string) $request->input('password');

        if ($passwordChanged) {
            $data['password'] = bcrypt($newPassword);
        }

        $this->userRepository->update($userId, $data);

        if ($passwordChanged) {
            Auth::logoutOtherDevices($newPassword);
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('msg', 'Đổi mật khẩu thành công. Vui lòng đăng nhập lại.');
        }

        return back()->with('msg', 'Cập nhật thông tin thành công.');
    }

    public function logs(Request $request, $id)
    {
        $user = $this->userRepository->find($id);

        if (empty($user)) {
            abort(404);
        }

        $pageTitle = "Lịch sử: {$user->name}";

        $query = ActiveLog::query()
            ->where('subject_type', get_class($user))
            ->where('subject_id', $user->id);

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('description', 'like', "%{$q}%")
                    ->orWhere('log_name', 'like', "%{$q}%");
            });
        }

        $logs = $query
            ->latest()
            ->paginate(config('paginate.log_limit'))
            ->withQueryString();

        return view('user::logs', compact('pageTitle', 'user', 'logs'));
    }
}
