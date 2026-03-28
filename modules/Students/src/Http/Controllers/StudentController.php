<?php

namespace Modules\Students\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Modules\ActiveLogs\src\Models\ActiveLog;
use Modules\Students\src\Http\Requests\studentRequest;
use Modules\Students\src\Models\Student;
use Modules\Students\src\Repositories\StudentsRepositoryInterface;
use Yajra\DataTables\Facades\DataTables;

class StudentController extends Controller
{
    protected $studentRepository;

    public function __construct(StudentsRepositoryInterface $studentRepository)
    {
        $this->studentRepository = $studentRepository;
    }

    public function index()
    {
        $pageTitle = 'Quản lý học viên';

        return view('students::lists', compact('pageTitle'));
    }

    public function trash()
    {
        $pageTitle = 'Thùng rác học viên';

        return view('students::trash', compact('pageTitle'));
    }

    public function data()
    {
        $students = $this->studentRepository->getAllStudents();
        $user = auth()->user();
        $canLogs = $user?->hasPermission('students.logs');
        $canView = $user?->hasPermission('students.view');
        $canEdit = $user?->hasPermission('students.edit');
        $canDelete = $user?->canAnyPermission(['students.soft_delete', 'students.delete']);

        return DataTables::of($students)
            ->addColumn('select', function ($student) {
                return '<div class="form-check m-0 d-flex justify-content-center"><input type="checkbox" class="form-check-input bulk-row-checkbox" value="' . $student->id . '"></div>';
            })
            ->addColumn('two_factor', function ($student) {
                return (int) $student->two_factor_email_enabled === 1
                    ? '<span class="badge rounded-pill" style="background:#2563eb;color:#fff;border:1px solid #2563eb;">Đã bật 2FA</span>'
                    : '<span class="badge rounded-pill bg-light text-dark border">Chưa bật</span>';
            })
            ->addColumn('logs', function ($student) use ($canLogs) {
                return $canLogs
                    ? '<a href="' . route('students.logs', $student->id) . '" class="btn btn-light border">Lịch sử</a>'
                    : '<span class="text-muted small">Không có quyền</span>';
            })
            ->addColumn('courses', function ($student) use ($canView) {
                return $canView
                    ? '<a href="' . route('students.purchased-courses', $student->id) . '" class="btn btn-light border">Khóa học</a>'
                    : '<span class="text-muted small">Không có quyền</span>';
            })
            ->addColumn('link', function ($student) use ($canView) {
                return $canView
                    ? '<a href="' . route('students.coupon-history', $student->id) . '" class="btn btn-primary">Lịch sử mã</a>'
                    : '<span class="text-muted small">Không có quyền</span>';
            })
            ->addColumn('edit', function ($student) use ($canEdit) {
                return $canEdit
                    ? '<a href="' . route('students.edit', $student->id) . '" class="btn btn-warning">Sửa</a>'
                    : '<span class="text-muted small">Không có quyền</span>';
            })
            ->addColumn('delete', function ($student) use ($canDelete) {
                return $canDelete
                    ? '<a href="' . route('students.delete', $student->id) . '" class="btn btn-outline-danger delete-action">Xóa</a>'
                    : '<span class="text-muted small">Không có quyền</span>';
            })
            ->editColumn('created_at', function ($student) {
                return Carbon::parse($student->created_at)->format('d/m/Y H:i:s');
            })
            ->editColumn('status', function ($student) {
                return (int) $student->status === 1
                    ? '<span class="text-success"><i class="fa-solid fa-circle-check"></i> Kích hoạt</span>'
                    : '<span class="text-muted"><i class="fa-solid fa-circle-xmark"></i> Chưa kích hoạt</span>';
            })
            ->rawColumns(['select', 'two_factor', 'edit', 'delete', 'status', 'link', 'courses', 'logs'])
            ->toJson();
    }

