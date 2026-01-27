<?php

namespace Modules\Coupons\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Coupons\src\Http\Requests\CouponRequest;
use Modules\Courses\src\Models\Courses;
use Modules\Students\src\Models\Coupons;
use Modules\Students\src\Models\CouponUsage;
use Modules\Students\src\Models\Student;
use Modules\Students\src\Repositories\CouponsRepositoryInterface;

class CouponController extends Controller
{
    protected $couponRepository;

    public function __construct(CouponsRepositoryInterface $couponRepository)
    {
        $this->couponRepository = $couponRepository;
    }
    public function index()
    {
        $pageTitle = 'Quản lý mã giảm giá';
        return view('coupons::lists', compact('pageTitle'));
    }

    public function data()
    {
        $coupons = $this->couponRepository->getAllCoupons();
        return datatables()->of($coupons)
            ->addColumn('discount_type', function ($coupon) {
                return $coupon->discount_type === 'percent'
                    ? '<span class="badge bg-info">%</span>'
                    : '<span class="badge bg-success">Tiền</span>';
            })
            ->addColumn('discount_value', function ($coupon) {
                if ($coupon->discount_type === 'value') {
                    return '- ' . money($coupon->discount_value);
                }

                if ($coupon->discount_type === 'percent') {
                    return '- ' . $coupon->discount_value . ' %';
                }

                return '';
            })
            ->addColumn('count', function ($coupon) {
                if (empty($coupon->count)) {
                    return '<span class="badge bg-secondary">Không giới hạn</span>';
                }

                $used = $coupon->usagescoupon_count ?? 0;
                $remaining = max($coupon->count - $used, 0);

                return '
                        <span class="badge bg-primary">
                            Còn lại: ' . $remaining . '
                        </span>
                        <br>
                        <small class="text-muted">
                            Đã dùng: ' . $used . '/' . $coupon->count . '
                        </small>
                        ';
            })
            ->addColumn('time', function ($coupon) {
                if (!$coupon->start_date || !$coupon->end_date) {
                    return '<span class="text-muted">Chưa thiết lập</span>';
                }
                return Carbon::parse($coupon->start_date)->format('d/m/Y')
                    . ' → '
                    . Carbon::parse($coupon->end_date)->format('d/m/Y');
            })
            ->addColumn('total_condition', function ($coupon) {
                return money($coupon->total_condition) ?? 'Chưa thiết lập';
            })
            ->addColumn('edit', function ($coupon) {
                return '<a href="' . route('coupons.edit', $coupon->id) . '" class="btn btn-sm btn-warning">Sửa</a>';
            })
            ->addColumn('delete', function ($coupon) {
                return '<a href="' . route('coupons.delete', $coupon->id) . '" class="btn btn-danger delete-action">Xóa</a>';
            })
            ->addColumn('bindings', function ($coupon) {
                return '
                        <a href="' . route('coupons.coupons-history', $coupon->id) . '" 
                        class="btn btn-sm btn-info mb-1">
                            <i class="fas fa-history"></i>
                        </a>

                        <a href="' . route('coupons.coupons-student', $coupon->id) . '" 
                        class="btn btn-sm btn-warning mb-1">
                            <i class="fas fa-user"></i>
                        </a>

                        <a href="' . route('coupons.coupons-course', $coupon->id) . '" 
                        class="btn btn-sm btn-success mb-1">
                            <i class="fas fa-book"></i>
                        </a>
                    ';
            })
            ->rawColumns(['edit', 'delete', 'discount_type', 'discount_value', 'time', 'bindings', 'count'])
            ->make(true);
    }

    public function create()
    {
        $pageTitle = 'Thêm mã giảm giá';
        return view('coupons::create', compact('pageTitle'));
    }

    public function store(CouponRequest $request)
    {
        $coupons = $request->except(['_token']);
        $coupon = $this->couponRepository->create($coupons);
        if (!$coupon) {
            abort(404);
        }
        return redirect()->route('coupons.index')->with('msg', __('coupons::messages.create.success'));
    }

    public function edit($id)
    {
        $pageTitle = 'Chỉnh sửa mã giảm giá';
        $coupon = $this->couponRepository->find($id);
        return view('coupons::edit', compact('pageTitle', 'coupon'));
    }

