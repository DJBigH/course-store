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
        $pageTitle = 'QuÃ¡ÂºÂ£n lÃƒÂ½ giÃƒÂ¡o viÃƒÂªn';

        return view('teacher::lists', compact('pageTitle'));
    }

    public function trash()
    {
        $pageTitle = 'ThÃƒÂ¹ng rÃƒÂ¡c giÃ¡ÂºÂ£ng viÃƒÂªn';

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

        return DataTables::of($teacher)
            ->addColumn('select', function ($teachers) {
                return '<div class="form-check m-0 d-flex justify-content-center"><input type="checkbox" class="form-check-input bulk-row-checkbox" value="' . $teachers->id . '"></div>';
            })
            ->addColumn('logs', function ($teachers) use ($canLogs) {
                return $canLogs ? '<a href="' . route('teacher.logs', $teachers->id) . '" class="btn btn-light border">LÃ¡Â»â€¹ch sÃ¡Â»Â­</a>' : '<span class="text-muted small">KhÃƒÂ´ng cÃƒÂ³ quyÃ¡Â»Ân</span>';
            })
            ->addColumn('edit', function ($teachers) use ($canEdit) {
                return $canEdit ? '<a href="' . route('teacher.edit', $teachers->id) . '" class="btn btn-warning">SÃ¡Â»Â­a</a>' : '<span class="text-muted small">KhÃƒÂ´ng cÃƒÂ³ quyÃ¡Â»Ân</span>';
            })
            ->addColumn('delete', function ($teachers) use ($canDelete) {
                return $canDelete ? '<a href="' . route('teacher.delete', $teachers->id) . '" class="btn btn-outline-danger delete-action">XÃƒÂ³a</a>' : '<span class="text-muted small">KhÃƒÂ´ng cÃƒÂ³ quyÃ¡Â»Ân</span>';
            })
            ->editColumn('created_at', function ($teachers) {
                return Carbon::parse($teachers->created_at)->format('d/m/Y H:i:s');
            })
            ->addColumn('last_active_at', function ($teachers) {
                if ($teachers->last_active_at) {
                    return Carbon::parse($teachers->last_active_at)->format('d/m/Y H:i:s');
                }

                return '<span class="text-warning small">ChÆ°a cÃ³ hoáº¡t Ä‘á»™ng</span>';
            })
            ->addColumn('inactive_days', function ($teachers) {
                $reference = $teachers->last_active_at ?: $teachers->created_at;
                $days = Carbon::parse($reference)->diffInDays(now());
                $tone = 'activity-age--fresh';

                if ($days >= 90) {
                    $tone = 'activity-age--danger';
                } elseif ($days >= 60) {
                    $tone = 'activity-age--warning';
                } elseif ($days >= 30) {
                    $tone = 'activity-age--notice';
                }

                if (!$teachers->last_active_at) {
                    return '<span class="activity-age ' . $tone . '">' . $days . ' ngày</span><div class="small text-muted">Chưa từng hoạt động</div>';
                }

                if ($days === 0) {
                    return '<span class="activity-age activity-age--fresh">Hôm nay</span>';
                }

                return '<span class="activity-age ' . $tone . '">' . $days . ' ngày</span>';
            })
            ->editColumn('image', function ($teachers) {
                return $teachers->image
                    ? '<img src="' . $teachers->image . '" style="width: 80px; border-radius: 12px;">'
                    : 'KhÃƒÂ´ng cÃƒÂ³ Ã¡ÂºÂ£nh';
            })
            ->rawColumns(['select', 'edit', 'delete', 'image', 'logs', 'last_active_at', 'inactive_days'])
            ->toJson();
    }

    public function trashData()
    {
        $canRestore = auth()->user()?->canAnyPermission(['teachers.soft_delete', 'teachers.delete']);
        $canForceDelete = auth()->user()?->hasPermission('teachers.force_delete');
        $teachers = \Modules\Teacher\src\Models\Teacher::query()->onlyTrashed()->latest('deleted_at');

        return DataTables::of($teachers)
            ->addColumn('select', fn($teacher) => '<div class="form-check m-0 d-flex justify-content-center"><input type="checkbox" class="form-check-input bulk-row-checkbox" value="' . $teacher->id . '"></div>')
            ->addColumn('image', function ($teacher) {
                return $teacher->image
                    ? '<img src="' . $teacher->image . '" style="width: 80px; border-radius: 12px;">'
                    : 'KhÃƒÂ´ng cÃƒÂ³ Ã¡ÂºÂ£nh';
            })
            ->addColumn('name', fn($teacher) => e($teacher->name_locale))
            ->addColumn('deleted_at', fn($teacher) => Carbon::parse($teacher->deleted_at)->format('d/m/Y H:i:s'))
            ->addColumn('restore', function ($teacher) use ($canRestore) {
                if (!$canRestore) {
                    return '<span class="text-muted small">KhÃƒÂ´ng cÃƒÂ³ quyÃ¡Â»Ân</span>';
                }

                return '<form method="POST" action="' . route('teacher.restore', $teacher->id) . '" class="d-inline-block">'
                    . csrf_field()
                    . '<button type="submit" class="btn btn-success">KhÃƒÂ´i phÃ¡Â»Â¥c</button>'
                    . '</form>';
            })
            ->addColumn('force_delete', function ($teacher) use ($canForceDelete) {
                if (!$canForceDelete) {
                    return '<span class="text-muted small">KhÃƒÂ´ng cÃƒÂ³ quyÃ¡Â»Ân</span>';
                }

                return '<form method="POST" action="' . route('teacher.force-delete', $teacher->id) . '" class="d-inline-block" onsubmit="return confirm(\'XÃƒÂ³a vÃ„Â©nh viÃ¡Â»â€¦n giÃ¡ÂºÂ£ng viÃƒÂªn nÃƒÂ y?\');">'
                    . csrf_field()
                    . method_field('DELETE')
                    . '<button type="submit" class="btn btn-outline-danger">XÃƒÂ³a vÃ„Â©nh viá»…n</button>'
                    . '</form>';
            })
            ->rawColumns(['select', 'image', 'restore', 'force_delete'])
            ->toJson();
    }

    public function create()
    {
        $pageTitle = 'ThÃƒÂªm mÃ¡Â»â€ºi giÃ¡ÂºÂ£ng viÃƒÂªn';

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
                'bulk_action' => 'Vui lÃƒÂ²ng chÃ¡Â»Ân ÃƒÂ­t nhÃ¡ÂºÂ¥t mÃ¡Â»â„¢t giÃ¡ÂºÂ£ng viÃƒÂªn.',
            ]);
        }

        $teachers = $selectedIds
            ->map(fn($id) => $this->teacherRepository->find($id))
            ->filter();

        if ($teachers->isEmpty()) {
            return back()->with('msg_danger', 'KhÃƒÂ´ng tÃƒÂ¬m thÃ¡ÂºÂ¥y giÃ¡ÂºÂ£ng viÃƒÂªn Ã„â€˜Ã¡Â»Æ’ xÃ¡Â»Â­ lÃƒÂ½.');
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
                    logName: 'XÃƒÂ³a hÃƒÂ ng loáº¡t',
                    description: 'XÃƒÂ³a giÃƒÂ¡o viÃƒÂªn'
                );
            }

            return back()->with('msg', 'Ã„ÂÃƒÂ£ xÃƒÂ³a ' . $teachers->count() . ' giÃ¡ÂºÂ£ng viÃƒÂªn.');
        }

        return back()->with('msg_danger', 'Thao tÃƒÂ¡c hÃƒÂ ng loáº¡t khÃƒÂ´ng há»£p lá»‡.');
    }

    public function store(TeacherRequest $request)
    {
        $data = $request->except(['_token']);
        $teacher = $this->teacherRepository->create($data);

        activity_log(
            action: 'create',
            subject: $teacher,
            properties: [
                'data' => array_diff_key($data, array_flip(['password'])),
            ],
            logName: 'ThÃƒÂªm mÃ¡Â»â€ºi',
            description: 'TÃ¡ÂºÂ¡o mÃ¡Â»â€ºi giÃ¡o viÃªn'
        );

        return redirect()->route('teacher.index')->with('msg', __('teacher::messages.create.success'));
    }

    public function edit($id)
    {
        $pageTitle = 'CÃ¡ÂºÂ­p nhÃ¡ÂºÂ­t giÃ¡o viÃªn';
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
                logName: 'CÃ¡ÂºÂ­p nhÃ¡ÂºÂ­t',
                description: 'CÃ¡ÂºÂ­p nhÃ¡ÂºÂ­t giÃ¡o viÃªn'
            );

            return back()->with('msg', __('teacher::messages.update.success'));
        }

        return back()->with('msg_danger', __('teacher::messages.update.failure'));
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
                logName: 'XÃƒÂ³a',
                description: 'XÃƒÂ³a giÃƒÂ¡o viÃƒÂªn'
            );

            return back()->with('msg', __('teacher::messages.delete.success'));
        }

        return back()->with('msg_danger', 'XÃƒÂ³a tháº¥t báº¡i');
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
                'bulk_action' => 'Vui lÃƒÂ²ng chÃ¡Â»Ân ÃƒÂ­t nhÃ¡ÂºÂ¥t mÃ¡Â»â„¢t giÃ¡ÂºÂ£ng viÃƒÂªn trong thÃƒÂ¹ng rÃƒÂ¡c.',
            ]);
        }

        $teachers = \Modules\Teacher\src\Models\Teacher::query()->onlyTrashed()->whereIn('id', $selectedIds)->get();

        if ($teachers->isEmpty()) {
            return back()->with('msg_danger', 'KhÃƒÂ´ng tÃƒÂ¬m thÃ¡ÂºÂ¥y giÃ¡ÂºÂ£ng viÃƒÂªn há»£p lá»‡ trong thÃƒÂ¹ng rÃƒÂ¡c.');
        }

        if ($action === 'restore') {
            foreach ($teachers as $teacher) {
                $teacher->restore();
            }

            return back()->with('msg', 'Ã„ÂÃƒÂ£ khÃƒÂ´i phá»¥c ' . $teachers->count() . ' giÃ¡ÂºÂ£ng viÃƒÂªn.');
        }

        if ($action === 'force_delete') {
            $teacherHasCourses = $teachers->first(fn($teacher) => $this->teacherHasCourses($teacher->id));

            if ($teacherHasCourses) {
                return back()->with('msg_danger', 'KhÃƒÂ´ng thá»ƒ xÃƒÂ³a vÃ„Â©nh viá»…n giÃ¡ÂºÂ£ng viÃƒÂªn cÃƒÂ²n khÃƒÂ³a há»c Ã„â€˜ang gáº¯n.');
            }

            foreach ($teachers as $teacher) {
                if ($teacher->image) {
                    deleteFileStorage($teacher->image);
                }

                $teacher->forceDelete();
            }

            return back()->with('msg', 'Ã„ÂÃƒÂ£ xÃƒÂ³a vÃ„Â©nh viá»…n ' . $teachers->count() . ' giÃ¡ÂºÂ£ng viÃƒÂªn.');
        }

        return back()->with('msg_danger', 'Thao tÃƒÂ¡c trong thÃƒÂ¹ng rÃƒÂ¡c khÃƒÂ´ng há»£p lá»‡.');
    }

    public function restore($id)
    {
        $teacher = \Modules\Teacher\src\Models\Teacher::query()->onlyTrashed()->find($id);

        if (!$teacher) {
            abort(404);
        }

        $teacher->restore();

        return back()->with('msg', 'KhÃƒÂ´i phá»¥c giÃ¡ÂºÂ£ng viÃƒÂªn thÃƒÂ nh cÃƒÂ´ng.');
    }

    public function forceDelete($id)
    {
        $teacher = \Modules\Teacher\src\Models\Teacher::query()->onlyTrashed()->find($id);

        if (!$teacher) {
            abort(404);
        }

        if ($this->teacherHasCourses($teacher->id)) {
            return back()->with('msg_danger', 'KhÃƒÂ´ng thá»ƒ xÃƒÂ³a vÃ„Â©nh viá»…n giÃ¡ÂºÂ£ng viÃƒÂªn cÃƒÂ²n khÃƒÂ³a há»c Ã„â€˜ang gáº¯n.');
        }

        if ($teacher->image) {
            deleteFileStorage($teacher->image);
        }

        $teacher->forceDelete();

        return back()->with('msg', 'Ã„ÂÃƒÂ£ xÃƒÂ³a vÃ„Â©nh viá»…n giÃ¡ÂºÂ£ng viÃƒÂªn.');
    }

    public function logs(Request $request, $id)
    {
        $teacher = $this->teacherRepository->find($id);

        if (empty($teacher)) {
            abort(404);
        }

        $pageTitle = "LÃ¡Â»â€¹ch sÃ¡Â»Â­: {$teacher->name}";

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
}
