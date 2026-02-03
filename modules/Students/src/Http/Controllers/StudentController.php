<?php

namespace Modules\Students\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
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
            ->rawColumns(['edit', 'delete', 'status', 'link', 'courses'])
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
            'name' => $request->name,
            'email' => $request->email,
            'status' => $request->status,
            'password' => bcrypt($request->password),
            'address' => $request->address,
            'phone' => $request->phone
        ];
        $this->studentRepository->create($dataInsert);

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
        $data = $request->except('_token', 'password');

        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }

        $status = $this->studentRepository->update($id, $data);
        if (!empty($status)) {
            return back()->with('msg', __('students::messages.update.success'));
        } else {
            return back()->with('msg_danger', __('students::messages.update.failure'));
        }
    }

    public function delete($id)
    {
        $students = $this->studentRepository->find($id);
        if (empty($students)) {
            abort(404);
        }
        $this->studentRepository->delete($id);
        return back()->with('msg', __('students::messages.delete.success'));
    }

    public function CouponHistory($id)
    {
        $pageTitle = 'Cập nhập học viên';

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
}
