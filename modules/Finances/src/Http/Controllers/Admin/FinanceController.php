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
            $summaries = $this->financesRepo->getTeacherEarningsSummaries($filters['from_date'] ?? null, $filters['to_date'] ?? null, $currency);
            foreach ($summaries as $summaryItem) {
                // We still need the teacher model object for the view, but we can fetch them all at once if needed
                // or just pass the array. The view expects 'teacher' object.
                $teacherSummaries->push(array_merge($summaryItem, [
                    'teacher' => (object) [
                        'id' => $summaryItem['teacher_id'],
                        'name' => $summaryItem['teacher_name'],
                        'slug' => $summaryItem['teacher_slug'],
                    ]
                ]));
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

        // Teacher payout summaries - Optimized with one query
        $teacherSummaries = \Modules\Finances\src\Models\PayoutRequest::query()
            ->join('teacher as t', 't.id', '=', 'payout_requests.teacher_id')
            ->selectRaw('
                t.id as teacher_id, 
                t.name as teacher_name, 
                SUM(CASE WHEN status = "requested" THEN amount ELSE 0 END) as requested,
                SUM(CASE WHEN status = "processing" THEN amount ELSE 0 END) as processing,
                SUM(CASE WHEN status = "paid" THEN amount ELSE 0 END) as paid,
                SUM(CASE WHEN status = "rejected" THEN amount ELSE 0 END) as rejected
            ')
            ->groupBy('t.id', 't.name')
            ->get()
            ->map(function($row) {
                return [
                    'teacher' => (object) ['id' => $row->teacher_id, 'name' => $row->teacher_name],
                    'requested' => $row->requested,
                    'processing' => $row->processing,
                    'paid' => $row->paid,
                    'rejected' => $row->rejected,
                ];
            });

        $teachers = Teacher::all();

        return view('finances::admin.payouts', compact('payouts', 'summary', 'accountChangeRequests', 'teacherSummaries', 'teachers', 'pageTitle'));
    }


    public function updatePayout(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:processing,paid,rejected',
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $payout = \Modules\Finances\src\Models\PayoutRequest::findOrFail($id);
        $oldStatus = $payout->status;
        
        $this->financesRepo->updatePayoutStatus($id, $request->all());
        $payout->refresh();

        activity_log(
            action: 'update_payout_status',
            subject: $payout,
            properties: [
                'old_status' => $oldStatus,
                'new_status' => $payout->status,
                'admin_note' => $request->admin_note,
                'amount' => $payout->amount,
                'teacher' => $payout->teacher?->name_locale,
            ],
            logName: 'admin_finance_management',
            description: "Cập nhật trạng thái rút tiền #PAY{$payout->id}: {$oldStatus} -> {$payout->status}"
        );

        return back()->with('msg', __('finances::admin.messages.payout_update_success'));
    }

    public function updatePayoutAccountChangeRequest(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $changeRequest = \Modules\Finances\src\Models\PayoutAccountChangeRequest::findOrFail($id);
        $oldStatus = $changeRequest->status;

        $success = $this->financesRepo->updateAccountChangeRequest($id, $request->all());

        if (!$success) {
            return back()->with('msg', __('finances::admin.messages.payout_change_processed'));
        }

        $changeRequest->refresh();

        activity_log(
            action: 'update_payout_account_change_status',
            subject: $changeRequest,
            properties: [
                'old_status' => $oldStatus,
                'new_status' => $changeRequest->status,
                'admin_note' => $request->admin_note,
                'teacher' => $changeRequest->teacher?->name_locale,
            ],
            logName: 'admin_finance_management',
            description: "Xử lý yêu cầu thay đổi tài khoản rút tiền #ACC{$changeRequest->id}: {$changeRequest->status}"
        );

        return back()->with('msg', __('finances::admin.messages.payout_account_change_success'));
    }

    public function exportEarnings(Request $request, $format = 'csv')
    {
        $filters = $request->only(['teacher_id', 'from_date', 'to_date', 'currency']);
        $currency = $filters['currency'] ?? 'ALL';
        
        $items = $this->financesRepo->getAdminEarnings(array_merge($filters, ['per_page' => 50000]));
        if (method_exists($items, 'getCollection')) {
            $items = $items->getCollection();
        }

        $fileName = 'doanh_thu_doi_soat_' . date('Y-m-d') . '.csv';
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Mã Đơn Hàng', 'Ngày Giao Dịch', 'Giảng Viên', 'Khóa Học', 'Tiền Tệ', 'Gross', 'Discount', 'Net', 'Commission %', 'Teacher Share', 'Platform Net'];

        $callback = function() use($items, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns);

            foreach ($items as $item) {
                $itemCurrency = $item->order?->currency ?: 'VND';
                fputcsv($file, [
                    $item->order?->code,
                    optional($item->order?->payment_complete_date ?: $item->order?->created_at)->format('Y-m-d H:i'),
                    $item->courses?->teacher?->name_locale ?: '-',
                    $item->courses?->name_locale ?: '-',
                    $itemCurrency,
                    $item->finance_breakdown['gross_amount'],
                    $item->finance_breakdown['allocated_discount'],
                    $item->finance_breakdown['net_revenue'],
                    $item->finance_breakdown['commission_rate'] . '%',
                    $item->finance_breakdown['teacher_revenue'],
                    $item->finance_breakdown['platform_revenue']
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPayouts(Request $request, $format = 'csv')
    {
        $filters = $request->only(['status']);
        $payouts = $this->financesRepo->getAdminPayouts(array_merge($filters, ['per_page' => 50000]));
        if (method_exists($payouts, 'getCollection')) {
            $payouts = $payouts->getCollection();
        }

        $fileName = 'yeu_cau_rut_tien_' . date('Y-m-d') . '.csv';
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Mã Rút Tiền', 'Giảng Viên', 'Số Tiền', 'Trạng Thái', 'Ngân Hàng', 'Số Tài Khoản', 'Tên Tài Khoản', 'Ngày Tạo', 'Ghi chú Admin'];

        $callback = function() use($payouts, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns);

            foreach ($payouts as $p) {
                fputcsv($file, [
                    '#PAY' . $p->id,
                    $p->teacher?->name_locale ?: '-',
                    $p->amount,
                    $p->status,
                    $p->bank_name,
                    $p->account_number,
                    $p->account_name,
                    $p->created_at->format('Y-m-d H:i'),
                    $p->admin_note
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