    public function update(CouponRequest $request, $id)
    {
        $coupons = $request->except(['_token']);
        $this->couponRepository->update($id, $coupons);
        return redirect()->route('coupons.index')->with('msg', __('coupons::messages.update.success'));
    }

    public function delete($id)
    {
        $coupon = $this->couponRepository->find($id);
        if (!$coupon) {
            abort(404);
        }
        $this->couponRepository->delete($id);
        return back()->with('msg', __('coupons::messages.delete.success'));
    }

    public function CouponStudent($id)
    {
        $pageTitle = 'Cấp mã khuyến mãi cho học viên';

        $coupon = $this->couponRepository->find($id);
        if (!$coupon) {
            abort(404);
        }
        // Lấy toàn bộ học viên
        $students = Student::orderBy('id', 'desc')->get();

        // Các học viên đã được gán mã
        $assignedStudentIds = $coupon->students()
            ->pluck('students.id')
            ->toArray();

        return view('coupons::coupon_student', compact(
            'pageTitle',
            'coupon',
            'students',
            'assignedStudentIds'
        ));
    }

    public function AssignCouponStudent(Request $request, $id)
    {
        $coupon = $this->couponRepository->find($id);

        if (!$coupon) {
            abort(404);
        }
        // Danh sách học viên được chọn
        $studentIds = $request->input('students', []);

        // Validate (tùy chọn nhưng nên có)
        if (!is_array($studentIds)) {
            return back()->with('msg_danger', 'Dữ liệu không hợp lệ');
        }

        $syncData = [];
        foreach ($studentIds as $studentId) {
            $syncData[$studentId] = [
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        $coupon->students()->sync($syncData);

        return redirect()
            ->route('coupons.coupons-student', $id)
            ->with('msg', 'Cấp mã cho học viên thành công');
    }

    public function CouponCourse($id)
    {
        $pageTitle = 'Cấp mã cho khóa học';

        $coupon = $this->couponRepository->find($id);
        if (!$coupon) abort(404);

        $courses = Courses::orderBy('id', 'desc')->get();

        $assignedCourseIds = $coupon->courses()
            ->pluck('courses.id')
            ->toArray();

        return view('coupons::coupon_course', compact(
            'pageTitle',
            'coupon',
            'courses',
            'assignedCourseIds'
        ));
    }

    public function AssignCouponCourse(Request $request, $id)
    {
        $coupon = $this->couponRepository->find($id);
        if (!$coupon) {
            return back()->with('msg_danger', 'Mã khuyến mãi không tồn tại');
        }

        $courseIds = $request->input('courses', []);

        $syncData = [];
        foreach ($courseIds as $courseId) {
            $syncData[$courseId] = [
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $coupon->courses()->sync($syncData);

        return redirect()
            ->route('coupons.coupons-course', $id)
            ->with('msg', 'Cập nhật khóa học áp dụng mã thành công');
    }

    public function CouponHistory($id)
    {
        $pageTitle = 'Lịch sử dùng mã giảm giá';
        $coupon = $this->couponRepository->find($id);
        if (!$coupon) abort(404);

        $histories = CouponUsage::with(['students', 'order', 'coupon'])
            ->where('coupon_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();
        return view('coupons::coupon_usage', compact(
            'coupon',
            'histories',
            'pageTitle'
        ));
    }

    public function CouponClient()
    {
        $pageTitle = 'Mã giảm giá';
        $pageName = 'Mã giảm giá';
        $student = Auth::guard('students')->user();
        $myCoupons = $student
            ? $student->coupons()->paginate(config('paginate.mycoupon_limit'))
            : collect();

        $courseCoupons = Coupons::whereHas('courses')
            ->with('courses')
            ->paginate(config('paginate.mycoupon_limit'));

        $publicCoupons = Coupons::query()
            ->whereDoesntHave('students')
            ->whereDoesntHave('courses')
            ->paginate(config('paginate.mycoupon_limit'));
        return view('coupons::Clients.coupon', compact(
            'pageName',
            'pageTitle',
            'myCoupons',
            'courseCoupons',
            'publicCoupons'
        ));
    }
}
