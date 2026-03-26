<?php

namespace Modules\Coupons\src\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Notifications\CouponStudentNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Modules\ActiveLogs\src\Models\ActiveLog;
use Modules\Coupons\src\Http\Requests\CouponRequest;
use Modules\Courses\src\Models\Courses;
use Modules\Students\src\Models\Coupons;
use Modules\Students\src\Models\CouponUsage;
use Modules\Students\src\Models\Student;
use Modules\Students\src\Repositories\CouponsRepositoryInterface;
use Modules\User\src\Models\User;

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
            ->addColumn('select', function ($coupon) {
                return '<div class="form-check m-0 d-flex justify-content-center"><input type="checkbox" class="form-check-input bulk-row-checkbox" value="' . $coupon->id . '"></div>';
            })
            ->addColumn('logs', function ($coupon) {
                return '<a href="' . route('coupons.logs', $coupon->id) . '" class="btn btn-sm btn-secondary">
                <i class="fas fa-clock"></i>
            </a>';
            })
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
                return '<a href="' . route('coupons.delete', $coupon->id) . '" class="btn btn-outline-danger delete-action">Xóa</a>';
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
            ->rawColumns(['select', 'edit', 'delete', 'discount_type', 'discount_value', 'time', 'bindings', 'count', 'logs'])
            ->make(true);
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
                'bulk_action' => 'Vui lòng chọn ít nhất một mã giảm giá.',
            ]);
        }

        $coupons = Coupons::query()->whereIn('id', $selectedIds)->get();

        if ($coupons->isEmpty()) {
            return back()->with('msg_danger', 'Không tìm thấy mã giảm giá để xử lý.');
        }

        if ($action === 'duplicate') {
            $duplicatedCount = 0;

            foreach ($coupons as $coupon) {
                $newCoupon = Coupons::query()->create([
                    'code' => $this->duplicateCouponCode($coupon->code),
                    'discount_type' => $coupon->discount_type,
                    'discount_value' => $coupon->discount_value,
                    'total_condition' => $coupon->total_condition,
                    'count' => $coupon->count,
                    'start_date' => $coupon->start_date,
                    'end_date' => $coupon->end_date,
                ]);

                $newCoupon->students()->sync($coupon->students()->pluck('students.id')->toArray());
                $newCoupon->courses()->sync($coupon->courses()->pluck('courses.id')->toArray());

                activity_log(
                    action: 'duplicate',
                    subject: $newCoupon,
                    properties: [
                        'source_coupon_id' => $coupon->id,
                        'new_coupon_id' => $newCoupon->id,
                    ],
                    logName: 'Nhân bản hàng loạt',
                    description: 'Nhân bản mã giảm giá'
                );

                $duplicatedCount++;
            }

            return back()->with('msg', 'Đã nhân bản ' . $duplicatedCount . ' mã giảm giá.');
        }

        if ($action === 'delete') {
            foreach ($coupons as $coupon) {
                $snapshot = $coupon->toArray();
                $this->couponRepository->delete($coupon->id);

                activity_log(
                    action: 'delete',
                    subject: $coupon,
                    properties: ['data' => $snapshot],
                    logName: 'Xóa hàng loạt',
                    description: 'Xóa mã giảm giá'
                );
            }

            return back()->with('msg', 'Đã xóa ' . $coupons->count() . ' mã giảm giá.');
        }

        return back()->with('msg_danger', 'Thao tác hàng loạt không hợp lệ.');
    }

    public function create()
    {
        $pageTitle = 'Thêm mã giảm giá';
        return view('coupons::create', compact('pageTitle'));
    }

    public function store(CouponRequest $request)
    {
        $data = $request->except(['_token']);

        $coupon = $this->couponRepository->create($data);
        if (!$coupon) abort(404);

        activity_log(
            action: 'create',
            subject: $coupon,
            properties: ['data' => $data],
            logName: 'Thêm mới',
            description: 'Tạo mới mã giảm giá'
        );

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
        $coupon = $this->couponRepository->find($id);
        if (!$coupon) abort(404);

        $old = $coupon->toArray();

        $data = $request->except(['_token']);
        $this->couponRepository->update($id, $data);

        $fresh = $this->couponRepository->find($id);
        $new = $fresh ? $fresh->toArray() : [];

        \activity_log(
            action: 'update',
            subject: $fresh ?? $coupon,
            properties: [
                'old' => $old,
                'new' => $new,
            ],
            logName: 'Cập nhập',
            description: 'Cập nhật mã giảm giá'
        );

        return redirect()->route('coupons.index')->with('msg', __('coupons::messages.update.success'));
    }


    public function delete($id)
    {
        $coupon = $this->couponRepository->find($id);
        if (!$coupon) abort(404);

        $snapshot = $coupon->toArray();

        $this->couponRepository->delete($id);

        activity_log(
            action: 'delete',
            subject: $coupon,
            properties: ['data' => $snapshot],
            logName: 'Xóa',
            description: 'Xóa mã giảm giá'
        );

        return back()->with('msg', __('coupons::messages.delete.success'));
    }


    protected function duplicateCouponCode(?string $code): string
    {
        return trim(($code ?: 'COUPON') . '-COPY-' . strtoupper(Str::random(4)));
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

        // ✅ Check hiệu lực coupon
        $now = now();

        // Nếu start_date có thể null (cho dùng ngay) thì xử lý mềm:
        if ($coupon->start_date && Carbon::parse($coupon->start_date)->gt($now)) {
            return back()->with('msg_danger', 'Mã giảm giá chưa tới thời gian hiệu lực');
        }

        // Nếu end_date có thể null (không hết hạn) thì xử lý mềm:
        if ($coupon->end_date && Carbon::parse($coupon->end_date)->lt($now)) {
            return back()->with('msg_danger', 'Mã giảm giá đã hết hạn, không thể gán cho học viên');
        }

        $studentIds = $request->input('students', []);

        if (!is_array($studentIds)) {
            return back()->with('msg_danger', 'Dữ liệu không hợp lệ');
        }

        $syncData = [];
        foreach ($studentIds as $studentId) {
            $syncData[$studentId] = [
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $result = $coupon->students()->sync($syncData);

        $attachedStudentIds = $result['attached'] ?? [];
        $detachedStudentIds = $result['detached'] ?? [];

        if (!empty($attachedStudentIds)) {
            $students = Student::select('id', 'name', 'email')
                ->whereIn('id', $attachedStudentIds)
                ->get();

            // ✅ LOG trên COUPON (1 dòng log cho coupon)
            activity_log(
                action: 'assign_students',
                subject: $coupon,
                properties: [
                    'coupon_id'   => $coupon->id,
                    'coupon_code' => $coupon->code ?? $coupon->name ?? null,
                    'students'    => $students->map(fn($s) => [
                        'id' => $s->id,
                        'name' => $s->name,
                        'email' => $s->email,
                    ])->values()->all(),
                ],
                logName: 'Gán mã',
                description: 'Gán mã giảm giá cho học viên'
            );

            // ✅ LOG trên từng STUDENT (mỗi học viên 1 log)
            foreach ($students as $student) {
                activity_log(
                    action: 'assigned_coupon',
                    subject: $student,
                    properties: [
                        'coupon_id'   => $coupon->id,
                        'coupon_code' => $coupon->code ?? $coupon->name ?? null,
                        'coupon_name' => $coupon->name ?? null,
                    ],
                    logName: 'Gán mã',
                    description: 'Được gán mã giảm giá'
                );
            }
        }

        if (!empty($detachedStudentIds)) {
            $students = Student::select('id', 'name', 'email')
                ->whereIn('id', $detachedStudentIds)
                ->get();

            // ✅ LOG trên COUPON
            activity_log(
                action: 'revoke_students',
                subject: $coupon,
                properties: [
                    'coupon_id'   => $coupon->id,
                    'coupon_code' => $coupon->code ?? $coupon->name ?? null,
                    'students'    => $students->map(fn($s) => [
                        'id' => $s->id,
                        'name' => $s->name,
                        'email' => $s->email,
                    ])->values()->all(),
                ],
                logName: 'Hủy mã',
                description: 'Hủy gán mã khỏi học viên'
            );

            // ✅ LOG trên từng STUDENT
            foreach ($students as $student) {
                activity_log(
                    action: 'revoked_coupon',
                    subject: $student,
                    properties: [
                        'coupon_id'   => $coupon->id,
                        'coupon_code' => $coupon->code ?? $coupon->name ?? null,
                        'coupon_name' => $coupon->name ?? null,
                    ],
                    logName: 'Hủy mã',
                    description: 'Bị hủy mã giảm giá'
                );
            }
        }

        // ✅ Chỉ gửi noti cho học viên được gán mới
        $attachedStudentIds = $result['attached'] ?? [];
        if (!empty($attachedStudentIds)) {
            Student::whereIn('id', $attachedStudentIds)
                ->chunk(100, function ($students) use ($coupon) {
                    foreach ($students as $student) {
                        $student->notify(new CouponStudentNotification($coupon));
                    }
                });
        }

        return redirect()
            ->route('coupons.coupons-student', $id)
            ->with('msg', 'Cập nhật học viên thành công');
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

        $result = $coupon->courses()->sync($syncData);

        $attachedCourseIds = $result['attached'] ?? [];
        $detachedCourseIds = $result['detached'] ?? [];
        $updatedCourseIds  = $result['updated'] ?? [];

        if (!empty($attachedCourseIds)) {
            activity_log(
                action: 'assign_courses',
                subject: $coupon,
                properties: ['course_ids' => $attachedCourseIds],
                logName: 'Gán mã',
                description: 'Gán mã cho khóa học'
            );
        }

        if (!empty($detachedCourseIds)) {
            activity_log(
                action: 'revoke_courses',
                subject: $coupon,
                properties: ['course_ids' => $detachedCourseIds],
                logName: 'Hủy gán mã',
                description: 'Hủy mã khỏi khóa học'
            );
        }


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
        $pageTitle = __('coupons::clients/common.pageName');
        $pageName  = __('coupons::clients/common.pageName');

        $student = Auth::guard('students')->user();

        $myCoupons = $student
            ? $student->coupons()
                ->active()
                ->paginate(config('paginate.mycoupon_limit'), ['*'], 'my_page')
            : null;

        $courseCoupons = Coupons::query()
            ->active()
            ->whereHas('courses')
            ->with('courses')
            ->paginate(config('paginate.mycoupon_limit'), ['*'], 'course_page');

        $publicCoupons = Coupons::query()
            ->active()
            ->whereDoesntHave('students')
            ->whereDoesntHave('courses')
            ->paginate(config('paginate.mycoupon_limit'), ['*'], 'public_page');

        return view('coupons::Clients.coupon', compact(
            'pageName',
            'pageTitle',
            'myCoupons',
            'courseCoupons',
            'publicCoupons'
        ));
    }

    public function logs(Request $request, $id)
    {
        $coupons = $this->couponRepository->find($id);
        if (empty($coupons)) abort(404);

        $pageTitle = "Lịch sử: {$coupons->code}";

        $query = ActiveLog::query()
            ->where('subject_type', get_class($coupons))
            ->where('subject_id', $coupons->id)->withoutGlobalScopes();

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

        return view('coupons::logs', compact('pageTitle', 'coupons', 'logs'));
    }
}
