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
use Modules\Courses\src\Models\Courses;
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

        $breadcrumbs = [['label' => 'Quản lý học viên']];
        return view('students::lists', compact('pageTitle', 'breadcrumbs'));
    }

    public function trash()
    {
        $pageTitle = 'Thùng rác học viên';

        $breadcrumbs = [
            ['label' => 'Quản lý học viên', 'link' => route('students.index')],
            ['label' => 'Thùng rác']
        ];
        return view('students::trash', compact('pageTitle', 'breadcrumbs'));
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
            ->addColumn('student_info', function ($student) {
                $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($student->name) . '&background=f1f5f9&color=64748b';
                
                return '
                    <div class="d-flex align-items-center gap-3">
                        <img src="' . $avatar . '" class="rounded-circle shadow-sm" style="width: 40px; height: 40px; object-fit: cover;">
                        <div>
                            <div class="fw-bold text-dark">' . e($student->name) . '</div>
                            <div class="text-muted small">' . e($student->email) . '</div>
                        </div>
                    </div>';
            })
            ->addColumn('security_status', function ($student) {
                $statusBadge = (int) $student->status === 1
                    ? '<span class="badge bg-success-subtle text-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i>Kích hoạt</span>'
                    : '<span class="badge bg-secondary-subtle text-muted px-2 py-1"><i class="fa-solid fa-circle-xmark me-1"></i>Chưa kích hoạt</span>';

                $twoFactorBadge = (int) $student->two_factor_email_enabled === 1
                    ? '<span class="badge bg-primary-subtle text-primary px-2 py-1 ms-1">2FA On</span>'
                    : '<span class="badge bg-light text-muted border px-2 py-1 ms-1" style="font-size: 10px;">2FA Off</span>';

                $rolesBadge = '<span class="badge bg-info-subtle text-info px-2 py-1 ms-1">Học viên</span>';
                if ($student->teacher) {
                    $rolesBadge .= '<span class="badge bg-success text-white px-2 py-1 ms-1" style="font-size: 10px;">Giảng viên</span>';
                }

                return '
                    <div class="d-flex flex-wrap gap-1 align-items-center">
                        ' . $statusBadge . '
                        ' . $twoFactorBadge . '
                        ' . $rolesBadge . '
                    </div>';
            })
            ->addColumn('email_verified', function ($student) {
                return $student->email_verified_at
                    ? '<span class="badge bg-success-subtle text-success px-2 py-1"><i class="fa-solid fa-envelope-circle-check me-1"></i>Đã xác thực</span>'
                    : '<span class="badge bg-danger-subtle text-danger px-2 py-1"><i class="fa-solid fa-triangle-exclamation me-1"></i>Chưa xác thực</span>';
            })
            ->addColumn('courses_list', function ($student) {
                if ($student->courses->isEmpty()) {
                    return '<span class="text-muted small">Chưa đăng ký khóa học</span>';
                }
                
                $html = '<div class="d-flex flex-column gap-1" style="max-width: 300px;">';
                foreach ($student->courses->take(3) as $course) {
                    $teacherName = $course->teacher ? e($course->teacher->name) : 'N/A';
                    $html .= '<div class="text-truncate small" title="' . e($course->name) . ' - GV: ' . $teacherName . '">
                                <i class="fa-solid fa-book text-muted me-1"></i> ' . e($course->name) . ' 
                                <span class="text-secondary">(GV: ' . $teacherName . ')</span>
                              </div>';
                }
                if ($student->courses->count() > 3) {
                    $html .= '<div class="text-primary small fw-semibold">+ ' . ($student->courses->count() - 3) . ' khóa học khác</div>';
                }
                $html .= '</div>';
                
                return $html;
            })
            ->editColumn('created_at', function ($student) {
                return '<div class="small text-muted">' . Carbon::parse($student->created_at)->format('d/m/Y') . '</div>';
            })
            ->addColumn('actions', function ($student) use ($canEdit, $canView, $canLogs, $canDelete) {
                $btn = '<div class="dropdown">
                            <button class="btn btn-light btn-sm border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2">';

                if ($canView) {
                    $btn .= '<li><a class="dropdown-item py-2" href="' . route('students.purchased-courses', $student->id) . '"><i class="fa-solid fa-book-open text-info me-2"></i>Khóa học đã mua</a></li>';
                    $btn .= '<li><a class="dropdown-item py-2" href="' . route('students.coupon-history', $student->id) . '"><i class="fa-solid fa-ticket text-primary me-2"></i>Lịch sử mã giảm</a></li>';
                }

                if ($canLogs) {
                    $btn .= '<li><a class="dropdown-item py-2" href="' . route('students.logs', $student->id) . '"><i class="fa-solid fa-clock-rotate-left text-muted me-2"></i>Lịch sử thao tác</a></li>';
                }

                if ($canEdit) {
                    $btn .= '<li><a class="dropdown-item py-2" href="' . route('students.edit', $student->id) . '"><i class="fa-solid fa-user-pen text-warning me-2"></i>Chỉnh sửa</a></li>';
                    
                    $btn .= '<li><hr class="dropdown-divider my-1"></li>';
                    $btn .= '<li class="dropdown-header small text-muted py-1">Đăng nhập giả lập</li>';
                    $btn .= '<li><a class="dropdown-item py-2" href="' . route('students.impersonate', [$student->id, 'type' => 'student']) . '"><i class="fa-solid fa-user-graduate text-dark me-2"></i>Quyền Học viên</a></li>';
                    
                    if ($student->teacher) {
                        $btn .= '<li><a class="dropdown-item py-2" href="' . route('students.impersonate', [$student->id, 'type' => 'teacher']) . '"><i class="fa-solid fa-chalkboard-user text-dark me-2"></i>Quyền Giảng viên</a></li>';
                    }
                }

                if ($canDelete) {
                    $btn .= '<li><hr class="dropdown-divider my-1"></li>';
                    $btn .= '<li><a class="dropdown-item py-2 text-danger delete-action" href="' . route('students.delete', $student->id) . '"><i class="fa-solid fa-trash me-2"></i>Xóa học viên</a></li>';
                }

                $btn .= '</ul></div>';
                return $btn;
            })
            ->rawColumns(['select', 'student_info', 'security_status', 'email_verified', 'courses_list', 'created_at', 'actions'])
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

        $breadcrumbs = [
            ['label' => 'Quản lý học viên', 'link' => route('students.index')],
            ['label' => 'Thêm mới']
        ];
        return view('students::create', compact('pageTitle', 'breadcrumbs'));
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
            'email_verified_at' => $request->boolean('email_verified') ? now() : null,
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

        $breadcrumbs = [
            ['label' => 'Quản lý học viên', 'link' => route('students.index')],
            ['label' => 'Cập nhật']
        ];
        return view('students::edit', compact('students', 'pageTitle', 'breadcrumbs'));
    }

    public function update(studentRequest $request, $id)
    {
        $studentModel = $this->studentRepository->find($id);

        if (empty($studentModel)) {
            abort(404);
        }

        $old = $studentModel->toArray();
        unset($old['password']);

        $data = $request->except('_token', 'password', 'email_verified');

        if ($request->boolean('email_verified')) {
            if (!$studentModel->email_verified_at) {
                $data['email_verified_at'] = now();
            }
        } else {
            $data['email_verified_at'] = null;
        }

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

                activity_log(
                    action: 'restore',
                    subject: $student->fresh(),
                    properties: ['restored_from_trash' => true],
                    logName: 'Khôi phục hàng loạt',
                    description: 'Khôi phục học viên từ thùng rác'
                );
            }

            return back()->with('msg', 'Đã khôi phục ' . $students->count() . ' học viên.');
        }

        if ($action === 'force_delete') {
            foreach ($students as $student) {
                $snapshot = method_exists($student, 'toArray') ? $student->toArray() : (array) $student;
                $this->logoutStudentSessions($student->id);
                $student->courses()->detach();
                $student->coupons()->detach();
                $student->forceDelete();

                activity_log(
                    action: 'force_delete',
                    subject: $student,
                    properties: [
                        'data' => $snapshot,
                        'deleted_permanently' => true,
                    ],
                    logName: 'Xóa vĩnh viễn hàng loạt',
                    description: 'Xóa vĩnh viễn học viên'
                );
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

        activity_log(
            action: 'restore',
            subject: $student->fresh(),
            properties: ['restored_from_trash' => true],
            logName: 'Khôi phục',
            description: 'Khôi phục học viên thành công.'
        );

        return back()->with('msg', 'Khôi phục học viên thành công.');
    }

    public function forceDelete($id)
    {
        $student = \Modules\Students\src\Models\Student::query()->onlyTrashed()->find($id);

        if (!$student) {
            abort(404);
        }

        $snapshot = method_exists($student, 'toArray') ? $student->toArray() : (array) $student;
        $this->logoutStudentSessions($student->id);
        $student->courses()->detach();
        $student->coupons()->detach();
        $student->forceDelete();

        activity_log(
            action: 'force_delete',
            subject: $student,
            properties: [
                'data' => $snapshot,
                'deleted_permanently' => true,
            ],
            logName: 'Xóa vĩnh viễn',
            description: 'Xóa vĩnh viễn học viên'
        );

        return back()->with('msg', 'Đã xóa vĩnh viễn học viên.');
    }

    public function CouponHistory($id)
    {
        $pageTitle = 'Lịch sử mã giảm giá học viên';

        $student = Student::with(['coupons'])->findOrFail($id);

        $breadcrumbs = [
            ['label' => 'Quản lý học viên', 'link' => route('students.index')],
            ['label' => 'Lịch sử mã giảm']
        ];
        return view('students::coupon_history', compact('student', 'pageTitle', 'breadcrumbs'));
    }

    public function purchasedCourses($id)
    {
        $pageTitle = 'Khóa học đã mua';

        $student = $this->studentRepository->find($id);
        if (!$student) {
            abort(404);
        }
        $courses = $this->studentRepository->getPurchasedCourses($id, config('paginate.limit'));
        
        $allCourses = Courses::query()
            ->withoutGlobalScopes()
            ->with('teacher:id,name')
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'teacher_id']);

        $breadcrumbs = [
            ['label' => 'Quản lý học viên', 'link' => route('students.index')],
            ['label' => 'Khóa học đã mua']
        ];
        return view('students::course_student', compact('student', 'courses', 'allCourses', 'pageTitle', 'breadcrumbs'));
    }

    public function searchCourses(Request $request)
    {
        $keyword = $request->input('q');
        $courses = Courses::query()
            ->withoutGlobalScopes()
            ->where('status', 1)
            ->when($keyword, function ($query) use ($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('code', 'like', '%' . $keyword . '%');
            })
            ->select(['id', 'name', 'code'])
            ->limit(20)
            ->get();

        return response()->json($courses);
    }

    public function grantCourse(Request $request, $id)
    {
        $student = $this->studentRepository->find($id);
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Học viên không tồn tại.'], 404);
        }

        $request->validate([
            'course_ids' => 'required|array',
            'course_ids.*' => 'integer|exists:courses,id',
        ]);

        $courseIds = $request->input('course_ids');
        $sendEmail = $request->has('send_email');
        $courses = Courses::query()->whereIn('id', $courseIds)->get();

        foreach ($courses as $course) {
            // Kiểm tra xem đã có chưa
            $exists = $student->courses()->where('courses.id', $course->id)->exists();
            if (!$exists) {
                $student->courses()->attach($course->id, ['status' => 1, 'created_at' => now(), 'updated_at' => now()]);

                // Gửi thông báo Email & Notify
                try {
                    $student->notify(new \App\Notifications\AdminCourseGiftNotification($course, session('locale', 'vi'), $sendEmail));
                } catch (\Exception $e) {
                    \Log::error('Gift Notification Error: ' . $e->getMessage());
                }

                activity_log(
                    action: 'grant_course',
                    subject: $student,
                    properties: [
                        'course_id' => $course->id,
                        'course_name' => $course->name,
                        'admin' => auth()->user()?->name,
                    ],
                    logName: 'Tặng khóa học',
                    description: "Admin đã tặng khóa học [{$course->name}] cho học viên [{$student->name}]"
                );
            }
        }

        return response()->json(['success' => true, 'message' => 'Đã tặng khóa học thành công.']);
    }

    public function revokeCourse(Request $request, $id)
    {
        $student = $this->studentRepository->find($id);
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Học viên không tồn tại.'], 404);
        }

        $courseId = $request->input('course_id');
        $course = Courses::query()->find($courseId);
        
        if (!$course) {
            return response()->json(['success' => false, 'message' => 'Khóa học không tồn tại.'], 404);
        }

        $student->courses()->detach($courseId);

        activity_log(
            action: 'revoke_course',
            subject: $student,
            properties: [
                'course_id' => $course->id,
                'course_name' => $course->name,
                'admin' => auth()->user()?->name,
            ],
            logName: 'Thu hồi khóa học',
            description: "Admin đã thu hồi khóa học [{$course->name}] từ học viên [{$student->name}]"
        );

        return response()->json(['success' => true, 'message' => 'Đã thu hồi khóa học thành công.']);
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

        $loginLogs = ActiveLog::query()
            ->where('subject_type', get_class($student))
            ->where('subject_id', $student->id)
            ->where('action', 'login')
            ->latest()
            ->take(10)
            ->get();

        $breadcrumbs = [
            ['label' => 'Quản lý học viên', 'link' => route('students.index')],
            ['label' => 'Lịch sử hoạt động']
        ];
        return view('students::logs', compact('pageTitle', 'student', 'logs', 'loginLogs', 'breadcrumbs'));
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

    public function impersonate(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        $type = $request->query('type', 'student');

        // Lưu thông tin Admin hiện tại và ID học viên vào session
        session([
            'admin_impersonator' => auth()->id(),
            'impersonated_student_id' => $student->id
        ]);

        // Đăng nhập vào guard students
        auth('students')->login($student);
        
        // Đảm bảo session được lưu ngay lập tức
        session()->save();

        if ($type === 'teacher' && $student->teacher) {
            return redirect()->route('teacher.dashboard.index', ['locale' => app()->getLocale()]);
        }

        return redirect()->route('students.account.index', ['locale' => app()->getLocale()]);
    }

    public function stopImpersonating()
    {
        if (!session()->has('admin_impersonator')) {
            return redirect('/');
        }

        auth('students')->logout();
        session()->forget('admin_impersonator');

        return redirect()->route('students.index')->with('msg', 'Đã quay lại tài khoản Admin.');
    }
}
