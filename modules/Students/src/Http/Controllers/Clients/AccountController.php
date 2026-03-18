<?php

namespace Modules\Students\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Mail\AccountDeactivatedMail;
use App\Models\Scopes\ActiveScope;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Modules\Orders\src\Repositories\OrdersRepositoryInterface;
use Modules\Orders\src\Repositories\OrdersStatusRepositoryInterface;
use Modules\Students\src\Http\Requests\Clients\PasswordRequest;
use Modules\Students\src\Http\Requests\Clients\StudentsRequest;
use Modules\Students\src\Http\Requests\studentRequest;
use Modules\Students\src\Repositories\StudentsRepositoryInterface;
use Modules\Teacher\src\Repositories\TeacherRepositoryInterface;

class AccountController extends Controller
{
    protected $studentRepository;
    private $teacherRepository;
    private $orderRepository;
    private $ordersStatusRepository;

    public function __construct(
        StudentsRepositoryInterface $studentRepository,
        TeacherRepositoryInterface $teacherRepository,
        OrdersRepositoryInterface $orderRepository,
        OrdersStatusRepositoryInterface $ordersStatusRepository
    ) {
        $this->studentRepository = $studentRepository;
        $this->teacherRepository = $teacherRepository;
        $this->orderRepository = $orderRepository;
        $this->ordersStatusRepository = $ordersStatusRepository;
    }

    public function index()
    {
        $pageTitle = __('students::clients/account.account.title');
        $pageName = $pageTitle;
        $student = Auth::guard('students')->user();

        $totalCourses = $student->courses()->count();
        $totalCoupons = $student->coupons()->count();
        $totalOrders = $student->orders()->count();

        $recentCourses = $student->courses()
            ->with('teacher')
            ->latest('created_at')
            ->take(3)
            ->get();

        $recentOrders = $student->orders()
            ->with(['status', 'detail.courses'])
            ->latest('created_at')
            ->take(3)
            ->get();

        return view('students::clients.account', compact(
            'pageTitle',
            'pageName',
            'totalCoupons',
            'totalCourses',
            'totalOrders',
            'recentCourses',
            'recentOrders'
        ));
    }

    public function profile($locale)
    {
        $pageTitle = __('students::clients/account.profile.title');
        $pageName = $pageTitle;
        $student = Auth::guard('students')->user();

        return view('students::clients.profile', compact('pageTitle', 'pageName', 'student'));
    }

    public function updateProfile(StudentsRequest $request)
    {
        $id = Auth::guard('students')->user()->id;
        $status = $this->studentRepository->update($id, [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $status,
                'message' => $status
                    ? __('students::clients/messages.profile.update_success')
                    : __('students::clients/messages.profile.update_error'),
                'student' => [
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'address' => $request->address,
                ],
            ], $status ? 200 : 422);
        }

