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
            ->editColumn('name', function ($user) {
                $statusBadge = (int) $user->is_locked === 1
                    ? '<span class="badge rounded-pill ms-2" style="background:#dc2626;color:#fff;border:1px solid #dc2626;">Đã khóa</span>'
                    : '<span class="badge rounded-pill ms-2" style="background:#16a34a;color:#fff;border:1px solid #16a34a;">Hoạt động</span>';

                return '<div class="fw-semibold">' . e($user->name) . '</div>' . $statusBadge;
            })
            ->addColumn('two_factor', function ($user) {
                return (int) $user->two_factor_email_enabled === 1
                    ? '<span class="badge rounded-pill" style="background:#2563eb;color:#fff;border:1px solid #2563eb;">Đã bật 2FA</span>'
                    : '<span class="badge rounded-pill bg-light text-dark border">Chưa bật</span>';
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
            ->addColumn('lock', function ($user) use ($canEdit) {
                if (!$canEdit) {
                    return '<span class="text-muted small">Không có quyền</span>';
                }

                if ((int) auth()->id() === (int) $user->id) {
                    return '<span class="badge rounded-pill bg-light text-dark border">Tài khoản hiện tại</span>';
                }

                $label = (int) $user->is_locked === 1 ? 'Mở khóa' : 'Khóa';
                $class = (int) $user->is_locked === 1 ? 'btn btn-success' : 'btn btn-outline-secondary';
                $confirm = (int) $user->is_locked === 1
                    ? ''
                    : ' onsubmit="return confirm(\'Bạn có chắc chắn muốn khóa tài khoản này khỏi admin panel không?\')"';

                return '<form method="POST" action="' . route('user.toggle-lock', $user->id) . '" class="d-inline-block"' . $confirm . '>'
                    . csrf_field()
                    . '<button type="submit" class="' . $class . '">' . $label . '</button>'
                    . '</form>';
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
            ->rawColumns(['select', 'name', 'two_factor', 'edit', 'lock', 'delete', 'logs'])
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

        return view('user::show', compact('pageTitle', 'user', 'activeAdminSessions'));
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
            $user = $this->userRepository->find($userId);
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
