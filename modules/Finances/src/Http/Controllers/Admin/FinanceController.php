<?php

namespace Modules\Finances\src\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Finances\src\Repositories\FinancesRepositoryInterface;
use Modules\Teacher\src\Models\Teacher;

class FinanceController extends Controller
{
    protected $financesRepo;

    public function __construct(FinancesRepositoryInterface $financesRepo)
    {
        $this->financesRepo = $financesRepo;
    }

    public function earnings(Request $request)
    {
        $pageTitle = 'Đối soát doanh thu giảng viên';
        $filters = $request->only(['teacher_id', 'from_date', 'to_date', 'currency']);
        $currency = $filters['currency'] ?? 'ALL';
        
        $items = $this->financesRepo->getAdminEarnings($filters);
        
        // Fetch summary for top cards
        $summary = $this->financesRepo->getEarningsSummary($filters['teacher_id'] ?? 0, null, $filters['from_date'] ?? null, $filters['to_date'] ?? null, $currency);
        
        // Summarize by teacher for the second table
        $teacherSummaries = collect();
        if (empty($filters['teacher_id'])) {
            // Note: Use has('courses') because has('order_details') might not be mapped correctly
            $teachers = Teacher::has('courses')->get();
            foreach ($teachers as $teacher) {
                $teacherSummary = $this->financesRepo->getEarningsSummary($teacher->id, null, $filters['from_date'] ?? null, $filters['to_date'] ?? null, $currency);
                if ($teacherSummary['gross_amount'] > 0) {
                    $teacherSummaries->push(array_merge($teacherSummary, [
                        'teacher' => $teacher, 
                        'orders_count' => \Modules\Orders\src\Models\OrderDetail::whereHas('courses', fn($q) => $q->where('teacher_id', $teacher->id))->count()
                    ]));
                }
            }
        }

        $teachers = Teacher::all();
        $currencies = collect(['VND', 'USD', 'KRW', 'JPY', 'CNY']);

        return view('finances::admin.earnings', compact('items', 'summary', 'teacherSummaries', 'teachers', 'currencies', 'currency', 'pageTitle'));
    }

    public function payouts(Request $request)
    {
        $pageTitle = 'Xử lý rút tiền & Tài khoản';
        $filters = $request->only(['status', 'account_change_status']);
        $payouts = $this->financesRepo->getAdminPayouts($filters);
        $summary = $this->financesRepo->getAdminPayoutSummary();
        
        // Account change requests pagination
        $accountChangeRequests = \Modules\Finances\src\Models\PayoutAccountChangeRequest::with(['teacher.student', 'replaceAccount'])
            ->when(!empty($filters['account_change_status']), fn($q) => $q->where('status', $filters['account_change_status']))
            ->latest()
            ->paginate(10, ['*'], 'acc_change_page');

        // Teacher payout summaries
        $teacherSummaries = collect();
        $teacherIds = \Modules\Finances\src\Models\PayoutRequest::distinct()->pluck('teacher_id');
        $teachers = Teacher::whereIn('id', $teacherIds)->get();
        foreach ($teachers as $teacher) {
            $teacherSummaries->push([
                'teacher' => $teacher,
                'requested' => \Modules\Finances\src\Models\PayoutRequest::where('teacher_id', $teacher->id)->where('status', 'requested')->sum('amount'),
                'processing' => \Modules\Finances\src\Models\PayoutRequest::where('teacher_id', $teacher->id)->where('status', 'processing')->sum('amount'),
                'paid' => \Modules\Finances\src\Models\PayoutRequest::where('teacher_id', $teacher->id)->where('status', 'paid')->sum('amount'),
                'rejected' => \Modules\Finances\src\Models\PayoutRequest::where('teacher_id', $teacher->id)->where('status', 'rejected')->sum('amount'),
            ]);
        }

        $teachers = Teacher::all();

        return view('finances::admin.payouts', compact('payouts', 'summary', 'accountChangeRequests', 'teacherSummaries', 'teachers', 'pageTitle'));
    }


    public function updatePayout(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:processing,paid,rejected',
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $this->financesRepo->updatePayoutStatus($id, $request->all());

        return back()->with('msg', __('finances::admin.messages.payout_update_success'));
    }

    public function updatePayoutAccountChangeRequest(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $success = $this->financesRepo->updateAccountChangeRequest($id, $request->all());

        if (!$success) {
            return back()->with('msg', __('finances::admin.messages.payout_change_processed'));
        }

        return back()->with('msg', __('finances::admin.messages.payout_account_change_success'));
    }

    public function exportEarnings(Request $request, $format)
    {
        $filters = $request->only(['teacher_id', 'from_date', 'to_date']);
        $items = $this->financesRepo->getAdminEarnings(array_merge($filters, ['per_page' => 10000]))->getCollection();

        if ($format === 'csv') {
            // Implementation of CSV export
        }

        return view('finances::admin.exports.earnings_excel', compact('items'));
    }

    public function exportPayouts(Request $request, $format)
    {
        $filters = $request->only(['status']);
        $payouts = $this->financesRepo->getAdminPayouts(array_merge($filters, ['per_page' => 10000]))->getCollection();

        if ($format === 'csv') {
            // Implementation of CSV export
        }

        return view('finances::admin.exports.payouts_excel', compact('payouts'));
    }
}
