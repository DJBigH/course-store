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
        $filters = $request->only(['teacher_id', 'from_date', 'to_date']);
        $items = $this->financesRepo->getAdminEarnings($filters);
        
        // Fetch summary for top cards
        $summary = $this->financesRepo::getEarningsSummary($filters['teacher_id'] ?? 0, null, $filters['from_date'] ?? null, $filters['to_date'] ?? null);
        
        // Summarize by teacher for the second table
        $teacherSummaries = collect();
        if (empty($filters['teacher_id'])) {
            $teachers = Teacher::has('order_details')->with('order_details.order')->get();
            foreach ($teachers as $teacher) {
                $teacherSummary = $this->financesRepo::getEarningsSummary($teacher->id, null, $filters['from_date'] ?? null, $filters['to_date'] ?? null);
                if ($teacherSummary['gross_amount'] > 0) {
                    $teacherSummaries->push(array_merge($teacherSummary, ['teacher' => $teacher, 'orders_count' => OrderDetail::whereHas('courses', fn($q) => $q->where('teacher_id', $teacher->id))->count()]));
                }
            }
        }

        $teachers = Teacher::all();

        return view('finances::admin.earnings', compact('items', 'summary', 'teacherSummaries', 'teachers'));
    }

    public function payouts(Request $request)
    {
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
        $teachers = Teacher::whereHas('payouts')->get();
        foreach ($teachers as $teacher) {
            $teacherSummaries->push([
                'teacher' => $teacher,
                'requested' => $teacher->payouts()->where('status', 'requested')->sum('amount'),
                'processing' => $teacher->payouts()->where('status', 'processing')->sum('amount'),
                'paid' => $teacher->payouts()->where('status', 'paid')->sum('amount'),
                'rejected' => $teacher->payouts()->where('status', 'rejected')->sum('amount'),
            ]);
        }

        return view('finances::admin.payouts', compact('payouts', 'summary', 'accountChangeRequests', 'teacherSummaries'));
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