        return ['success' => $status];
    }

    public function showDeactivateConfirm($locale)
    {
        $pageTitle = __('students::clients/account.profile.deactivate_page_title');
        $pageName = $pageTitle;
        $student = Auth::guard('students')->user();

        return view('students::clients.deactivate_confirm', compact('pageTitle', 'pageName', 'student'));
    }

    public function deactivate($locale)
    {
        $student = Auth::guard('students')->user();

        $student->forceFill([
            'email_verified_at' => null,
        ])->save();

        Mail::to($student->email)
            ->locale($locale)
            ->queue(new AccountDeactivatedMail($student, $locale));

        return redirect()
            ->route('students.account.deactivate-success', ['locale' => $locale])
            ->with('account_deactivated_success', true)
            ->with('msg', __('students::clients/account.profile.deactivate_success'));
    }

    public function deactivateSuccess($locale)
    {
        if (!session('account_deactivated_success')) {
            return redirect()->route('home', ['locale' => $locale]);
        }

        $pageTitle = __('students::clients/account.profile.deactivate_success_title');
        $pageName = $pageTitle;

        return view('students::clients.deactivate_success', compact('pageTitle', 'pageName'));
    }

    public function myCourse(Request $request)
    {
        $pageTitle = __('students::clients/account.my_course.title');
        $pageName = $pageTitle;

        $filters = [];
        if ($request->teacher_id) {
            $filters['teacher_id'] = $request->teacher_id;
        }

        if ($request->keyword) {
            $filters['keyword'] = $request->keyword;
        }

        $studentId = Auth::guard('students')->user()->id;
        $courses = $this->studentRepository->getCourses($studentId, $filters, config('pagination.account_limit'));
        $student = Auth::guard('students')->user();
        $teacher = $student->courses()
            ->withoutGlobalScope(ActiveScope::class)
            ->with('teacher')
            ->get()
            ->pluck('teacher')
            ->filter()
            ->unique('id')
            ->sortBy('name_locale')
            ->values();

        return view('students::clients.my_courses', compact('pageTitle', 'pageName', 'courses', 'teacher'));
    }

    public function myCoupon(Request $request)
    {
        $pageTitle = __('students::clients/account.coupons.title');
        $pageName = $pageTitle;
        $filters = [];
        $studentId = Auth::guard('students')->user()->id;
        $coupon = $this->studentRepository->getCoupons($studentId, $filters, config('paginate.coupon_limit'));

        return view('students::clients.my_coupons', compact('pageName', 'pageTitle', 'coupon'));
    }

    public function myOrder(Request $request)
    {
        $pageTitle = __('students::clients/account.order.title');
        $pageName = $pageTitle;

        $validator = Validator::make($request->only(['start_date', 'end_date']), [
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $filters = [];
        if ($request->status_id) {
            $filters['status_id'] = $request->status_id;
        }

        if (!$validator->fails() && $request->start_date) {
            $filters['start_date'] = Carbon::parse($request->start_date)->format('Y-m-d');
        }

        if (!$validator->fails() && $request->end_date) {
            $filters['end_date'] = Carbon::parse($request->end_date)->format('Y-m-d');
        }

        if ($request->total) {
            $filters['total'] = $request->total;
        }

        if ($request->code) {
            $filters['code'] = $request->code;
        }

        $studentId = Auth::guard('students')->user()->id;
        $orders = $this->orderRepository->getOrdersByStudent($studentId, $filters, config('pagination.orders_limit'));
        $ordersStatus = $this->ordersStatusRepository->getOrdersStatus();

        return view('students::clients.my_order', compact('pageTitle', 'pageName', 'orders', 'ordersStatus'));
    }

    public function orderDetail($locale, $orderId)
    {
        $pageTitle = __('students::clients/account.order_detail.title');
        $pageName = $pageTitle;
        $order = $this->orderRepository->getOrder($orderId);

        if (!$order) {
            abort(404);
        }

        $now = strtotime(date('Y-m-d H:i:s'));
        $paymentDate = strtotime($order->payment_date);
        $driff = $now - $paymentDate;
        if ($driff > 30) {
            $order->expired = true;
        }

        return view('students::clients.order_detail', compact('pageTitle', 'pageName', 'order'));
    }

    public function changePassword()
    {
        $pageTitle = __('students::clients/account.change_password.title');
        $pageName = $pageTitle;

        return view('students::clients.change_password', compact('pageTitle', 'pageName'));
    }

    public function updatePassword(PasswordRequest $request)
    {
        $id = Auth::guard('students')->user()->id;
        $status = $this->studentRepository->setPassword($request->password, $id);
        if ($status) {
            $message = __('students::clients/messages.update-password.success');
        } else {
            $message = __('students::clients/messages.update-password.failure');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $status,
                'message' => $message,
            ], $status ? 200 : 422);
        }

        return back()->with('msg', $message)->with('msgType', $status ? 'success' : 'danger');
    }
}
