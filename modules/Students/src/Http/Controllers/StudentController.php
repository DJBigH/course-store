<?php

namespace Modules\Students\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\ActiveLogs\src\Models\ActiveLog;
use Modules\Students\src\Http\Requests\studentRequest;
use Modules\Students\src\Models\CouponUsage;
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

    public function data()
    {
        $students = $this->studentRepository->getAllStudents();

        return DataTables::of($students)
            ->addColumn('logs', function ($student) {
                return '<a href="' . route('students.logs', $student->id) . '" class="btn btn-info">Lịch sử</a>';
            })

            ->addColumn('courses', function ($student) {
                return '<a href="' . route('students.purchased-courses', $student->id) . '" class="btn btn-info">Xem khóa học</a>';
            })
            ->addColumn('link', function ($student) {
                return '<a href="' . route('students.coupon-history', $student->id) . '" class="btn btn-primary">Lịch sử cấp mã</a>';
            })
            ->addColumn('edit', function ($student) {
                return '<a href="' . route('students.edit', $student->id) . '" class="btn btn-warning">Sửa</a>';
            })
            ->addColumn('delete', function ($student) {
                return '<a href="' . route('students.delete', $student->id) . '" class="btn btn-danger delete-action">Xóa</a>';
            })
            ->editColumn('created_at', function ($students) {
                return Carbon::parse($students->created_at)->format('d/m/Y H:i:s');
            })
            ->editColumn('status', function ($students) {
                return $students->status == 1 ? '<span class="text-success"><i class="fa-solid fa-circle-check"></i> Kích hoạt</span>' : '<span class="text-muted"><i class="fa-solid fa-circle-xmark"></i> Chưa kích hoạt</span>';
            })
            ->rawColumns(['edit', 'delete', 'status', 'link', 'courses', 'logs'])
            ->toJson();
    }

    public function create()
    {
        $pageTitle = 'Thêm mới học viên';
        return view('students::create', compact('pageTitle'));
    }

    public function store(studentRequest $request)
    {
        $dataInsert = [
            'name'     => $request->name,
            'email'    => $request->email,
            'status'   => $request->status,
            'password' => bcrypt($request->password),
            'address'  => $request->address,
            'phone'    => $request->phone,
        ];

        $student = $this->studentRepository->create($dataInsert); // ✅ nên return model

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
        $pageTitle = 'Cập nhập học viên';
        $students = $this->studentRepository->find($id);
        if (empty($students)) {
            abort(404);
        }

        return view('students::edit', compact('students', 'pageTitle'));
    }

    public function update(studentRequest $request, $id)
    {
        $studentModel = $this->studentRepository->find($id);
        if (empty($studentModel)) abort(404);

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
                logName: 'Cập nhập',
                description: 'Cập nhật học viên'
            );

            return back()->with('msg', __('students::messages.update.success'));
        }

        return back()->with('msg_danger', __('students::messages.update.failure'));
    }


    public function delete($id)
    {
        $student = $this->studentRepository->find($id);
        if (empty($student)) abort(404);

        $snapshot = $student->toArray();
        unset($snapshot['password']);

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


    public function CouponHistory($id)
    {
        $pageTitle = 'Lịch sử mã giảm giá học viên';

        $student = Student::with([
            'coupons'
        ])->findOrFail($id);
        return view(
            'students::coupon_history',
            compact('student', 'pageTitle')
        );
    }

    public function purchasedCourses($id)
    {
        $pageTitle = 'Khóa học đã mua';

        $student = $this->studentRepository->find($id);

        $courses = $this->studentRepository
            ->getPurchasedCourses($id, config('paginate.limit'));

        return view(
            'students::course_student',
            compact('student', 'courses', 'pageTitle')
        );
    }

    public function logs(Request $request, $id)
    {
        $student = $this->studentRepository->find($id);
        if (empty($student)) abort(404);

        $pageTitle = "Lịch sử: {$student->name}";

        $query = ActiveLog::query()
            ->where('subject_type', get_class($student))
            ->where('subject_id', $student->id)->withoutGlobalScopes();

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
            ->paginate(config('paginate.log_limit'))
            ->withQueryString();

        return view('students::logs', compact('pageTitle', 'student', 'logs'));
    }
}
