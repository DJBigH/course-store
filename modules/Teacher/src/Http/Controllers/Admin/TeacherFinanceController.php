<?php

namespace Modules\Teacher\src\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Notifications\TeacherPayoutStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\Orders\src\Models\OrderDetail;
use Modules\Teacher\src\Models\Teacher;
use Modules\Teacher\src\Models\TeacherPayoutRequest;
use Modules\Teacher\src\Support\TeacherFinanceCalculator;

class TeacherFinanceController extends Controller
{
    public function earnings(Request $request)
    {
        $pageTitle = 'Doi soat doanh thu giang vien';
        $detailsQuery = $this->paidTeacherOrderDetailsQuery($request);
        $summary = TeacherFinanceCalculator::summarize(
            $detailsQuery->get(),
            fn ($detail) => (float) ($detail->courses?->teacher?->commission_rate ?? 0)
        );
        $allItems = TeacherFinanceCalculator::decorate(
            $detailsQuery->get(),
            fn ($detail) => (float) ($detail->courses?->teacher?->commission_rate ?? 0)
        );
        $teacherSummaries = $this->buildTeacherSummaries($allItems);

        $items = $detailsQuery->paginate(15)->withQueryString();
        $items->setCollection(TeacherFinanceCalculator::decorate(
            $items->getCollection(),
            fn ($detail) => (float) ($detail->courses?->teacher?->commission_rate ?? 0)
        ));

        $teachers = Teacher::query()->where('status', 'active')->orderBy('name')->get();

        return view('teacher::finance.earnings', compact('pageTitle', 'items', 'summary', 'teachers', 'teacherSummaries'));
    }

