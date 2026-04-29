<?php

namespace Modules\User\src\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\AdminSecurityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
    protected $adminSecurityService;

    public function __construct(UserRepositoryInterface $userRepository, AdminSecurityService $adminSecurityService)
    {
        $this->userRepository = $userRepository;
        $this->adminSecurityService = $adminSecurityService;
    }

    public function index()
    {
        $pageTitle = 'Quản lý người dùng';
        $groupOptions = Group::query()->orderBy('id')->get(['id', 'name', 'slug']);

        return view('user::lists', compact('pageTitle', 'groupOptions'));
    }

    public function trash()
    {
        $pageTitle = 'Thùng rác người dùng';

        return view('user::trash', compact('pageTitle'));
    }

    public function data(Request $request)
    {
        $users = $this->userRepository->getAllUser();
        $canLogs = auth()->user()?->hasPermission('users.logs');
        $canEdit = auth()->user()?->hasPermission('users.edit');
        $canDelete = auth()->user()?->canAnyPermission(['users.soft_delete', 'users.delete']);

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
            ->addColumn('user_info', function ($user) {
                $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=f1f5f9&color=64748b';
                
                return '
                    <div class="d-flex align-items-center gap-3">
                        <img src="' . $avatar . '" class="rounded-circle shadow-sm" style="width: 40px; height: 40px; object-fit: cover;">
                        <div>
                            <div class="fw-bold text-dark">' . e($user->name) . '</div>
                            <div class="text-muted small">' . e($user->email) . '</div>
                        </div>
                    </div>';
            })
            ->addColumn('security_group', function ($user) {
                $group = $user->group?->name ?: ('Nhóm #' . $user->group_id);
                
                $twoFactorBadge = (int) $user->two_factor_email_enabled === 1
                    ? '<span class="badge bg-primary-subtle text-primary px-2 py-1 ms-1">2FA On</span>'
                    : '<span class="badge bg-light text-muted border px-2 py-1 ms-1" style="font-size: 10px;">2FA Off</span>';

                $statusBadge = (int) $user->is_locked === 1
                    ? '<span class="badge bg-danger-subtle text-danger px-2 py-1 ms-1">Đã khóa</span>'
                    : '<span class="badge bg-success-subtle text-success px-2 py-1 ms-1">Hoạt động</span>';

                return '
                    <div>
                        <div class="fw-semibold text-secondary mb-1">' . e($group) . '</div>
                        <div class="d-flex align-items-center gap-1 mt-1">
                            ' . $statusBadge . '
                            ' . $twoFactorBadge . '
                        </div>
                    </div>';
            })
            ->editColumn('created_at', function ($user) {
                return '<div class="small text-muted">' . Carbon::parse($user->created_at)->format('d/m/Y') . '</div>';
            })
            ->addColumn('actions', function ($user) use ($canEdit, $canLogs, $canDelete) {
                $btn = '<div class="dropdown">
                            <button class="btn btn-light btn-sm border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2">';

                if ($canEdit) {
                    $btn .= '<li><a class="dropdown-item py-2" href="' . route('user.edit', $user->id) . '"><i class="fa-solid fa-user-pen text-primary me-2"></i>Chỉnh sửa</a></li>';
                    
                    if ((int) auth()->id() !== (int) $user->id) {
                        $label = (int) $user->is_locked === 1 ? 'Mở khóa' : 'Khóa tài khoản';
                        $icon = (int) $user->is_locked === 1 ? 'fa-solid fa-lock-open text-success' : 'fa-solid fa-lock text-warning';
                        $confirm = (int) $user->is_locked === 1 ? '' : 'onclick="return confirm(\'Khóa tài khoản này khỏi admin panel?\')"';
                        
                        $btn .= '<li>
                                    <form method="POST" action="' . route('user.toggle-lock', $user->id) . '" class="d-inline-block w-100" ' . $confirm . '>
                                        ' . csrf_field() . '
                                        <button type="submit" class="dropdown-item py-2"><i class="' . $icon . ' me-2"></i>' . $label . '</button>
                                    </form>
                                 </li>';
                    }
                }

                if ($canLogs) {
                    $btn .= '<li><a class="dropdown-item py-2" href="' . route('user.logs', $user->id) . '"><i class="fa-solid fa-clock-rotate-left text-muted me-2"></i>Lịch sử thao tác</a></li>';
                }

                if ($canDelete) {
                    $btn .= '<li><hr class="dropdown-divider my-1"></li>';
                    $btn .= '<li><a class="dropdown-item py-2 text-danger delete-action" href="' . route('user.delete', $user->id) . '"><i class="fa-solid fa-trash me-2"></i>Xóa tài khoản</a></li>';
                }

                $btn .= '</ul></div>';
                return $btn;
            })
            ->rawColumns(['select', 'user_info', 'security_group', 'created_at', 'actions'])
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

        if (in_array($action, ['lock', 'unlock'], true)) {
            $updatedCount = 0;

            foreach ($users as $user) {
                if ((int) auth()->id() === (int) $user->id) {
                    continue;
                }

                $newLockState = $action === 'lock';
                $oldState = (bool) $user->is_locked;

                $this->userRepository->update($user->id, ['is_locked' => $newLockState]);
                $updatedCount++;

                if ($newLockState) {
                    $this->logoutUserSessions($user->id);
                }

                activity_log(
                    action: $newLockState ? 'lock' : 'unlock',
                    subject: $user,
                    properties: [
                        'old' => ['is_locked' => $oldState],
                        'new' => ['is_locked' => $newLockState],
                    ],
                    logName: $newLockState ? 'Khóa hàng loạt' : 'Mở khóa hàng loạt',
                    description: $newLockState ? 'Khóa người dùng' : 'Mở khóa người dùng'
                );
            }

            if ($updatedCount === 0) {
                return back()->with('msg_danger', 'Không có người dùng hợp lệ để xử lý thao tác này.');
            }

            return back()->with('msg', $action === 'lock'
                ? 'Đã khóa các người dùng đã chọn.'
                : 'Đã mở khóa các người dùng đã chọn.');
        }

        return back()->with('msg_danger', 'Thao tác hàng loạt không hợp lệ.');
    }

    public function trashData()
    {
        $users = \Modules\User\src\Models\User::query()->onlyTrashed()->with(['group:id,name,slug,is_admin'])->latest('deleted_at');
        $canRestore = auth()->user()?->canAnyPermission(['users.soft_delete', 'users.delete']);
        $canForceDelete = auth()->user()?->hasPermission('users.force_delete');

        return DataTables::of($users)
            ->addColumn('select', fn($user) => '<div class="form-check m-0 d-flex justify-content-center"><input type="checkbox" class="form-check-input bulk-row-checkbox" value="' . $user->id . '"></div>')
            ->addColumn('group_name', fn($user) => e($user->group?->name ?: ('Nhóm #' . $user->group_id)))
            ->addColumn('deleted_at', fn($user) => Carbon::parse($user->deleted_at)->format('d/m/Y H:i:s'))
            ->addColumn('restore', function ($user) use ($canRestore) {
                if (!$canRestore) {
                    return '<span class="text-muted small">Không có quyền</span>';
                }

                return '<form method="POST" action="' . route('user.restore', $user->id) . '" class="d-inline-block">'
                    . csrf_field()
                    . '<button type="submit" class="btn btn-success btn-sm">Khôi phục</button>'
                    . '</form>';
            })
            ->addColumn('force_delete', function ($user) use ($canForceDelete) {
                if (!$canForceDelete) {
                    return '<span class="text-muted small">Không có quyền</span>';
                }

                return '<form method="POST" action="' . route('user.force-delete', $user->id) . '" class="d-inline-block" onsubmit="return confirm(\'Xóa vĩnh viễn người dùng này?\');">'
                    . csrf_field()
                    . method_field('DELETE')
                    . '<button type="submit" class="btn btn-outline-danger btn-sm">Xóa vĩnh viễn</button>'
                    . '</form>';
            })
            ->rawColumns(['select', 'restore', 'force_delete'])
            ->toJson();
    }

    public function trashBulkAction(Request $request)
    {
        $action = $request->input('bulk_action');
        $selectedIds = collect(explode(',', (string) $request->input('selected_ids', '')))
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($selectedIds->isEmpty()) {
            throw ValidationException::withMessages([
                'bulk_action' => 'Vui lòng chọn ít nhất một người dùng trong thùng rác.',
            ]);
        }

        $users = \Modules\User\src\Models\User::query()->onlyTrashed()->whereIn('id', $selectedIds)->get();

        if ($users->isEmpty()) {
            return back()->with('msg_danger', 'Không tìm thấy người dùng hợp lệ trong thùng rác.');
        }

        if ($action === 'restore') {
            foreach ($users as $user) {
                $user->restore();
            }

            return back()->with('msg', 'Đã khôi phục ' . $users->count() . ' người dùng.');
        }

        if ($action === 'force_delete') {
            foreach ($users as $user) {
                $this->logoutUserSessions($user->id);
                $user->forceDelete();
            }

            return back()->with('msg', 'Đã xóa vĩnh viễn ' . $users->count() . ' người dùng.');
        }

        return back()->with('msg_danger', 'Thao tác trong thùng rác không hợp lệ.');
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
            'is_locked' => (int) $request->input('is_locked', 0),
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
        if ((int) auth()->id() === (int) $id && (int) $request->input('is_locked') === 1) {
            return back()->with('msg_danger', 'Bạn không thể tự khóa chính tài khoản đang đăng nhập.');
        }

        $data = $request->except('_token', 'password');

        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }

        $old = $this->userRepository->find($id);
        $status = $this->userRepository->update($id, $data);

        if (!empty($status)) {
            $new = $this->userRepository->find($id);

            if ($new?->is_locked) {
                $this->logoutUserSessions($new->id);
            }

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

    public function toggleLock($id)
    {
        $user = $this->userRepository->find($id);

        if (empty($user)) {
            abort(404);
        }

        if ((int) auth()->id() === (int) $user->id) {
            return back()->with('msg_danger', 'Bạn không thể tự khóa chính tài khoản đang đăng nhập.');
        }

        $newState = !$user->is_locked;

        $this->userRepository->update($user->id, ['is_locked' => $newState]);

        if ($newState) {
            $this->logoutUserSessions($user->id);
        }

        activity_log(
            action: $newState ? 'lock' : 'unlock',
            subject: $user,
            properties: [
                'old' => ['is_locked' => (bool) $user->is_locked],
                'new' => ['is_locked' => $newState],
            ],
            logName: $newState ? 'Khóa tài khoản' : 'Mở khóa tài khoản',
            description: $newState ? 'Khóa người dùng khỏi admin panel' : 'Mở khóa người dùng vào admin panel'
        );

        return back()->with('msg', $newState
            ? 'Đã khóa người dùng khỏi admin panel.'
            : 'Đã mở khóa người dùng.');
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
        $activeAdminSessions = $this->adminSecurityService->activeSessionCount($user);

        $loginHistories = ActiveLog::query()
            ->where('subject_type', get_class($user))
            ->where('subject_id', $user->id)
            ->where(function ($query) {
                $query->where('log_name', 'auth_login')
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('log_name', 'admin_security')
                            ->whereIn('action', ['two_factor_code_sent', 'login_context_changed']);
                    });
            })
            ->latest()
            ->limit(8)
            ->get();

        $actionHistories = ActiveLog::query()
            ->where('causer_id', $user->id)
            ->whereNotIn('action', ['login', 'two_factor_code_sent', 'login_context_changed'])
            ->latest()
            ->limit(8)
            ->get();

        return view('user::show', compact('pageTitle', 'user', 'activeAdminSessions', 'loginHistories', 'actionHistories'));
    }

    public function showUpdate(UpdateProfileRequest $request)
    {
        $userId = auth()->id();
        $data = $request->except('_token', 'password');
        $passwordChanged = $request->filled('password');
        $newPassword = (string) $request->input('password');
        $oldUser = $this->userRepository->find($userId);

        if ($passwordChanged) {
            $data['password'] = bcrypt($newPassword);
        }

        $this->userRepository->update($userId, $data);
        $updatedUser = $this->userRepository->find($userId);

        if ($oldUser && $updatedUser) {
            $oldSnapshot = $oldUser->toArray();
            $newSnapshot = $updatedUser->toArray();

            unset($oldSnapshot['password'], $newSnapshot['password']);

            activity_log(
                action: 'update',
                subject: $updatedUser,
                properties: [
                    'old' => $oldSnapshot,
                    'new' => $newSnapshot,
                ],
                logName: 'Cập nhật hồ sơ admin',
                description: $passwordChanged
                    ? 'Cập nhật thông tin cá nhân và đổi mật khẩu admin'
                    : 'Cập nhật thông tin cá nhân admin'
            );
        }

        if ($passwordChanged) {
            $user = $updatedUser;

            activity_log(
                action: 'password_changed',
                subject: $user,
                properties: [
                    'changed_at' => now()->toDateTimeString(),
                ],
                logName: 'Đổi mật khẩu admin',
                description: 'Đổi mật khẩu tài khoản admin'
            );

            $this->adminSecurityService->logoutAllSessions($user);
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

    public function toggleTwoFactor(Request $request)
    {
        $user = auth()->user();
        $newState = !$user->two_factor_email_enabled;

        $user->forceFill([
            'two_factor_email_enabled' => $newState,
            'two_factor_email_enabled_at' => $newState ? now() : null,
            'two_factor_email_code' => null,
            'two_factor_email_code_expires_at' => null,
            'two_factor_email_code_sent_at' => null,
        ])->save();

        activity_log(
            action: $newState ? 'two_factor_enabled' : 'two_factor_disabled',
            subject: $user,
            properties: ['enabled' => $newState],
            logName: 'Bảo mật tài khoản',
            description: $newState ? 'Đã bật xác thực 2 lớp cho admin.' : 'Đã tắt xác thực 2 lớp cho admin.'
        );

        return back()->with('msg', $newState
            ? 'Đã bật xác thực 2 lớp qua email cho tài khoản admin.'
            : 'Đã tắt xác thực 2 lớp cho tài khoản admin.');
    }

    public function logoutAllSessions(Request $request)
    {
        $user = auth()->user();
        $this->adminSecurityService->logoutAllSessions($user);
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('msg', 'Đã đăng xuất tất cả các phiên admin.');
    }

    public function restore($id)
    {
        $user = \Modules\User\src\Models\User::query()->onlyTrashed()->find($id);

        if (!$user) {
            abort(404);
        }

        $user->restore();

        return back()->with('msg', 'Khôi phục người dùng thành công.');
    }

    public function forceDelete($id)
    {
        $user = \Modules\User\src\Models\User::query()->onlyTrashed()->find($id);

        if (!$user) {
            abort(404);
        }

        $this->logoutUserSessions($user->id);
        $user->forceDelete();

        return back()->with('msg', 'Đã xóa vĩnh viễn người dùng.');
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

    protected function logoutUserSessions(int $userId): void
    {
        $user = $this->userRepository->find($userId);

        if ($user) {
            $this->adminSecurityService->logoutAllSessions($user);
        }

        if (config('session.driver') !== 'database' || !Schema::hasTable(config('session.table', 'sessions'))) {
            return;
        }

        DB::table(config('session.table', 'sessions'))
            ->where('user_id', $userId)
            ->delete();
    }
}
