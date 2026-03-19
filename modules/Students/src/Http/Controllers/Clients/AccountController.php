<?php

namespace Modules\Students\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Mail\AccountDeactivatedMail;
use App\Models\Scopes\ActiveScope;
use App\Notifications\ResetPasswordChangeNotification;
use App\Support\StudentTwoFactorService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\ActiveLogs\src\Models\ActiveLog;
use Modules\Orders\src\Repositories\OrdersRepositoryInterface;
use Modules\Orders\src\Repositories\OrdersStatusRepositoryInterface;
use Modules\Students\src\Http\Requests\Clients\PasswordRequest;
use Modules\Students\src\Http\Requests\Clients\StudentsRequest;
use Modules\Students\src\Models\Student;
use Modules\Students\src\Repositories\StudentsRepositoryInterface;
use Modules\Teacher\src\Repositories\TeacherRepositoryInterface;

class AccountController extends Controller
{
    protected $studentRepository;
    private $teacherRepository;
    private $orderRepository;
    private $ordersStatusRepository;
    private $twoFactorService;

    public function __construct(
        StudentsRepositoryInterface $studentRepository,
        TeacherRepositoryInterface $teacherRepository,
        OrdersRepositoryInterface $orderRepository,
        OrdersStatusRepositoryInterface $ordersStatusRepository,
        StudentTwoFactorService $twoFactorService
    ) {
        $this->studentRepository = $studentRepository;
        $this->teacherRepository = $teacherRepository;
        $this->orderRepository = $orderRepository;
        $this->ordersStatusRepository = $ordersStatusRepository;
        $this->twoFactorService = $twoFactorService;
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

    public function profile(Request $request)
    {
        $pageTitle = __('students::clients/account.profile.title');
        $pageName = $pageTitle;
        $student = Auth::guard('students')->user();
        $message = session('msg_success') ?? session('msg_danger');

        $editingDisabled = $request->query('editing') === 'disabled' && $message;

        return view('students::clients.profile', compact('pageTitle', 'pageName', 'student', 'editingDisabled'));
    }

    public function updateProfile(StudentsRequest $request)
    {
        $student = Auth::guard('students')->user();
        $oldValues = [
            'name' => (string) $student->name,
            'email' => (string) $student->email,
            'phone' => (string) $student->phone,
            'address' => (string) ($student->address ?? ''),
        ];

        $newValues = [
            'name' => (string) $request->input('name'),
            'email' => (string) $student->email,
            'phone' => (string) $request->input('phone'),
            'address' => (string) ($request->input('address') ?? ''),
        ];

        $this->studentRepository->update($student->id, $newValues);
        $student->refresh();

        activity_log(
            'profile_updated',
            $student,
            [
                'old' => $oldValues,
                'new' => $newValues,
            ],
            'student_profile',
            __('students::clients/account.activity_log.profile_updated_desc')
        );

        $successMessage = __('students::clients/messages.profile.update.success');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'editingDisabled' => true,
                'student' => [
                    'name' => $student->name,
                    'email' => $student->email,
                    'phone' => $student->phone,
                    'address' => $student->address,
                ],
            ]);
        }

        return back()->with('msg_success', $successMessage);
    }

    public function showMyCourse(Request $request)
    {
        $pageTitle = __('students::clients/account.my_course.title');
        $pageName = $pageTitle;
        $student = Auth::guard('students')->user();
        $keyword = trim((string) $request->query('keyword'));
        $teacherId = $request->query('teacher_id');

        $teachers = $student->courses()
            ->with('teacher')
            ->get()
            ->pluck('teacher')
            ->filter()
            ->unique('id')
            ->sortBy('name_locale')
            ->values();

        $courses = $student->courses()->with('teacher');

        if ($teacherId) {
            $courses->where('teacher_id', $teacherId);
        }

        if ($keyword !== '') {
            $locale = app()->getLocale();
            $localizedNameColumn = 'name_' . $locale;
            $localizedDescriptionColumn = 'description_' . $locale;

            $courses->where(function ($query) use ($keyword, $localizedNameColumn, $localizedDescriptionColumn) {
                $query->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('description', 'like', '%' . $keyword . '%');

                if (schema_has_column('courses', $localizedNameColumn)) {
                    $query->orWhere($localizedNameColumn, 'like', '%' . $keyword . '%');
                }

                if (schema_has_column('courses', $localizedDescriptionColumn)) {
                    $query->orWhere($localizedDescriptionColumn, 'like', '%' . $keyword . '%');
                }
            });
        }

        $courses = $courses->latest('student_course.created_at')->paginate(5)->withQueryString();

        return view('students::clients.my_courses', compact('pageTitle', 'pageName', 'courses', 'teachers', 'teacherId', 'keyword'));
    }

    public function myCoupon()
    {
        $pageTitle = __('students::clients/account.coupons.title');
        $pageName = $pageTitle;
        $student = Auth::guard('students')->user();
        $coupons = $student->coupons()->latest()->paginate(3);
        return view('students::clients.my_coupons', compact('pageTitle', 'pageName', 'coupons'));
    }

    public function myOrder(Request $request)
    {
        $pageTitle = __('students::clients/account.order.title');
        $pageName = $pageTitle;
        $student = Auth::guard('students')->user();

        $orders = $student->orders()->latest();
        $ordersStatus = $this->ordersStatusRepository->getAll();

        if ($request->filled('status')) {
            $orders->where('status_id', $request->status);
        }

        if ($request->filled('code_order')) {
            $orders->where('code_order', 'like', '%' . $request->code_order . '%');
        }

        if ($request->filled('start_day')) {
            $orders->whereDate('created_at', '>=', Carbon::parse($request->start_day)->startOfDay());
        }

        if ($request->filled('end_day')) {
            $orders->whereDate('created_at', '<=', Carbon::parse($request->end_day)->endOfDay());
        }

        if ($request->filled('total_price')) {
            $orders->where('total_price', $request->total_price);
        }

        $orders = $orders->paginate(6)->withQueryString();

        return view('students::clients.my_order', compact('pageTitle', 'pageName', 'orders', 'ordersStatus'));
    }

    public function detailOrder($id)
    {
        $pageTitle = __('students::clients/account.order_detail.title');
        $pageName = $pageTitle;
        $student = Auth::guard('students')->user();

        $order = $student->orders()->with([
            'detail.courses.teacher',
            'status',
            'paymentStatus',
            'orderAddress',
            'coupon'
        ])->findOrFail($id);

        return view('students::clients.order_detail', compact('pageTitle', 'pageName', 'order'));
    }

    public function showChangePassword()
    {
        $pageTitle = __('students::clients/account.change_password.title');
        $pageName = $pageTitle;
        $student = Auth::guard('students')->user();
        return view('students::clients.change_password', compact('pageTitle', 'pageName', 'student'));
    }

    public function updatePassword(PasswordRequest $request)
    {
        $student = Auth::guard('students')->user();

        $student->password = bcrypt($request->new_password);
        $student->setRememberToken(Str::random(60));
        $student->save();
        $student->notify(new ResetPasswordChangeNotification());

        activity_log(
            'password_changed',
            $student,
            ['email' => $student->email],
            'student_security',
            __('students::clients/account.activity_log.password_changed_desc')
        );

        $this->twoFactorService->forgetRecentVerification($request);
        Auth::guard('students')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $successMessage = __('students::clients/messages.password.update.success');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $successMessage,
                'redirect' => route('clients-login', ['locale' => app()->getLocale()]),
                'logout' => true,
            ]);
        }

        return redirect()->route('clients-login', ['locale' => app()->getLocale()])->with('msg_success', $successMessage);
    }

    public function deactivateConfirm()
    {
        $pageTitle = __('students::clients/account.profile.deactivate_page_title');
        $pageName = $pageTitle;
        $student = Auth::guard('students')->user();

        return view('students::clients.deactivate_confirm', compact('pageTitle', 'pageName', 'student'));
    }

    public function deactivate(Request $request)
    {
        $student = Auth::guard('students')->user();
        $request->validate([
            'two_factor_ticket' => ['required', 'string'],
        ]);

        $ticketKey = 'students.two_factor.deactivate_ticket';
        if ($request->input('two_factor_ticket') !== $request->session()->get($ticketKey)) {
            abort(403);
        }

        $request->session()->forget($ticketKey);

        $student->forceFill([
            'email_verified_at' => null,
            'email_two_factor_enabled' => false,
            'two_factor_email_code' => null,
            'two_factor_email_purpose' => null,
            'two_factor_email_code_expires_at' => null,
            'two_factor_email_code_sent_at' => null,
        ])->save();

        Mail::to($student->email)->locale(app()->getLocale())->queue(new AccountDeactivatedMail($student, app()->getLocale()));

        activity_log(
            'account_deactivated',
            $student,
            ['email' => $student->email],
            'student_security',
            __('students::clients/account.activity_log.account_deactivated_desc')
        );

        $request->session()->put('students.deactivation.success_logout', true);

        return redirect()->route('students.account.deactivate.success', ['locale' => app()->getLocale()]);
    }

    public function deactivateSuccess()
    {
        if (!session('students.deactivation.success_logout')) {
            return redirect()->route('students.account.profile', ['locale' => app()->getLocale()]);
        }

        $pageTitle = __('students::clients/account.profile.deactivate_success_title');
        $pageName = $pageTitle;

        return view('students::clients.deactivate_success', compact('pageTitle', 'pageName'));
    }

    public function postDeactivateSuccess(Request $request)
    {
        if (!session('students.deactivation.success_logout')) {
            return redirect()->route('home', ['locale' => app()->getLocale()]);
        }

        Auth::guard('students')->logout();
        $request->session()->forget('students.deactivation.success_logout');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home', ['locale' => app()->getLocale()])
            ->with('msg_success', __('students::clients/account.profile.deactivate_success_logout'));
    }

    public function activityHistory(Request $request)
    {
        $pageTitle = __('students::clients/account.activity_history.title');
        $pageName = $pageTitle;
        $student = Auth::guard('students')->user();

        $loginActivitiesQuery = ActiveLog::query()
            ->where('causer_id', $student->id)
            ->where('subject_type', Student::class)
            ->where('subject_id', $student->id)
            ->where('log_name', 'auth_login')
            ->latest();

        $activitiesQuery = ActiveLog::query()
            ->where('causer_id', $student->id)
            ->where('subject_type', Student::class)
            ->where('subject_id', $student->id)
            ->whereIn('log_name', ['student_profile', 'student_security'])
            ->latest();

        if ($request->filled('from_date')) {
            $fromDate = Carbon::parse($request->from_date)->startOfDay();
            $loginActivitiesQuery->where('created_at', '>=', $fromDate);
            $activitiesQuery->where('created_at', '>=', $fromDate);
        }

        if ($request->filled('to_date')) {
            $toDate = Carbon::parse($request->to_date)->endOfDay();
            $loginActivitiesQuery->where('created_at', '<=', $toDate);
            $activitiesQuery->where('created_at', '<=', $toDate);
        }

        if ($request->filled('action')) {
            $activitiesQuery->where('action', $request->action);
        }

        $loginLogs = $loginActivitiesQuery->paginate(5, ['*'], 'login_page')->withQueryString();
        $activityLogs = $activitiesQuery->paginate(8, ['*'], 'activity_page')->withQueryString();

        $availableActions = [
            'profile_updated',
            'password_changed',
            'two_factor_code_sent',
            'two_factor_enabled',
            'two_factor_disabled',
            'account_deactivated',
        ];

        $filters = [
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'action' => $request->action,
        ];

        return view('students::clients.activity_history', compact(
            'pageTitle',
            'pageName',
            'loginLogs',
            'activityLogs',
            'availableActions',
            'filters'
        ));
    }
}
