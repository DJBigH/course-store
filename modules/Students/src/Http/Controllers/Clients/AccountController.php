<?php

namespace Modules\Students\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
    public function __construct(StudentsRepositoryInterface $studentRepository, TeacherRepositoryInterface $teacherRepository, OrdersRepositoryInterface $orderRepository, OrdersStatusRepositoryInterface $ordersStatusRepository)
    {
        $this->studentRepository = $studentRepository;
        $this->teacherRepository = $teacherRepository;
        $this->orderRepository = $orderRepository;
        $this->ordersStatusRepository = $ordersStatusRepository;
    }
    public function index()
    {
        $pageTitle = 'Thông tin tài khoản';
        $pageName = 'Thông tin tài khoản';
        return view('students::clients.account', compact('pageTitle', 'pageName'));
    }

    public function profile()
    {
        $pageTitle = 'Thông tin cá nhân';
        $pageName = 'Thông tin cá nhân';

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
        return ['success' => $status];
    }


    public function myCourse(Request $request)
    {
        $pageTitle = 'Khóa học của tôi';
        $pageName = 'Khóa học của tôi';

        $filters = [];
        if ($request->teacher_id) {
            $filters['teacher_id'] = $request->teacher_id;
        }

        if ($request->keyword) {
            $filters['keyword'] = $request->keyword;
        }

        $studentId = Auth::guard('students')->user()->id;
        $courses = $this->studentRepository->getCourses($studentId, $filters, config('pagination.account_limit'));
        $teacher = $this->teacherRepository->getTeachers();
        return view('students::clients.my_courses', compact('pageTitle', 'pageName', 'courses', 'teacher'));
    }

    public function myCoupon(Request $request)
    {
        $pageTitle = 'Khóa học của tôi';
        $pageName = 'Khóa học của tôi';
        $filters = [];
        $studentId = Auth::guard('students')->user()->id;
        $coupon = $this->studentRepository->getCoupons($studentId, $filters, config('paginate.coupon_limit'));
        return view('students::clients.my_coupons', compact('pageName', 'pageTitle','coupon'));
    }

    public function myOrder(Request $request)
    {
        $pageTitle = 'Đơn hàng';
        $pageName = 'Đơn hàng';

        $filters = [];
        if ($request->status_id) {
            $filters['status_id'] = $request->status_id;
        }

        if ($request->start_date) {
            $filters['start_date'] = Carbon::parse($request->start_date)->format('Y-m-d');
        }

        if ($request->end_date) {
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

    public function orderDetail($orderId)
    {
        $pageTitle = 'Chi tiết đơn hàng';
        $pageName = 'Chi tiết đơn hàng';
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
        $pageTitle = 'Đổi mật khẩu';
        $pageName = 'Đổi mật khẩu';
        return view('students::clients.change_password', compact('pageTitle', 'pageName'));
    }

    public function updatePassword(PasswordRequest $request)
    {
        $id = Auth::guard('students')->user()->id;
        $status = $this->studentRepository->setPassword($request->password, $id);
        if ($status) {
            $message = __('students::messages.update-password.success');
        } else {
            $message = __('students::messages.update-password.failure');
        }
        return back()->with('msg', $message)->with('msgType', $status ? 'success' : 'danger');
    }
}
