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
        $pageTitle = 'Quản lý giáo viên';

        return view('teacher::lists', compact('pageTitle'));
    }

    public function trash()
    {
        $pageTitle = 'Thùng rác giảng viên';

        return view('teacher::trash', compact('pageTitle'));
    }

    public function data(Request $request)
    {
        $teacher = $this->teacherRepository->getAllTeacher();

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
                return $canLogs ? '<a href="' . route('teacher.logs', $teachers->id) . '" class="btn btn-light border">Lịch sử</a>' : '<span class="text-muted small">Không có quyền</span>';
            })
            ->addColumn('edit', function ($teachers) use ($canEdit) {
                return $canEdit ? '<a href="' . route('teacher.edit', $teachers->id) . '" class="btn btn-warning">Sửa</a>' : '<span class="text-muted small">Không có quyền</span>';
            })
            ->addColumn('delete', function ($teachers) use ($canDelete) {
                return $canDelete ? '<a href="' . route('teacher.delete', $teachers->id) . '" class="btn btn-outline-danger delete-action">Xóa</a>' : '<span class="text-muted small">Không có quyền</span>';
            })
            ->editColumn('created_at', function ($teachers) {
                return Carbon::parse($teachers->created_at)->format('d/m/Y H:i:s');
            })
            ->editColumn('image', function ($teachers) {
                return $teachers->image
                    ? '<img src="' . $teachers->image . '" style="width: 80px; border-radius: 12px;">'
                    : 'Không có ảnh';
            })
            ->rawColumns(['select', 'edit', 'delete', 'image', 'logs'])
            ->toJson();

        return DataTables::of($teacher)
            ->addColumn('select', function ($teachers) {
                return '<div class="form-check m-0 d-flex justify-content-center"><input type="checkbox" class="form-check-input bulk-row-checkbox" value="' . $teachers->id . '"></div>';
            })
            ->addColumn('logs', function ($teachers) {
                return '<a href="' . route('teacher.logs', $teachers->id) . '" class="btn btn-light border">Lịch sử</a>';
            })
            ->addColumn('edit', function ($teachers) {
                return '<a href="' . route('teacher.edit', $teachers->id) . '" class="btn btn-warning">Sửa</a>';
            })
            ->addColumn('delete', function ($teachers) {
                return '<a href="' . route('teacher.delete', $teachers->id) . '" class="btn btn-outline-danger delete-action">Xóa</a>';
            })
            ->editColumn('created_at', function ($teachers) {
                return Carbon::parse($teachers->created_at)->format('d/m/Y H:i:s');
            })
            ->editColumn('image', function ($teachers) {
                return $teachers->image
                    ? '<img src="' . $teachers->image . '" style="width: 80px; border-radius: 12px;">'
                    : 'Không có ảnh';
            })
            ->rawColumns(['select', 'edit', 'delete', 'image', 'logs'])
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
                    : 'Không có ảnh';
            })
            ->addColumn('name', fn($teacher) => e($teacher->name_locale))
            ->addColumn('deleted_at', fn($teacher) => Carbon::parse($teacher->deleted_at)->format('d/m/Y H:i:s'))
            ->addColumn('restore', function ($teacher) use ($canRestore) {
                if (!$canRestore) {
                    return '<span class="text-muted small">Không có quyền</span>';
                }

                return '<form method="POST" action="' . route('teacher.restore', $teacher->id) . '" class="d-inline-block">'
                    . csrf_field()
                    . '<button type="submit" class="btn btn-success">Khôi phục</button>'
                    . '</form>';
            })
            ->addColumn('force_delete', function ($teacher) use ($canForceDelete) {
                if (!$canForceDelete) {
                    return '<span class="text-muted small">Không có quyền</span>';
                }

                return '<form method="POST" action="' . route('teacher.force-delete', $teacher->id) . '" class="d-inline-block" onsubmit="return confirm(\'Xóa vĩnh viễn giảng viên này?\');">'
                    . csrf_field()
                    . method_field('DELETE')
                    . '<button type="submit" class="btn btn-outline-danger">Xóa vĩnh viễn</button>'
                    . '</form>';
            })
            ->rawColumns(['select', 'image', 'restore', 'force_delete'])
            ->toJson();
    }

    public function create()
    {
        $pageTitle = 'Thêm mới giáo viên';

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
                'bulk_action' => 'Vui lòng chọn ít nhất một giảng viên.',
            ]);
        }

        $teachers = $selectedIds
            ->map(fn($id) => $this->teacherRepository->find($id))
            ->filter();

        if ($teachers->isEmpty()) {
            return back()->with('msg_danger', 'Không tìm thấy giảng viên để xử lý.');
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
                    logName: 'Xóa hàng loạt',
                    description: 'Xóa giáo viên'
                );
            }

            return back()->with('msg', 'Đã xóa ' . $teachers->count() . ' giảng viên.');
        }

        return back()->with('msg_danger', 'Thao tác hàng loạt không hợp lệ.');
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
            logName: 'Thêm mới',
            description: 'Tạo mới giáo viên'
        );

        return redirect()->route('teacher.index')->with('msg', __('teacher::messages.create.success'));
    }

    public function edit($id)
    {
        $pageTitle = 'Cập nhật giáo viên';
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
                logName: 'Cập nhật',
                description: 'Cập nhật giáo viên'
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
                logName: 'Xóa',
                description: 'Xóa giáo viên'
            );

            return back()->with('msg', __('teacher::messages.delete.success'));
        }

        return back()->with('msg_danger', 'Xóa thất bại');
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
                'bulk_action' => 'Vui lòng chọn ít nhất một giảng viên trong thùng rác.',
            ]);
        }

        $teachers = \Modules\Teacher\src\Models\Teacher::query()->onlyTrashed()->whereIn('id', $selectedIds)->get();

        if ($teachers->isEmpty()) {
            return back()->with('msg_danger', 'Không tìm thấy giảng viên hợp lệ trong thùng rác.');
        }

        if ($action === 'restore') {
            foreach ($teachers as $teacher) {
                $teacher->restore();
            }

            return back()->with('msg', 'Đã khôi phục ' . $teachers->count() . ' giảng viên.');
        }

        if ($action === 'force_delete') {
            $teacherHasCourses = $teachers->first(fn($teacher) => $this->teacherHasCourses($teacher->id));

            if ($teacherHasCourses) {
                return back()->with('msg_danger', 'Không thể xóa vĩnh viễn giáo viên còn khóa học đang gắn.');
            }

            foreach ($teachers as $teacher) {
                if ($teacher->image) {
                    deleteFileStorage($teacher->image);
                }

                $teacher->forceDelete();
            }

            return back()->with('msg', 'Đã xóa vĩnh viễn ' . $teachers->count() . ' giảng viên.');
        }

        return back()->with('msg_danger', 'Thao tác trong thùng rác không hợp lệ.');
    }

    public function restore($id)
    {
        $teacher = \Modules\Teacher\src\Models\Teacher::query()->onlyTrashed()->find($id);

        if (!$teacher) {
            abort(404);
        }

        $teacher->restore();

        return back()->with('msg', 'Khôi phục giảng viên thành công.');
    }

    public function forceDelete($id)
    {
        $teacher = \Modules\Teacher\src\Models\Teacher::query()->onlyTrashed()->find($id);

        if (!$teacher) {
            abort(404);
        }

        if ($this->teacherHasCourses($teacher->id)) {
            return back()->with('msg_danger', 'Không thể xóa vĩnh viễn giáo viên còn khóa học đang gắn.');
        }

        if ($teacher->image) {
            deleteFileStorage($teacher->image);
        }

        $teacher->forceDelete();

        return back()->with('msg', 'Đã xóa vĩnh viễn giảng viên.');
    }

    public function logs(Request $request, $id)
    {
        $teacher = $this->teacherRepository->find($id);

        if (empty($teacher)) {
            abort(404);
        }

        $pageTitle = "Lịch sử: {$teacher->name}";

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
