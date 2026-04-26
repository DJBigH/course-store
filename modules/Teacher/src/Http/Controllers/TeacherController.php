<?php

namespace Modules\Teacher\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\ActiveLogs\src\Models\ActiveLog;
use Modules\Courses\src\Models\Courses;
use Modules\Teacher\src\Http\Requests\TeacherRequest;
use Modules\Teacher\src\Repositories\TeacherRepositoryInterface;
use Yajra\DataTables\Facades\DataTables;

class TeacherController extends Controller
{
    protected $teacherRepository;

    public function __construct(TeacherRepositoryInterface $teacherRepository)
    {
        $this->teacherRepository = $teacherRepository;
    }

    public function index()
    {
        $pageTitle = __('teacher::admin.titles.index');

        return view('teacher::lists', compact('pageTitle'));
    }

    public function trash()
    {
        $pageTitle = __('teacher::admin.titles.trash');

        return view('teacher::trash', compact('pageTitle'));
    }

    public function data(Request $request)
    {
        $teacher = $this->teacherRepository->getAllTeacher();
        $inactive30 = now()->subDays(30);
        $inactive60 = now()->subDays(60);

        if ($request->filled('q')) {
            $keyword = trim((string) $request->input('q'));

            $teacher->where(function ($query) use ($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('name_en', 'like', '%' . $keyword . '%')
                    ->orWhere('name_ko', 'like', '%' . $keyword . '%')
                    ->orWhere('name_ja', 'like', '%' . $keyword . '%')
                    ->orWhere('name_zh', 'like', '%' . $keyword . '%')
                    ->orWhere('slug', 'like', '%' . $keyword . '%')
                    ->orWhere('exp', 'like', '%' . $keyword . '%');
            });
        }

        if ($request->filled('profile_status')) {
            if ($request->input('profile_status') === 'has_image') {
                $teacher->whereNotNull('image')->where('image', '!=', '');
            }

            if ($request->input('profile_status') === 'missing_image') {
                $teacher->where(function ($query) {
                    $query->whereNull('image')->orWhere('image', '');
                });
            }
        }

        if ($request->filled('activity_status')) {
            $activityStatus = (string) $request->input('activity_status');

            if ($activityStatus === 'never_active') {
                $teacher->whereNull('last_active_at');
            }

            if ($activityStatus === 'active_30') {
                $teacher->whereNotNull('last_active_at')
                    ->where('last_active_at', '>=', $inactive30);
            }

            if ($activityStatus === 'inactive_30') {
                $teacher->where(function ($query) use ($inactive30) {
                    $query->where('last_active_at', '<', $inactive30)
                        ->orWhere(function ($subQuery) use ($inactive30) {
                            $subQuery->whereNull('last_active_at')
                                ->where('created_at', '<', $inactive30);
                        });
                });
            }

            if ($activityStatus === 'inactive_60') {
                $teacher->where(function ($query) use ($inactive60) {
                    $query->where('last_active_at', '<', $inactive60)
                        ->orWhere(function ($subQuery) use ($inactive60) {
                            $subQuery->whereNull('last_active_at')
                                ->where('created_at', '<', $inactive60);
                        });
                });
            }
        }

        if ($request->filled('from_date')) {
            $teacher->whereDate('created_at', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $teacher->whereDate('created_at', '<=', $request->input('to_date'));
        }

        $user = auth()->user();
        $canLogs = $user?->hasPermission('teachers.logs');
        $canEdit = $user?->hasPermission('teachers.edit');
        $canDelete = $user?->canAnyPermission(['teachers.soft_delete', 'teachers.delete']);
        $teacher = $teacher->latest();

        return DataTables::of($teacher)
            ->editColumn('name', function ($teacher) {
                return '<div>' . e($teacher->name_locale) . '</div>';
            })
            ->addColumn('teacher_status', function ($teacher) {
                if ($teacher->status === \Modules\Teacher\src\Models\Teacher::STATUS_CEASED) {
                    return '<span class="badge bg-secondary">
                                <i class="fa-solid fa-user-slash me-1"></i> ' . 'Đã huỷ hợp tác' . '
                            </span>';
                }

                $isLocked = intval($teacher->is_locked);
                if ($isLocked === 1) {
                    $reason = e($teacher->lock_reason);
                    $admin = e($teacher->lockedByAdmin?->name ?? __('teacher::admin.table.system_fallback'));
                    $time = $teacher->locked_at ? $teacher->locked_at->format('d/m/Y H:i') : '';
                    $tooltip = "Lý do: {$reason}\nNgười khóa: {$admin}\nThời gian: {$time}";
                    
                    return '<span class="badge bg-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="' . $tooltip . '">
                                <i class="fa-solid fa-user-lock me-1"></i> ' . __('teacher::admin.table.status_locked') . '
                            </span>';
                }

                return '<span class="badge bg-success">
                            <i class="fa-solid fa-check-circle me-1"></i> ' . __('teacher::admin.table.status_active') . '
                        </span>';
            })
            ->addColumn('rating', function ($teacher) {
                $avg = round((float) ($teacher->ratings_avg_rating ?? 0), 1);
                $count = (int) ($teacher->ratings_count ?? 0);
                
                return '<div class="teacher-rating-cell text-center">
                            <div class="rating-text fw-bold text-warning">
                                <i class="fa-solid fa-star me-1"></i>' . $avg . ' / 5
                            </div>
                            <div class="small text-muted">' . $count . ' đánh giá</div>
                        </div>';
            })
            ->addColumn('select', function ($teacher) {
                return '<div class="form-check m-0 d-flex justify-content-center"><input type="checkbox" class="form-check-input bulk-row-checkbox" value="' . $teacher->id . '"></div>';
            })
            ->addColumn('logs', function ($teacher) use ($canLogs) {
                return $canLogs ? '<a href="' . route('teacher.logs', $teacher->id) . '" class="btn btn-light border">' . __('teacher::admin.actions.logs') . '</a>' : '<span class="text-muted small">' . __('teacher::admin.actions.no_permission') . '</span>';
            })
            ->addColumn('edit', function ($teacher) {
                $btn = '';
                if (auth()->user()?->hasPermission('teachers.edit')) {
                    $btn .= '<a href="' . route('teacher-packages.grant', ['teacher_id' => $teacher->id]) . '" class="btn btn-warning btn-sm me-1" title="Tặng gói đặc quyền"><i class="fa-solid fa-gift"></i></a>';

                    if ($teacher->is_locked) {
                        $btn .= '<form action="' . route('teacher.toggle-lock', $teacher->id) . '" method="POST" class="d-inline-block me-1">' . csrf_field() . '<button type="submit" class="btn btn-success btn-sm" title="Mở khóa tài khoản" onclick="return confirm(\'Xác nhận mở khóa cho giáo viên này?\')"><i class="fa-solid fa-lock-open"></i></button></form>';
                    } else {
                        $btn .= '<button type="button" class="btn btn-danger btn-sm me-1 btn-lock-teacher" data-id="' . $teacher->id . '" data-name="' . e($teacher->name) . '" data-url="' . route('teacher.toggle-lock', $teacher->id) . '" title="Khóa tài khoản"><i class="fa-solid fa-lock"></i></button>';
                    }

                    if ($teacher->status === \Modules\Teacher\src\Models\Teacher::STATUS_CEASED) {
                        $btn .= '<form action="' . route('teacher.toggle-ceased', $teacher->id) . '" method="POST" class="d-inline-block me-1">' . csrf_field() . '<button type="submit" class="btn btn-outline-success btn-sm" title="Khôi phục hợp tác" onclick="return confirm(\'Khôi phục hợp tác với giảng viên này?\')"><i class="fa-solid fa-handshake-angle"></i></button></form>';
                    } else {
                        $btn .= '<form action="' . route('teacher.toggle-ceased', $teacher->id) . '" method="POST" class="d-inline-block me-1">' . csrf_field() . '<button type="submit" class="btn btn-outline-danger btn-sm" title="Huỷ hợp tác" onclick="return confirm(\'Bạn có chắc muốn huỷ hợp tác với giảng viên này?\')"><i class="fa-solid fa-user-slash"></i></button></form>';
                    }

                    $btn .= '<a href="' . route('teacher.edit', $teacher->id) . '" class="btn btn-primary btn-sm"><i class="fa-solid fa-pen-to-square"></i></a>';
                }
                return $btn ?: '<span class="text-muted small">' . __('teacher::admin.actions.no_permission') . '</span>';
            })
            ->addColumn('delete', function ($teacher) use ($canDelete) {
                return $canDelete ? '<a href="' . route('teacher.delete', $teacher->id) . '" class="btn btn-outline-danger delete-action">' . __('teacher::admin.actions.delete') . '</a>' : '<span class="text-muted small">' . __('teacher::admin.actions.no_permission') . '</span>';
            })
            ->editColumn('created_at', function ($teacher) {
                return Carbon::parse($teacher->created_at)->format('d/m/Y H:i:s');
            })
            ->addColumn('last_active_at', function ($teacher) {
                if ($teacher->last_active_at) {
                    return Carbon::parse($teacher->last_active_at)->format('d/m/Y H:i:s');
                }
                return '<span class="text-warning small">' . __('teacher::admin.table.not_active_yet') . '</span>';
            })
            ->addColumn('inactive_days', function ($teacher) {
                $reference = $teacher->last_active_at ?: $teacher->created_at;
                $days = Carbon::parse($reference)->diffInDays(now());
                $tone = 'activity-age--fresh';
                if ($days >= 90) $tone = 'activity-age--danger';
                elseif ($days >= 60) $tone = 'activity-age--warning';
                elseif ($days >= 30) $tone = 'activity-age--notice';

                if (!$teacher->last_active_at) {
                    return '<span class="activity-age ' . $tone . '">' . $days . ' ' . __('teacher::admin.table.days_unit') . '</span><div class="small text-muted">' . __('teacher::admin.table.never_active') . '</div>';
                }
                return '<span class="activity-age ' . $tone . '">' . ($days === 0 ? __('teacher::admin.table.today') : $days . ' ' . __('teacher::admin.table.days_unit')) . '</span>';
            })
            ->editColumn('image', function ($teacher) {
                return $teacher->image ? '<img src="' . $teacher->image . '" style="width: 80px; border-radius: 12px;">' : __('teacher::admin.table.no_image');
            })
            ->addColumn('badge', function ($teacher) {
                $badge = $teacher->primary_badge;
                if (!$badge) return '<span class="text-muted small">' . __('teacher::admin.table.no_badge') . '</span>';
                return '<span class="teacher-admin-badge teacher-admin-badge--' . e($badge['tone']) . '">' . e($badge['label']) . '</span>';
            })
            ->rawColumns(['select', 'edit', 'delete', 'image', 'logs', 'last_active_at', 'inactive_days', 'badge', 'name', 'teacher_status', 'rating'])
            ->toJson();
    }

    public function trashData()
    {
        $canRestore = auth()->user()?->canAnyPermission(['teachers.soft_delete', 'teachers.delete']);
        $canForceDelete = auth()->user()?->hasPermission('teachers.force_delete');
        $teachers = \Modules\Teacher\src\Models\Teacher::query()->onlyTrashed()->latest('deleted_at');

        return DataTables::of($teachers)
            ->addColumn('select', fn($teacher) => '<div class="form-check m-0 d-flex justify-content-center"><input type="checkbox" class="form-check-input bulk-row-checkbox" value="' . $teacher->id . '"></div>')
            ->editColumn('name', function ($teachers) {
                return '<div>' . e($teachers->name_locale) . '</div>';
            })
            ->addColumn('lock_status', function ($teachers) {
                if ($teachers->is_locked) {
                    return '<span class="badge bg-danger">Bị khóa</span>';
                }
                return '<span class="badge bg-success">Hoạt động</span>';
            })
            ->addColumn('image', function ($teacher) {
                return $teacher->image
                    ? '<img src="' . $teacher->image . '" style="width: 80px; border-radius: 12px;">'
                    : __('teacher::admin.table.no_image');
            })
            ->addColumn('deleted_at', fn($teacher) => Carbon::parse($teacher->deleted_at)->format('d/m/Y H:i:s'))
            ->addColumn('restore', function ($teacher) use ($canRestore) {
                if (!$canRestore) {
                    return '<span class="text-muted small">' . __('teacher::admin.actions.no_permission') . '</span>';
                }

                return '<form method="POST" action="' . route('teacher.restore', $teacher->id) . '" class="d-inline-block">'
                    . csrf_field()
                    . '<button type="submit" class="btn btn-success">' . __('teacher::admin.actions.restore') . '</button>'
                    . '</form>';
            })
            ->addColumn('force_delete', function ($teacher) use ($canForceDelete) {
                if (!$canForceDelete) {
                    return '<span class="text-muted small">' . __('teacher::admin.actions.no_permission') . '</span>';
                }

                return '<form method="POST" action="' . route('teacher.force-delete', $teacher->id) . '" class="d-inline-block" onsubmit="return confirm(\'' . __('teacher::admin.actions.confirm_force_delete') . '\');">'
                    . csrf_field()
                    . method_field('DELETE')
                    . '<button type="submit" class="btn btn-outline-danger">' . __('teacher::admin.actions.force_delete') . '</button>'
                    . '</form>';
            })
            ->rawColumns(['select', 'image', 'restore', 'force_delete', 'name', 'lock_status'])
            ->toJson();
    }

    public function create()
    {
        $pageTitle = __('teacher::admin.titles.create');

        return view('teacher::create', compact('pageTitle'));
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
                'bulk_action' => __('teacher::admin.messages.select_at_least_one'),
            ]);
        }

        $teachers = $selectedIds
            ->map(fn($id) => $this->teacherRepository->find($id))
            ->filter();

        if ($teachers->isEmpty()) {
            return back()->with('msg_danger', __('teacher::admin.messages.not_found'));
        }

        if ($action === 'delete') {
            foreach ($teachers as $teacher) {
                $snapshot = $teacher->toArray();
                unset($snapshot['password']);
                $image = $teacher->image;

                $teacher->delete();

                activity_log(
                    action: 'delete',
                    subject: $teacher,
                    properties: [
                        'data' => $snapshot,
                        'deleted_image' => $image ? basename($image) : null,
                    ],
                    logName: __('teacher::admin.logs.bulk_delete'),
                    description: __('teacher::admin.logs.bulk_delete_desc')
                );
            }

            return back()->with('msg', __('teacher::admin.messages.bulk_deleted', ['count' => $teachers->count()]));
        }

        return back()->with('msg_danger', __('teacher::admin.messages.invalid_bulk_action'));
    }

    public function store(TeacherRequest $request)
    {
        $data = $request->except(['_token']);
        $data = array_merge($data, $this->normalizeBadgePayload($request));
        $teacher = $this->teacherRepository->create($data);

        activity_log(
            action: 'create',
            subject: $teacher,
            properties: [
                'data' => array_diff_key($data, array_flip(['password'])),
            ],
            logName: __('teacher::admin.logs.create'),
            description: __('teacher::admin.logs.create_desc')
        );

        return redirect()->route('teacher.index')->with('msg', __('teacher::admin.messages.create_success'));
    }

    public function edit($id)
    {
        $pageTitle = __('teacher::admin.titles.edit');
        $teacher = $this->teacherRepository->find($id);

        if (empty($teacher)) {
            abort(404);
        }

        return view('teacher::edit', compact('teacher', 'pageTitle'));
    }

    public function update(TeacherRequest $request, $id)
    {
        $teacherModel = $this->teacherRepository->find($id);

        if (empty($teacherModel)) {
            abort(404);
        }

        $old = $teacherModel->toArray();
        $data = $request->except('_token');
        $data = array_merge($data, $this->normalizeBadgePayload($request));

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        } else {
            unset($data['password']);
        }

        $status = $this->teacherRepository->update($id, $data);

        if (!empty($status)) {
            $teacherFresh = $this->teacherRepository->find($id);
            $new = $teacherFresh ? $teacherFresh->toArray() : [];

            unset($old['password'], $new['password']);

            if (isset($old['description'])) {
                $old['description'] = formatHtmlForLog($old['description'], 120);
            }

            if (isset($new['description'])) {
                $new['description'] = formatHtmlForLog($new['description'], 120);
            }

            activity_log(
                action: 'update',
                subject: $teacherFresh ?? $teacherModel,
                properties: [
                    'old' => $old,
                    'new' => $new,
                ],
                logName: __('teacher::admin.logs.update'),
                description: __('teacher::admin.logs.update_desc')
            );

            return back()->with('msg', __('teacher::admin.messages.update_success'));
        }

        return back()->with('msg_danger', __('teacher::admin.messages.update_failure'));
    }

    private function normalizeBadgePayload(Request $request): array
    {
        $badgeKey = trim((string) $request->input('badge_key', 'none'));
        $badgeLabel = trim((string) $request->input('badge_label', ''));
        $badgeTone = trim((string) $request->input('badge_tone', 'slate'));

        $payload = [
            'badge_key' => null,
            'badge_label' => null,
            'badge_tone' => null,
            'is_verified_badge' => false,
            'is_premium_badge' => false,
        ];

        if ($badgeKey === '' || $badgeKey === 'none') {
            return $payload;
        }

        if ($badgeKey === 'verified') {
            $payload['badge_key'] = 'verified';
            $payload['is_verified_badge'] = true;

            return $payload;
        }

        if ($badgeKey === 'premium') {
            $payload['badge_key'] = 'premium';
            $payload['is_premium_badge'] = true;

            return $payload;
        }

        if ($badgeKey === 'custom') {
            $payload['badge_key'] = 'custom';
            $payload['badge_label'] = $badgeLabel !== '' ? $badgeLabel : 'Custom Badge';
            $payload['badge_tone'] = $badgeTone !== '' ? $badgeTone : 'slate';

            return $payload;
        }

        $payload['badge_key'] = $badgeKey;

        return $payload;
    }

    /**
     * Khóa hoặc mở khóa tài khoản giáo viên
     */
    public function toggleLock(Request $request, $id)
    {
        $teacher = $this->teacherRepository->find($id);
        if (!$teacher) {
            abort(404);
        }

        $isLocking = !$teacher->is_locked;

        if ($isLocking) {
            $request->validate([
                'lock_reason' => 'required|string|max:1000',
            ], [
                'lock_reason.required' => 'Vui lòng nhập lý do khóa tài khoản.',
            ]);
        }

        $teacher->update([
            'is_locked'   => $isLocking,
            'lock_reason' => $isLocking ? $request->lock_reason : null,
            'locked_at'   => $isLocking ? now() : null,
            'locked_by'   => $isLocking ? auth()->id() : null,
        ]);

        activity_log(
            action: $isLocking ? 'teacher_locked' : 'teacher_unlocked',
            subject: $teacher,
            properties: [
                'reason' => $teacher->lock_reason,
                'admin'  => auth()->user()?->name,
            ],
            logName: 'teacher_access',
            description: $isLocking 
                ? "Đã khóa quyền giáo viên của [{$teacher->name}] với lý do: {$teacher->lock_reason}"
                : "Đã mở khóa quyền giáo viên cho [{$teacher->name}]"
        );

        return back()->with('msg', $isLocking ? 'Đã khóa tài khoản giáo viên thành công.' : 'Đã mở khóa tài khoản giáo viên thành công.');
    }

    public function toggleCeased(Request $request, $id)
    {
        $teacher = $this->teacherRepository->find($id);
        if (!$teacher) {
            abort(404);
        }

        $isCeased = $teacher->status === \Modules\Teacher\src\Models\Teacher::STATUS_CEASED;
        $newStatus = $isCeased ? \Modules\Teacher\src\Models\Teacher::STATUS_ACTIVE : \Modules\Teacher\src\Models\Teacher::STATUS_CEASED;

        $teacher->update([
            'status' => $newStatus,
        ]);

        activity_log(
            action: $newStatus === \Modules\Teacher\src\Models\Teacher::STATUS_CEASED ? 'teacher_ceased' : 'teacher_restored',
            subject: $teacher,
            properties: [
                'admin'  => auth()->user()?->name,
            ],
            logName: 'teacher_partnership',
            description: $newStatus === \Modules\Teacher\src\Models\Teacher::STATUS_CEASED 
                ? "Đã huỷ hợp tác với giảng viên [{$teacher->name}]"
                : "Đã khôi phục hợp tác với giảng viên [{$teacher->name}]"
        );

        return back()->with('msg', $newStatus === \Modules\Teacher\src\Models\Teacher::STATUS_CEASED ? 'Đã huỷ hợp tác với giảng viên thành công.' : 'Đã khôi phục hợp tác với giảng viên thành công.');
    }

    public function delete($id)
    {
        $teacher = $this->teacherRepository->find($id);

        if (empty($teacher)) {
            abort(404);
        }

        $snapshot = $teacher->toArray();
        unset($snapshot['password']);
        $image = $teacher->image;

        $status = $teacher->delete();

        if ($status) {
            activity_log(
                action: 'delete',
                subject: $teacher,
                properties: [
                    'data' => $snapshot,
                    'deleted_image' => $image ? basename($image) : null,
                ],
                logName: __('teacher::admin.logs.delete'),
                description: __('teacher::admin.logs.delete_desc')
            );

            return back()->with('msg', __('teacher::admin.messages.delete_success'));
        }

        return back()->with('msg_danger', __('teacher::admin.messages.delete_failed'));
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
                'bulk_action' => __('teacher::admin.messages.select_at_least_one'),
            ]);
        }

        $teachers = \Modules\Teacher\src\Models\Teacher::query()->onlyTrashed()->whereIn('id', $selectedIds)->get();

        if ($teachers->isEmpty()) {
            return back()->with('msg_danger', __('teacher::admin.messages.not_found'));
        }

        if ($action === 'restore') {
            foreach ($teachers as $teacher) {
                $teacher->restore();
            }

            return back()->with('msg', __('teacher::admin.messages.restore_success'));
        }

        if ($action === 'force_delete') {
            $teacherHasCourses = $teachers->first(fn($teacher) => $this->teacherHasCourses($teacher->id));

            if ($teacherHasCourses) {
                return back()->with('msg_danger', __('teacher::admin.messages.cannot_delete_has_courses'));
            }

            foreach ($teachers as $teacher) {
                if ($teacher->image) {
                    deleteFileStorage($teacher->image);
                }

                $teacher->forceDelete();
            }

            return back()->with('msg', __('teacher::admin.messages.force_delete_success'));
        }

        return back()->with('msg_danger', __('teacher::admin.messages.invalid_bulk_action'));
    }

    public function restore($id)
    {
        $teacher = \Modules\Teacher\src\Models\Teacher::query()->onlyTrashed()->find($id);

        if (!$teacher) {
            abort(404);
        }

        $teacher->restore();

        return back()->with('msg', __('teacher::admin.messages.restore_success'));
    }

    public function forceDelete($id)
    {
        $teacher = \Modules\Teacher\src\Models\Teacher::query()->onlyTrashed()->find($id);

        if (!$teacher) {
            abort(404);
        }

        if ($this->teacherHasCourses($teacher->id)) {
            return back()->with('msg_danger', __('teacher::admin.messages.cannot_delete_has_courses'));
        }

        if ($teacher->image) {
            deleteFileStorage($teacher->image);
        }

        $teacher->forceDelete();

        return back()->with('msg', __('teacher::admin.messages.force_delete_success'));
    }

    public function logs(Request $request, $id)
    {
        $teacher = $this->teacherRepository->find($id);

        if (empty($teacher)) {
            abort(404);
        }

        $pageTitle = __('teacher::admin.titles.logs', ['name' => $teacher->name]);

        $query = ActiveLog::query()
            ->where('subject_type', get_class($teacher))
            ->where('subject_id', $teacher->id)
            ->withoutGlobalScopes();

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

        return view('teacher::logs', compact('pageTitle', 'teacher', 'logs'));
    }

    protected function teacherHasCourses(int $teacherId): bool
    {
        return Courses::query()->withTrashed()->where('teacher_id', $teacherId)->exists();
    }

    /**
     * API: Trả về danh sách khóa học của giảng viên
     */
    public function getCourses(int $id)
    {
        $courses = Courses::query()
            ->where('teacher_id', $id)
            ->where('status', 1)
            ->where('is_learning_locked', '!=', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'sale_price']);

        return response()->json($courses);
    }
}