    public function payouts(Request $request)
    {
        $pageTitle = 'Xu ly rut tien giang vien';
        $payouts = TeacherPayoutRequest::query()
            ->with(['teacher.student'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'requested' => (float) TeacherPayoutRequest::query()->where('status', 'requested')->sum('amount'),
            'processing' => (float) TeacherPayoutRequest::query()->where('status', 'processing')->sum('amount'),
            'paid' => (float) TeacherPayoutRequest::query()->where('status', 'paid')->sum('amount'),
        ];
        $teacherSummaries = TeacherPayoutRequest::query()
            ->with('teacher')
            ->get()
            ->groupBy('teacher_id')
            ->map(function (Collection $items) {
                $teacher = $items->first()?->teacher;

                return [
                    'teacher' => $teacher,
                    'requested' => (float) $items->where('status', 'requested')->sum('amount'),
                    'processing' => (float) $items->where('status', 'processing')->sum('amount'),
                    'paid' => (float) $items->where('status', 'paid')->sum('amount'),
                    'rejected' => (float) $items->where('status', 'rejected')->sum('amount'),
                ];
            })
            ->sortByDesc('requested');

        return view('teacher::finance.payouts', compact('pageTitle', 'payouts', 'summary', 'teacherSummaries'));
    }

    public function updatePayout(Request $request, $id)
    {
        $payload = $request->validate([
            'status' => ['required', 'in:processing,paid,rejected'],
            'admin_note' => ['nullable', 'string'],
        ]);

        $payout = TeacherPayoutRequest::query()->findOrFail($id);
        $payout->update([
            'status' => $payload['status'],
            'admin_note' => trim((string) ($payload['admin_note'] ?? '')) ?: null,
            'processed_at' => now(),
            'processed_by' => auth()->id(),
        ]);

        $student = $payout->teacher?->student;
        if ($student) {
            $student->notify(new TeacherPayoutStatusNotification($payout->fresh()));
        }

        return back()->with('msg', 'Da cap nhat trang thai yeu cau rut tien.');
    }

    public function exportEarnings(Request $request, string $format)
    {
        $details = TeacherFinanceCalculator::decorate(
            $this->paidTeacherOrderDetailsQuery($request)->get(),
            fn ($detail) => (float) ($detail->courses?->teacher?->commission_rate ?? 0)
        );

        return $format === 'excel'
            ? $this->downloadEarningsExcel($details)
            : $this->downloadEarningsCsv($details);
    }

    public function exportPayouts(Request $request, string $format)
    {
        $payouts = TeacherPayoutRequest::query()
            ->with(['teacher.student'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->latest('id')
            ->get();

        return $format === 'excel'
            ? $this->downloadPayoutsExcel($payouts)
            : $this->downloadPayoutsCsv($payouts);
    }

    private function paidTeacherOrderDetailsQuery(Request $request)
    {
        return OrderDetail::query()
            ->with(['courses.teacher', 'order.status', 'order.students'])
            ->whereHas('order', function ($query) {
                $query->where('status_id', 2);
            })
            ->whereHas('courses.teacher')
            ->when($request->filled('teacher_id'), function ($query) use ($request) {
                $teacherId = (int) $request->input('teacher_id');
                $query->whereHas('courses', function ($courseQuery) use ($teacherId) {
                    $courseQuery->withoutGlobalScopes()->withTrashed()->where('teacher_id', $teacherId);
                });
            })
            ->when($request->filled('from_date'), function ($query) use ($request) {
                $query->whereHas('order', function ($orderQuery) use ($request) {
                    $orderQuery->whereDate('payment_complete_date', '>=', $request->input('from_date'));
                });
            })
            ->when($request->filled('to_date'), function ($query) use ($request) {
                $query->whereHas('order', function ($orderQuery) use ($request) {
                    $orderQuery->whereDate('payment_complete_date', '<=', $request->input('to_date'));
                });
            })
            ->latest('id');
    }

    private function buildTeacherSummaries(Collection $items): Collection
    {
        return $items
            ->groupBy(fn ($item) => $item->courses?->teacher?->id ?: 0)
            ->map(function (Collection $group) {
                $teacher = $group->first()?->courses?->teacher;

                return [
                    'teacher' => $teacher,
                    'gross_amount' => (float) $group->sum(fn ($item) => $item->finance_breakdown['gross_amount'] ?? 0),
                    'allocated_discount' => (float) $group->sum(fn ($item) => $item->finance_breakdown['allocated_discount'] ?? 0),
                    'net_revenue' => (float) $group->sum(fn ($item) => $item->finance_breakdown['net_revenue'] ?? 0),
                    'teacher_revenue' => (float) $group->sum(fn ($item) => $item->finance_breakdown['teacher_revenue'] ?? 0),
                    'platform_revenue' => (float) $group->sum(fn ($item) => $item->finance_breakdown['platform_revenue'] ?? 0),
                    'orders_count' => $group->pluck('order_id')->unique()->count(),
                ];
            })
            ->sortByDesc('teacher_revenue')
            ->values();
    }

    private function downloadEarningsCsv(Collection $items)
    {
        $filename = 'teacher-earnings-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($items) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Order Code', 'Teacher', 'Course', 'Student', 'Gross', 'Allocated Discount', 'Net Revenue', 'Commission Rate', 'Teacher Revenue', 'Platform Revenue', 'Paid At']);

            foreach ($items as $item) {
                fputcsv($handle, [
                    $item->order?->code,
                    $item->courses?->teacher?->name_locale ?: '',
                    $item->courses?->name_locale ?: '',
                    $item->order?->students?->name ?: '',
                    $item->finance_breakdown['gross_amount'] ?? 0,
                    $item->finance_breakdown['allocated_discount'] ?? 0,
                    $item->finance_breakdown['net_revenue'] ?? 0,
                    $item->finance_breakdown['commission_rate'] ?? 0,
                    $item->finance_breakdown['teacher_revenue'] ?? 0,
                    $item->finance_breakdown['platform_revenue'] ?? 0,
                    optional($item->order?->payment_complete_date ?: $item->order?->created_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function downloadPayoutsCsv(Collection $payouts)
    {
        $filename = 'teacher-payouts-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($payouts) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Payout ID', 'Teacher', 'Email', 'Amount', 'Bank Name', 'Account Name', 'Account Number', 'Status', 'Admin Note', 'Requested At', 'Processed At']);

            foreach ($payouts as $payout) {
                fputcsv($handle, [
                    $payout->id,
                    $payout->teacher?->name_locale ?: '',
                    $payout->teacher?->student?->email ?: '',
                    $payout->amount,
                    $payout->bank_name,
                    $payout->bank_account_name,
                    $payout->bank_account_number,
                    $payout->status,
                    $payout->admin_note,
                    optional($payout->created_at)->format('Y-m-d H:i:s'),
                    optional($payout->processed_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function downloadEarningsExcel(Collection $items)
    {
        $filename = 'teacher-earnings-' . now()->format('Ymd-His') . '.xls';
        $html = view('teacher::finance.exports.earnings_excel', compact('items'))->render();

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function downloadPayoutsExcel(Collection $payouts)
    {
        $filename = 'teacher-payouts-' . now()->format('Ymd-His') . '.xls';
        $html = view('teacher::finance.exports.payouts_excel', compact('payouts'))->render();

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