    public function trashData()
    {
        $canRestore = auth()->user()?->canAnyPermission(['students.soft_delete', 'students.delete']);
        $canForceDelete = auth()->user()?->hasPermission('students.force_delete');
        $students = \Modules\Students\src\Models\Student::query()->onlyTrashed()->latest('deleted_at');

        return DataTables::of($students)
            ->addColumn('select', fn($student) => '<div class="form-check m-0 d-flex justify-content-center"><input type="checkbox" class="form-check-input bulk-row-checkbox" value="' . $student->id . '"></div>')
            ->addColumn('two_factor', function ($student) {
                return (int) $student->two_factor_email_enabled === 1
                    ? '<span class="badge rounded-pill" style="background:#2563eb;color:#fff;border:1px solid #2563eb;">Đã bật 2FA</span>'
                    : '<span class="badge rounded-pill bg-light text-dark border">Chưa bật</span>';
            })
            ->addColumn('deleted_at', fn($student) => Carbon::parse($student->deleted_at)->format('d/m/Y H:i:s'))
            ->addColumn('restore', function ($student) use ($canRestore) {
                if (!$canRestore) {
                    return '<span class="text-muted small">Không có quyền</span>';
                }

                return '<form method="POST" action="' . route('students.restore', $student->id) . '" class="d-inline-block">'
                    . csrf_field()
                    . '<button type="submit" class="btn btn-success">Khôi phục</button>'
                    . '</form>';
            })
            ->addColumn('force_delete', function ($student) use ($canForceDelete) {
                if (!$canForceDelete) {
                    return '<span class="text-muted small">Không có quyền</span>';
                }

                return '<form method="POST" action="' . route('students.force-delete', $student->id) . '" class="d-inline-block" onsubmit="return confirm(\'Xóa vĩnh viễn học viên này?\');">'
                    . csrf_field()
                    . method_field('DELETE')
                    . '<button type="submit" class="btn btn-outline-danger">Xóa vĩnh viễn</button>'
                    . '</form>';
            })
            ->rawColumns(['select', 'two_factor', 'restore', 'force_delete'])
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
                'bulk_action' => 'Vui lòng chọn ít nhất một học viên.',
            ]);
        }

        $students = Student::query()->whereIn('id', $selectedIds)->get();

        if ($students->isEmpty()) {
            return back()->with('msg_danger', 'Không tìm thấy học viên để xử lý.');
        }

        if ($action === 'activate' || $action === 'deactivate') {
            $newStatus = $action === 'activate' ? 1 : 0;
            Student::query()->whereIn('id', $selectedIds)->update(['status' => $newStatus]);

            foreach ($students as $student) {
                activity_log(
                    action: 'update',
                    subject: $student,
                    properties: [
                        'old' => ['status' => $student->status],
                        'new' => ['status' => $newStatus],
                    ],
                    logName: $newStatus === 1 ? 'Kích hoạt hàng loạt' : 'Tạm khóa hàng loạt',
                    description: $newStatus === 1 ? 'Kích hoạt học viên' : 'Tạm khóa học viên'
                );
            }

            return back()->with('msg', $newStatus === 1
                ? 'Đã kích hoạt ' . $students->count() . ' học viên.'
                : 'Đã tạm khóa ' . $students->count() . ' học viên.');
        }

        if ($action === 'delete') {
            foreach ($students as $student) {
                $snapshot = $student->toArray();
                unset($snapshot['password']);

                $this->logoutStudentSessions($student->id);
                $this->studentRepository->delete($student->id);

                activity_log(
                    action: 'delete',
                    subject: $student,
                    properties: [
                        'data' => $snapshot,
                    ],
                    logName: 'Xóa hàng loạt',
                    description: 'Xóa học viên'
                );
            }

            return back()->with('msg', 'Đã xóa ' . $students->count() . ' học viên.');
        }

        return back()->with('msg_danger', 'Thao tác hàng loạt không hợp lệ.');
    }

    public function create()
    {
        $pageTitle = 'Thêm mới học viên';

        return view('students::create', compact('pageTitle'));
    }

    public function store(studentRequest $request)
    {
        $dataInsert = [
            'name' => $request->name,
            'email' => $request->email,
            'status' => $request->status,
            'password' => bcrypt($request->password),
            'address' => $request->address,
            'phone' => $request->phone,
        ];

        $student = $this->studentRepository->create($dataInsert);

        activity_log(
            action: 'create',
            subject: $student,
            properties: [
                'data' => array_diff_key($dataInsert, array_flip(['password'])),
            ],
            logName: 'Thêm mới',
            description: 'Tạo mới học viên'
        );

        return redirect()->route('students.index')->with('msg', __('students::messages.create.success'));
    }

    public function edit($id)
    {
        $pageTitle = 'Cập nhật học viên';
        $students = $this->studentRepository->find($id);

        if (empty($students)) {
            abort(404);
        }

        return view('students::edit', compact('students', 'pageTitle'));
    }

    public function update(studentRequest $request, $id)
    {
        $studentModel = $this->studentRepository->find($id);

        if (empty($studentModel)) {
            abort(404);
        }

        $old = $studentModel->toArray();
        unset($old['password']);

        $data = $request->except('_token', 'password');

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $status = $this->studentRepository->update($id, $data);

        if (!empty($status)) {
            $studentFresh = $this->studentRepository->find($id);
            $new = $studentFresh ? $studentFresh->toArray() : [];
            unset($new['password']);

            activity_log(
                action: 'update',
                subject: $studentFresh ?? $studentModel,
                properties: [
                    'old' => $old,
                    'new' => $new,
                ],
                logName: 'Cập nhật',
                description: 'Cập nhật học viên'
            );

            return back()->with('msg', __('students::messages.update.success'));
        }

        return back()->with('msg_danger', __('students::messages.update.failure'));
    }

    public function delete($id)
    {
        $student = $this->studentRepository->find($id);

        if (empty($student)) {
            abort(404);
        }

        $snapshot = $student->toArray();
        unset($snapshot['password']);

        $this->logoutStudentSessions($student->id);
        $status = $this->studentRepository->delete($id);

        if ($status) {
            activity_log(
                action: 'delete',
                subject: $student,
                properties: [
                    'data' => $snapshot,
                ],
                logName: 'Xóa',
                description: 'Xóa học viên'
            );

            return back()->with('msg', __('students::messages.delete.success'));
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
                'bulk_action' => 'Vui lòng chọn ít nhất một học viên trong thùng rác.',
            ]);
        }

        $students = \Modules\Students\src\Models\Student::query()->onlyTrashed()->whereIn('id', $selectedIds)->get();

        if ($students->isEmpty()) {
            return back()->with('msg_danger', 'Không tìm thấy học viên hợp lệ trong thùng rác.');
        }

        if ($action === 'restore') {
            foreach ($students as $student) {
                $student->restore();
            }

            return back()->with('msg', 'Đã khôi phục ' . $students->count() . ' học viên.');
        }

        if ($action === 'force_delete') {
            foreach ($students as $student) {
                $this->logoutStudentSessions($student->id);
                $student->courses()->detach();
                $student->coupons()->detach();
                $student->forceDelete();
            }

            return back()->with('msg', 'Đã xóa vĩnh viễn ' . $students->count() . ' học viên.');
        }

        return back()->with('msg_danger', 'Thao tác trong thùng rác không hợp lệ.');
    }

    public function restore($id)
    {
        $student = \Modules\Students\src\Models\Student::query()->onlyTrashed()->find($id);

        if (!$student) {
            abort(404);
        }

        $student->restore();

        return back()->with('msg', 'Khôi phục học viên thành công.');
    }

    public function forceDelete($id)
    {
        $student = \Modules\Students\src\Models\Student::query()->onlyTrashed()->find($id);

        if (!$student) {
            abort(404);
        }

        $this->logoutStudentSessions($student->id);
        $student->courses()->detach();
        $student->coupons()->detach();
        $student->forceDelete();

        return back()->with('msg', 'Đã xóa vĩnh viễn học viên.');
    }

    public function CouponHistory($id)
    {
        $pageTitle = 'Lịch sử mã giảm giá học viên';

        $student = Student::with(['coupons'])->findOrFail($id);

        return view('students::coupon_history', compact('student', 'pageTitle'));
    }

    public function purchasedCourses($id)
    {
        $pageTitle = 'Khóa học đã mua';

        $student = $this->studentRepository->find($id);
        $courses = $this->studentRepository->getPurchasedCourses($id, config('paginate.limit'));

        return view('students::course_student', compact('student', 'courses', 'pageTitle'));
    }

    public function logs(Request $request, $id)
    {
        $student = $this->studentRepository->find($id);

        if (empty($student)) {
            abort(404);
        }

        $pageTitle = "Lịch sử: {$student->name}";

        $query = ActiveLog::query()
            ->where('subject_type', get_class($student))
            ->where('subject_id', $student->id)
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

        return view('students::logs', compact('pageTitle', 'student', 'logs'));
    }

    protected function logoutStudentSessions(int $studentId): void
    {
        if (config('session.driver') !== 'database' || !Schema::hasTable(config('session.table', 'sessions'))) {
            return;
        }

        DB::table(config('session.table', 'sessions'))
            ->where('user_id', $studentId)
            ->delete();
    }
}
