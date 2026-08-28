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
use Modules\Teacher\src\Models\TeacherBadge;
use Modules\Teacher\src\Models\TeacherTelegramSubscription;
use Modules\Orders\src\Models\Order;
use App\Jobs\SendTelegramTeacherNotification;
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
                $avatarUrl = $teacher->image ?: asset('resources/assets/teacher.png');
                $badgeHtml = '';
                $badge = $teacher->primary_badge;
                if ($badge) {
                    $badgeHtml = '<span class="teacher-admin-badge teacher-admin-badge--' . e($badge['tone']) . ' ms-2" style="font-size: 0.65rem; padding: 0.15rem 0.5rem; letter-spacing: 0;">' . e($badge['label']) . '</span>';
                }
                
                return '<div class="d-flex align-items-center gap-3">
                            <img src="' . $avatarUrl . '" class="rounded-circle shadow-sm border border-2 border-white" style="width: 44px; height: 44px; object-fit: cover;">
                            <div>
                                <div class="fw-bold text-dark d-flex align-items-center">' . e($teacher->name_locale) . $badgeHtml . '</div>
                                <div class="text-muted small" style="font-size: 0.75rem;">' . e($teacher->slug) . '</div>
                            </div>
                        </div>';
            })
            ->addColumn('teacher_status', function ($teacher) {
                if ($teacher->status === \Modules\Teacher\src\Models\Teacher::STATUS_CEASED) {
                    return '<span class="badge bg-secondary rounded-pill px-3 py-2" style="font-size: 0.75rem;">
                                <i class="fa-solid fa-user-slash me-1"></i> ' . 'Đã huỷ hợp tác' . '
                            </span>';
                }

                $isLocked = intval($teacher->is_locked);
                if ($isLocked === 1) {
                    $reason = e($teacher->lock_reason);
                    $admin = e($teacher->lockedByAdmin?->name ?? __('teacher::admin.table.system_fallback'));
                    $time = $teacher->locked_at ? $teacher->locked_at->format('d/m/Y H:i') : '';
                    $tooltip = "Lý do: {$reason}\nNgười khóa: {$admin}\nThời gian: {$time}";
                    
                    return '<span class="badge bg-danger rounded-pill px-3 py-2" style="font-size: 0.75rem;" data-bs-toggle="tooltip" data-bs-placement="top" title="' . $tooltip . '">
                                <i class="fa-solid fa-user-lock me-1"></i> ' . __('teacher::admin.table.status_locked') . '
                            </span>';
                }

                return '<span class="badge bg-success rounded-pill px-3 py-2" style="font-size: 0.75rem;">
                            <i class="fa-solid fa-check-circle me-1"></i> ' . __('teacher::admin.table.status_active') . '
                        </span>';
            })
            ->addColumn('exp_rating', function ($teacher) {
                $avg = round((float) ($teacher->ratings_avg_rating ?? 0), 1);
                $count = (int) ($teacher->ratings_count ?? 0);
                
                return '<div class="teacher-exp-rating-cell small">
                            <div class="fw-bold text-dark"><i class="fa-solid fa-briefcase text-muted me-1"></i> ' . e($teacher->exp) . ' năm</div>
                            <div class="text-warning mt-1">
                                <i class="fa-solid fa-star me-1"></i><strong>' . $avg . '</strong> <span class="text-muted">(' . $count . ' đánh giá)</span>
                            </div>
                        </div>';
            })
            ->addColumn('activity_timeline', function ($teacher) {
                $reference = $teacher->last_active_at ?: $teacher->created_at;
                $days = Carbon::parse($reference)->diffInDays(now());
                $tone = 'activity-age--fresh';
                if ($days >= 90) $tone = 'activity-age--danger';
                elseif ($days >= 60) $tone = 'activity-age--warning';
                elseif ($days >= 30) $tone = 'activity-age--notice';

                $created = Carbon::parse($teacher->created_at)->format('d/m/Y');
                $lastActive = $teacher->last_active_at 
                    ? Carbon::parse($teacher->last_active_at)->format('d/m/Y H:i') 
                    : '<span class="text-warning">' . __('teacher::admin.table.never_active') . '</span>';
                
                $daysText = $days === 0 ? __('teacher::admin.table.today') : $days . ' ' . __('teacher::admin.table.days_unit');

                return '<div class="teacher-activity-timeline small">
                            <div class="mb-1"><span class="text-muted">Tham gia:</span> <span class="fw-bold text-dark">' . $created . '</span></div>
                            <div class="mb-2"><span class="text-muted">Gần nhất:</span> ' . $lastActive . '</div>
                            <div><span class="activity-age ' . $tone . '">' . $daysText . '</span></div>
                        </div>';
            })
            ->addColumn('telegram_package', function ($teacher) {
                $status = $teacher->getTelegramPackageStatus();
                if ($status['status'] === 'active') {
                    $date = $status['expires_at'] ? $status['expires_at']->format('d/m/Y') : '';
                    return '<span class="badge bg-info bg-opacity-10 text-info border border-info rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                                <i class="fa-brands fa-telegram me-1"></i>Đến ' . $date . '
                            </span>';
                }
                return '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                            <i class="fa-solid fa-ban me-1"></i>Không có
                        </span>';
            })
            ->addColumn('select', function ($teacher) {
                return '<div class="form-check m-0 d-flex justify-content-center"><input type="checkbox" class="form-check-input bulk-row-checkbox" value="' . $teacher->id . '"></div>';
            })
            ->addColumn('actions', function ($teacher) use ($canLogs) {
                $btn = '<div class="dropdown">
                            <button class="btn btn-light btn-sm border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2">';
                
                if (auth()->user()?->hasPermission('teachers.edit')) {
                    $btn .= '<li><a class="dropdown-item py-2" href="' . route('teacher.edit', $teacher->id) . '"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Chỉnh sửa</a></li>';
                    $btn .= '<li><a class="dropdown-item py-2" href="' . route('teacher-packages.grant', ['teacher_id' => $teacher->id]) . '"><i class="fa-solid fa-gift text-warning me-2"></i>Tặng gói đặc quyền</a></li>';
                    $btn .= '<li><a class="dropdown-item py-2" href="' . route('teacher.edit', $teacher->id) . '#badges-assignment-section"><i class="fa-solid fa-award text-info me-2"></i>Cấp huy hiệu</a></li>';

                    if ($teacher->is_locked) {
                        $btn .= '<li>
                                    <form action="' . route('teacher.toggle-lock', $teacher->id) . '" method="POST" class="d-inline-block w-100">' . csrf_field() . '
                                        <button type="submit" class="dropdown-item py-2" onclick="return confirm(\'Xác nhận mở khóa cho giáo viên này?\')">
                                            <i class="fa-solid fa-lock-open text-success me-2"></i>Mở khóa
                                        </button>
                                    </form>
                                 </li>';
                    } else {
                        $btn .= '<li>
                                    <button type="button" class="dropdown-item py-2 btn-lock-teacher" data-id="' . $teacher->id . '" data-name="' . e($teacher->name) . '" data-url="' . route('teacher.toggle-lock', $teacher->id) . '">
                                        <i class="fa-solid fa-lock text-danger me-2"></i>Khóa tài khoản
                                    </button>
                                 </li>';
                    }

                    if ($teacher->status === \Modules\Teacher\src\Models\Teacher::STATUS_CEASED) {
                        $btn .= '<li>
                                    <form action="' . route('teacher.toggle-ceased', $teacher->id) . '" method="POST" class="d-inline-block w-100">' . csrf_field() . '
                                        <button type="submit" class="dropdown-item py-2" onclick="return confirm(\'Khôi phục hợp tác với giảng viên này?\')">
                                            <i class="fa-solid fa-handshake-angle text-success me-2"></i>Khôi phục hợp tác
                                        </button>
                                    </form>
                                 </li>';
                    } else {
                        $btn .= '<li>
                                    <form action="' . route('teacher.toggle-ceased', $teacher->id) . '" method="POST" class="d-inline-block w-100">' . csrf_field() . '
                                        <button type="submit" class="dropdown-item py-2" onclick="return confirm(\'Bạn có chắc muốn huỷ hợp tác với giảng viên này?\')">
                                            <i class="fa-solid fa-user-slash text-secondary me-2"></i>Huỷ hợp tác
                                        </button>
                                    </form>
                                 </li>';
                    }
                }

                if ($canLogs) {
                    $btn .= '<li><hr class="dropdown-divider my-1"></li>';
                    $btn .= '<li><a class="dropdown-item py-2" href="' . route('teacher.logs', $teacher->id) . '"><i class="fa-solid fa-clock-rotate-left text-muted me-2"></i>Xem lịch sử</a></li>';
                }

                if (auth()->user()?->canAnyPermission(['teachers.soft_delete', 'teachers.delete'])) {
                    $btn .= '<li><hr class="dropdown-divider my-1"></li>';
                    $btn .= '<li><a class="dropdown-item py-2 text-danger delete-action" href="' . route('teacher.delete', $teacher->id) . '"><i class="fa-solid fa-trash me-2"></i>Xóa mềm</a></li>';
                }

                $btn .= '</ul></div>';
                return $btn;
            })
            ->rawColumns(['select', 'actions', 'name', 'teacher_status', 'exp_rating', 'activity_timeline', 'telegram_package'])
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
        $badges = TeacherBadge::where('is_active', true)->get();

        return view('teacher::create', compact('pageTitle', 'badges'));
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

        if ($request->has('badges')) {
            $teacher->badges()->sync($request->badges);
        }

        return redirect()->route('teacher.index')->with('msg', __('teacher::admin.messages.create_success'));
    }

    public function edit($id)
    {
        $pageTitle = __('teacher::admin.titles.edit');
        $teacher = $this->teacherRepository->find($id);

        if (empty($teacher)) {
            abort(404);
        }

        $badges = TeacherBadge::where('is_active', true)->get();

        return view('teacher::edit', compact('teacher', 'pageTitle', 'badges'));
    }

    public function update(TeacherRequest $request, $id)
    {
        $teacherModel = $this->teacherRepository->find($id);

        if (empty($teacherModel)) {
            abort(404);
        }

        $old = $teacherModel->toArray();
        $data = $request->except('_token', 'telegram_duration_value', 'telegram_duration_unit');
        $data = array_merge($data, $this->normalizeBadgePayload($request));

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        } else {
            unset($data['password']);
        }

        // Handle Telegram Package Extension
        if ($request->filled('telegram_duration_value') && $request->filled('telegram_duration_unit')) {
            $value = (int) $request->input('telegram_duration_value');
            $unit = $request->input('telegram_duration_unit');
            
            $newExpiry = $teacherModel->addTelegramDuration($value, $unit);

            // Log as Order for history
            $order = Order::create([
                'code' => 'ADMIN' . strtoupper(uniqid()),
                'student_id' => $teacherModel->student_id,
                'total' => 0,
                'status_id' => 2, // Success
                'type' => 'telegram_package',
                'payment_method' => 'gift',
                'payment_complete_date' => now(),
                'currency' => 'VND'
            ]);

            // Notify Teacher via Telegram if active
            if ($teacherModel->hasTelegramFeature()) {
                $unitLabel = match($unit) {
                    'day' => 'ngày',
                    'month' => 'tháng',
                    'year' => 'năm',
                    default => $unit
                };
                $msg = "🎁 <b>QUÀ TẶNG TỪ HỆ THỐNG!</b>\n\n";
                $msg .= "Quản trị viên vừa gia hạn gói Telegram cho bạn thêm: <b>{$value} {$unitLabel}</b>\n";
                $msg .= "📅 <b>Hết hạn mới:</b> " . $newExpiry->format('d/m/Y');
                
                dispatch(new SendTelegramTeacherNotification($teacherModel, $msg));
            }
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

            if ($request->has('badges')) {
                try {
                    $oldBadgeIds = $teacherModel->badges->pluck('id')->toArray();
                    $newBadgeIds = array_map('intval', $request->badges);
                    $addedBadgeIds = array_diff($newBadgeIds, $oldBadgeIds);

                    $teacherModel->badges()->sync($newBadgeIds);

                    if (!empty($addedBadgeIds)) {
                        $addedBadges = TeacherBadge::whereIn('id', $addedBadgeIds)->get();
                        if ($teacherModel->student) {
                            $teacherModel->student->notify(new \App\Notifications\BadgeAssignmentNotification($teacherModel, $addedBadges, 'success'));
                        }

                        // Notify via Telegram
                        if ($teacherModel->hasTelegramFeature()) {
                            $badgeNames = $addedBadges->map(fn($b) => "🏆 <b>{$b->name_locale}</b>")->implode("\n");
                            $msg = "🌟 <b>CHÚC MỪNG BẠN ĐÃ NHẬN HUY HIỆU MỚI!</b>\n\n";
                            $msg .= "Bạn vừa được quản trị viên cấp các huy hiệu:\n{$badgeNames}\n\n";
                            $msg .= "Hãy truy cập bảng điều khiển để xem ngay nhé!";
                            
                            dispatch(new SendTelegramTeacherNotification($teacherModel, $msg));
                        }
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Badge update error: ' . $e->getMessage());
                    if ($teacherModel->student) {
                        $teacherModel->student->notify(new \App\Notifications\BadgeAssignmentNotification($teacherModel, [], 'failure', $e->getMessage()));
                    }
                }
            } else {
                $teacherModel->badges()->sync([]);
            }

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

        // Notify Teacher
        if ($teacher->student) {
            $type = $isLocking ? 'locked' : 'unlocked';
            $teacher->student->notify(new \App\Notifications\TeacherAccountStatusNotification($teacher, $type));
        }

        // Notify via Telegram
        if ($teacher->hasTelegramFeature()) {
            $statusTitle = $isLocking ? "🔒 <b>TÀI KHOẢN CỦA BẠN ĐÃ BỊ KHÓA</b>" : "🔓 <b>TÀI KHOẢN CỦA BẠN ĐÃ ĐƯỢC MỞ KHÓA</b>";
            $msg = "{$statusTitle}\n\n";
            if ($isLocking) {
                $msg .= "⚠️ <b>Lý do:</b> {$teacher->lock_reason}\n";
                $msg .= "Vui lòng liên hệ quản trị viên để biết thêm chi tiết.";
            } else {
                $msg .= "Chào mừng bạn đã trở lại! Bạn hiện đã có thể tiếp tục các hoạt động trên hệ thống.";
            }
            
            dispatch(new SendTelegramTeacherNotification($teacher, $msg));
        }

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

        // Notify Teacher
        if ($teacher->student) {
            $type = $newStatus === \Modules\Teacher\src\Models\Teacher::STATUS_CEASED ? 'ceased' : 'restored';
            $teacher->student->notify(new \App\Notifications\TeacherAccountStatusNotification($teacher, $type));
        }

        // Notify via Telegram
        if ($teacher->hasTelegramFeature()) {
            $statusTitle = ($newStatus === \Modules\Teacher\src\Models\Teacher::STATUS_CEASED) 
                ? "🤝 <b>HỢP TÁC TẠM DỪNG</b>" 
                : "🤝 <b>HỢP TÁC ĐÃ ĐƯỢC KHÔI PHỤC</b>";
            
            $msg = "{$statusTitle}\n\n";
            if ($newStatus === \Modules\Teacher\src\Models\Teacher::STATUS_CEASED) {
                $msg .= "Hệ thống đã tạm dừng hợp tác với tài khoản của bạn. Vui lòng liên hệ quản trị viên nếu có thắc mắc.";
            } else {
                $msg .= "Chào mừng bạn đã trở lại! Quan hệ hợp tác của bạn đã được khôi phục thành công.";
            }
            
            dispatch(new SendTelegramTeacherNotification($teacher, $msg));
        }

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

                activity_log(
                    action: 'restore',
                    subject: $teacher->fresh(),
                    properties: ['restored_from_trash' => true],
                    logName: __('teacher::admin.logs.bulk_restore') ?? 'Khôi phục hàng loạt',
                    description: __('teacher::admin.logs.bulk_restore_desc') ?? 'Khôi phục giảng viên'
                );
            }

            return back()->with('msg', __('teacher::admin.messages.restore_success'));
        }

        if ($action === 'force_delete') {
            $teacherHasCourses = $teachers->first(fn($teacher) => $this->teacherHasCourses($teacher->id));

            if ($teacherHasCourses) {
                return back()->with('msg_danger', __('teacher::admin.messages.cannot_delete_has_courses'));
            }

            foreach ($teachers as $teacher) {
                $snapshot = method_exists($teacher, 'toArray') ? $teacher->toArray() : (array) $teacher;
                if ($teacher->image) {
                    deleteFileStorage($teacher->image);
                }

                $teacher->forceDelete();

                activity_log(
                    action: 'force_delete',
                    subject: $teacher,
                    properties: [
                        'data' => $snapshot,
                        'deleted_permanently' => true,
                    ],
                    logName: __('teacher::admin.logs.bulk_force_delete') ?? 'Xóa vĩnh viễn hàng loạt',
                    description: __('teacher::admin.logs.bulk_force_delete_desc') ?? 'Xóa vĩnh viễn giảng viên'
                );
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

        activity_log(
            action: 'restore',
            subject: $teacher->fresh(),
            properties: ['restored_from_trash' => true],
            logName: __('teacher::admin.logs.restore') ?? 'Khôi phục',
            description: __('teacher::admin.logs.restore_desc') ?? 'Khôi phục giảng viên thành công.'
        );

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

        $snapshot = method_exists($teacher, 'toArray') ? $teacher->toArray() : (array) $teacher;
        $teacher->forceDelete();

        activity_log(
            action: 'force_delete',
            subject: $teacher,
            properties: [
                'data' => $snapshot,
                'deleted_permanently' => true,
            ],
            logName: __('teacher::admin.logs.force_delete') ?? 'Xóa vĩnh viễn',
            description: __('teacher::admin.logs.force_delete_desc') ?? 'Xóa vĩnh viễn giảng viên'
        );

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

    public function testTelegram($id)
    {
        $teacher = $this->teacherRepository->find($id);

        if (empty($teacher) || empty($teacher->telegram_chat_id)) {
            return response()->json(['success' => false, 'message' => 'Giáo viên chưa cấu hình Telegram Chat ID.']);
        }

        try {
            $botToken = config('services.telegram.bot_token');
            if (!$botToken) {
                return response()->json(['success' => false, 'message' => 'Chưa cấu hình Telegram Bot Token trong .env']);
            }

            $text = "🔔 <b>Hệ thống Giáo dục Đa Ngôn Ngữ</b>\n\n";
            $text .= "Xin chào <b>{$teacher->name}</b>,\n";
            $text .= "Đây là tin nhắn kiểm tra kết nối từ hệ thống Admin.\n";
            $text .= "Nếu bạn nhận được tin nhắn này, kết nối Telegram của bạn đã hoạt động bình thường!";

            \App\Jobs\SendTelegramNotification::dispatch($teacher->telegram_chat_id, $text, $botToken);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi kết nối: ' . $e->getMessage()]);
        }
    }
}
