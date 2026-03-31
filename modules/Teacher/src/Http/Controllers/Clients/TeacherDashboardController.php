<?php

namespace Modules\Teacher\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Courses\src\Models\Courses;
use Modules\Orders\src\Models\OrderDetail;
use Modules\Teacher\src\Models\Teacher;
use Modules\Teacher\src\Models\TeacherPayoutRequest;
use Modules\Teacher\src\Support\TeacherFinanceCalculator;

class TeacherDashboardController extends Controller
{
    public function index()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $coursesQuery = Courses::query()
            ->withoutGlobalScopes()
            ->withTrashed()
            ->where('teacher_id', $teacher->id);

        $orderDetails = $this->paidOrderDetailsQuery($teacher)->get();
        $summary = TeacherFinanceCalculator::summarize(
            $orderDetails,
            fn () => (float) $teacher->commission_rate
        );
        $payoutRequested = (float) TeacherPayoutRequest::query()
            ->where('teacher_id', $teacher->id)
            ->sum('amount');

        $pageTitle = 'Bang dieu khien giang vien';
        $pageName = 'Bang dieu khien giang vien';
        $stats = [
            'courses' => (clone $coursesQuery)->count(),
            'active_courses' => (clone $coursesQuery)->where('status', 1)->count(),
            'students' => $orderDetails->pluck('order.student_id')->filter()->unique()->count(),
            'gross_revenue' => $summary['gross_amount'],
            'allocated_discount' => $summary['allocated_discount'],
            'estimated_revenue' => $summary['teacher_revenue'],
            'platform_revenue' => $summary['platform_revenue'],
            'available_balance' => max($summary['teacher_revenue'] - $payoutRequested, 0),
        ];
        $recentCourses = $coursesQuery->latest('id')->take(4)->get();
        $recentSales = TeacherFinanceCalculator::decorate($orderDetails->sortByDesc('created_at')->take(6)->values(), fn () => (float) $teacher->commission_rate);

        return view('teacher::clients.dashboard.index', compact('pageTitle', 'pageName', 'teacher', 'stats', 'recentCourses', 'recentSales'));
    }

    public function courses()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $pageTitle = 'Khoa hoc cua toi';
        $pageName = 'Khoa hoc cua toi';
        $courses = Courses::query()
            ->withoutGlobalScopes()
            ->withTrashed()
            ->withCount(['lessons', 'students'])
            ->where('teacher_id', $teacher->id)
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('teacher::clients.dashboard.courses', compact('pageTitle', 'pageName', 'teacher', 'courses'));
    }

    public function earnings()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $pageTitle = 'Doanh thu giang vien';
        $pageName = 'Doanh thu giang vien';
        $items = $this->paidOrderDetailsQuery($teacher)->paginate(12)->withQueryString();
        $summary = TeacherFinanceCalculator::summarize(
            $this->paidOrderDetailsQuery($teacher)->get(),
            fn () => (float) $teacher->commission_rate
        );
        $items->setCollection(TeacherFinanceCalculator::decorate(
            $items->getCollection(),
            fn () => (float) $teacher->commission_rate
        ));

        return view('teacher::clients.dashboard.earnings', compact('pageTitle', 'pageName', 'teacher', 'items', 'summary'));
    }

    public function payouts()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $pageTitle = 'Rut tien';
        $pageName = 'Rut tien';
        $summary = TeacherFinanceCalculator::summarize(
            $this->paidOrderDetailsQuery($teacher)->get(),
            fn () => (float) $teacher->commission_rate
        );
        $requestedAmount = (float) TeacherPayoutRequest::query()->where('teacher_id', $teacher->id)->sum('amount');
        $availableBalance = max($summary['teacher_revenue'] - $requestedAmount, 0);
        $payouts = TeacherPayoutRequest::query()
            ->where('teacher_id', $teacher->id)
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('teacher::clients.dashboard.payouts', compact('pageTitle', 'pageName', 'teacher', 'payouts', 'summary', 'requestedAmount', 'availableBalance'));
    }

    public function storePayout(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:10000'],
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account_name' => ['required', 'string', 'max:120'],
            'bank_account_number' => ['required', 'string', 'max:50'],
            'note' => ['nullable', 'string'],
        ]);

        $summary = TeacherFinanceCalculator::summarize(
            $this->paidOrderDetailsQuery($teacher)->get(),
            fn () => (float) $teacher->commission_rate
        );
        $requestedAmount = (float) TeacherPayoutRequest::query()->where('teacher_id', $teacher->id)->sum('amount');
        $availableBalance = max($summary['teacher_revenue'] - $requestedAmount, 0);

        if ((float) $data['amount'] > $availableBalance) {
            return back()->with('msg_danger', 'So tien yeu cau rut lon hon so du kha dung.');
        }

        TeacherPayoutRequest::query()->create(array_merge($data, [
            'teacher_id' => $teacher->id,
            'status' => 'requested',
        ]));

        return redirect()->route('teacher.dashboard.payouts')
            ->with('msg_success', 'Da gui yeu cau rut tien thanh cong.');
    }

    private function resolveTeacher(): ?Teacher
    {
        $student = auth('students')->user();

        return Teacher::query()
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->first();
    }

    private function redirectToStatus()
    {
        return redirect()->route('teacher.account.status', ['locale' => session('locale', app()->getLocale())])
            ->with('msg_danger', 'Tai khoan cua ban chua duoc kich hoat khu giang vien.');
    }

    private function paidOrderDetailsQuery(Teacher $teacher)
    {
        return OrderDetail::query()
            ->with(['courses', 'order.status', 'order.students'])
            ->whereHas('courses', function ($query) use ($teacher) {
                $query->withoutGlobalScopes()->withTrashed()->where('teacher_id', $teacher->id);
            })
            ->whereHas('order', function ($query) {
                $query->where('status_id', 2);
            })
            ->latest('id');
    }
}


