<?php

namespace Modules\Finances\src\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Modules\Finances\src\Models\PayoutAccount;
use Modules\Finances\src\Models\PayoutAccountChangeRequest;
use Modules\Finances\src\Models\PayoutRequest;
use Modules\Finances\src\Support\FinanceCalculator;
use Modules\Orders\src\Models\OrderDetail;
use Modules\Teacher\src\Models\Teacher;

class FinancesRepository extends BaseRepository implements FinancesRepositoryInterface
{
    public function getModel()
    {
        return PayoutRequest::class;
    }

    public function getEarningsSummary($teacherId, ?string $groupBy = null, ?string $fromDate = null, ?string $toDate = null, ?string $currency = 'ALL'): array
    {
        $paidStatusId = (int) (DB::table('orders_status')->where('is_success', true)->value('id') ?? 2);

        $query = DB::table('orders_detail as od')
            ->join('orders as o', 'o.id', '=', 'od.order_id')
            ->join('courses as c', 'c.id', '=', 'od.course_id')
            ->join('teacher as t', 't.id', '=', 'c.teacher_id')
            ->where('o.status_id', $paidStatusId);

        if ($teacherId) {
            $query->where('c.teacher_id', $teacherId);
        }

        if ($currency && $currency !== 'ALL') {
            $query->where('o.currency', $currency);
        }

        if ($fromDate) {
            $query->whereDate('od.created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('od.created_at', '<=', $toDate);
        }

        // Logic for calculated fields:
        // gross = od.price * exRate
        // discount = min(gross, (o.discount * exRate) * (od.price / o.total))
        // net = gross - discount
        // teacher = net * (t.commission_rate / 100)
        
        $exRate = $currency === 'ALL' ? 'IFNULL(o.exchange_rate, 1)' : '1';
        
        $summary = $query->selectRaw("
            SUM(od.price * {$exRate}) as gross_amount,
            SUM(
                LEAST(
                    od.price * {$exRate}, 
                    IF(o.total > 0, (o.discount * {$exRate}) * (od.price / o.total), 0)
                )
            ) as allocated_discount,
            SUM(
                GREATEST(0, (od.price * {$exRate}) - LEAST(od.price * {$exRate}, IF(o.total > 0, (o.discount * {$exRate}) * (od.price / o.total), 0)))
            ) as net_revenue,
            SUM(
                GREATEST(0, (od.price * {$exRate}) - LEAST(od.price * {$exRate}, IF(o.total > 0, (o.discount * {$exRate}) * (od.price / o.total), 0))) 
                * (IFNULL(t.commission_rate, 0) / 100)
            ) as teacher_revenue
        ")->first();

        $platformRevenue = max(0, ($summary->net_revenue ?? 0) - ($summary->teacher_revenue ?? 0));

        return [
            'gross_amount' => (float) ($summary->gross_amount ?? 0),
            'allocated_discount' => (float) ($summary->allocated_discount ?? 0),
            'net_revenue' => (float) ($summary->net_revenue ?? 0),
            'teacher_revenue' => (float) ($summary->teacher_revenue ?? 0),
            'platform_revenue' => (float) $platformRevenue,
        ];
    }

    public function getCourseEarnings(int $teacherId, ?string $fromDate = null, ?string $toDate = null): array
    {
        $query = OrderDetail::query()
            ->whereHas('order', function ($q) {
                $q->whereHas('status', fn($sq) => $sq->where('is_success', true));
            })
            ->whereHas('courses', function ($q) use ($teacherId) {
                $q->withTrashed()->where('teacher_id', $teacherId);
            });

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $details = $query->with(['order', 'courses.teacher'])->get();
        $details = FinanceCalculator::decorate($details, function ($detail) {
            return $detail->courses?->teacher?->commission_rate ?? 0;
        });

        $courseGroups = $details->groupBy('courses_id');
        $results = [];

        foreach ($courseGroups as $courseId => $items) {
            $course = $items->first()->courses;
            $results[] = [
                'course' => $course,
                'orders_count' => $items->count(),
                'revenue' => $items->sum(fn($i) => $i->finance_breakdown['teacher_revenue']),
            ];
        }

        usort($results, fn($a, $b) => $b['revenue'] <=> $a['revenue']);

        return $results;
    }

    public function getRecentTransactions(int $teacherId, int $limit = 10)
    {
        $details = OrderDetail::query()
            ->whereHas('order', function ($q) {
                $q->whereHas('status', fn($sq) => $sq->where('is_success', true));
            })
            ->whereHas('courses', function ($q) use ($teacherId) {
                $q->withTrashed()->where('teacher_id', $teacherId);
            })
            ->with(['order', 'courses.teacher', 'order.students'])
            ->latest()
            ->limit($limit)
            ->get();

        return FinanceCalculator::decorate($details, function ($detail) {
            return $detail->courses?->teacher?->commission_rate ?? 0;
        });
    }

    public function getPayoutSummary(int $teacherId): array
    {
        $teacher = Teacher::find($teacherId);
        $totalRevenue = $this->getEarningsSummary($teacherId)['teacher_revenue'];

        $requested = PayoutRequest::where('teacher_id', $teacherId)
            ->whereIn('status', ['requested', 'processing', 'paid'])
            ->sum('amount');

        return [
            'teacher_revenue' => $totalRevenue,
            'requested' => $requested,
            'available' => max(0, $totalRevenue - $requested),
        ];
    }

    public function getAvailableBalance(int $teacherId): float
    {
        return $this->getPayoutSummary($teacherId)['available'];
    }

    public function getPayoutAccounts(int $teacherId)
    {
        return PayoutAccount::where('teacher_id', $teacherId)->get();
    }

    public function getAccountUsage(int $teacherId): array
    {
        $usedCount = PayoutAccount::where('teacher_id', $teacherId)->count();
        $limit = 3; // Hardcoded limit for now, could be dynamic

        return [
            'used' => $usedCount,
            'limit' => $limit,
            'limit_label' => (string) $limit,
            'can_create' => $usedCount < $limit,
        ];
    }

    public function getPendingAccountChangeRequests(int $teacherId)
    {
        return PayoutAccountChangeRequest::where('teacher_id', $teacherId)
            ->with('replaceAccount')
            ->latest()
            ->get();
    }

    public function getPayoutHistory(int $teacherId, int $perPage = 15)
    {
        return PayoutRequest::where('teacher_id', $teacherId)
            ->latest()
            ->paginate($perPage);
    }

    public function createPayoutRequest(int $teacherId, array $data): bool
    {
        $balance = $this->getAvailableBalance($teacherId);
        if ($data['amount'] > $balance) {
            return false;
        }

        $created = null;
        DB::transaction(function () use ($teacherId, $data, &$created) {
            if (($data['account_mode'] ?? '') === 'new') {
                PayoutAccount::create([
                    'teacher_id'          => $teacherId,
                    'bank_name'           => $data['bank_name'],
                    'bank_account_name'   => $data['bank_account_name'],
                    'bank_account_number' => $data['bank_account_number'],
                ]);
            }

            $created = PayoutRequest::create([
                'teacher_id'          => $teacherId,
                'amount'              => $data['amount'],
                'bank_name'           => $data['bank_name'],
                'bank_account_name'   => $data['bank_account_name'],
                'bank_account_number' => $data['bank_account_number'],
                'note'                => $data['note'] ?? null,
                'status'              => 'requested',
                'currency_code'       => $data['currency_code'] ?? 'VND',
                'exchange_rate'       => $data['exchange_rate'] ?? 1.0,
                'original_amount'     => $data['original_amount'] ?? $data['amount'],
                'converted_amount_vnd'=> $data['converted_amount_vnd'] ?? $data['amount'],
                'fee_percentage'      => $data['fee_percentage'] ?? 0.0,
                'fee_amount_vnd'      => $data['fee_amount_vnd'] ?? 0.0,
            ]);
        });

        if ($created) {
            activity_log(
                action: 'create_payout',
                subject: $created,
                properties: [
                    'data' => [
                        'amount'              => $created->amount,
                        'bank_name'           => $created->bank_name,
                        'bank_account_name'   => $created->bank_account_name,
                        'bank_account_number' => $created->bank_account_number,
                        'status'              => 'requested',
                    ],
                ],
                logName: 'teacher_payout_management',
            );
        }

        return true;
    }

    public function createPayoutAccount(int $teacherId, array $data): bool
    {
        $usage = $this->getAccountUsage($teacherId);
        if (!$usage['can_create']) {
            return false;
        }

        PayoutAccount::create([
            'teacher_id' => $teacherId,
            'bank_name' => $data['bank_name'],
            'bank_account_name' => $data['bank_account_name'],
            'bank_account_number' => $data['bank_account_number'],
        ]);

        return true;
    }

    public function createAccountChangeRequest(int $teacherId, array $data): bool
    {
        PayoutAccountChangeRequest::create([
            'teacher_id' => $teacherId,
            'replace_payout_account_id' => $data['replace_payout_account_id'],
            'bank_name' => $data['bank_name'],
            'bank_account_name' => $data['bank_account_name'],
            'bank_account_number' => $data['bank_account_number'],
            'status' => 'pending',
        ]);

        return true;
    }

    public function getAdminEarnings(array $filters)
    {
        $query = OrderDetail::query()
            ->whereHas('order', function ($q) use ($filters) {
                $q->whereHas('status', fn($sq) => $sq->where('is_success', true));
                if (!empty($filters['currency']) && $filters['currency'] !== 'ALL') {
                    $q->where('currency', $filters['currency']);
                }
            });

        if (!empty($filters['teacher_id'])) {
            $query->whereHas('courses', function ($q) use ($filters) {
                $q->withTrashed()->where('teacher_id', $filters['teacher_id']);
            });
        }
        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        $items = $query->with(['order', 'courses.teacher'])->latest()->paginate(20);

        // Decorate paginated items
        $items->getCollection()->transform(function ($item) {
            $item->finance_breakdown = FinanceCalculator::breakdown($item, $item->courses?->teacher?->commission_rate ?? 0);
            return $item;
        });

        return $items;
    }

    public function getAdminPayouts(array $filters)
    {
        $query = PayoutRequest::query()->with(['teacher.student']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate(20);
    }

    public function getAdminPayoutSummary(): array
    {
        return [
            'requested' => PayoutRequest::where('status', 'requested')->sum('amount'),
            'processing' => PayoutRequest::where('status', 'processing')->sum('amount'),
            'paid' => PayoutRequest::where('status', 'paid')->sum('amount'),
            'account_change_pending' => PayoutAccountChangeRequest::where('status', 'pending')->count(),
        ];
    }

    public function updatePayoutStatus(int $payoutId, array $data): bool
    {
        $payout = PayoutRequest::findOrFail($payoutId);
        $oldStatus = $payout->status;
        $updateData = [
            'status'     => $data['status'],
            'admin_note' => $data['admin_note'] ?? null,
        ];

        if ($data['status'] === 'paid' && !$payout->processed_at) {
            $updateData['processed_at'] = now();
            $updateData['processed_by'] = auth()->id();
        }

        $result = $payout->update($updateData);

        if ($result) {
            activity_log(
                action: 'payout_status_update',
                subject: $payout,
                properties: [
                    'old' => ['status' => $oldStatus],
                    'new' => ['status' => $data['status'], 'admin_note' => $data['admin_note'] ?? null],
                ],
                logName: 'admin_payout_management',
            );
        }

        return $result;
    }

    public function updateAccountChangeRequest(int $requestId, array $data): bool
    {
        $request = PayoutAccountChangeRequest::findOrFail($requestId);
        if ($request->status !== 'pending') {
            return false;
        }

        DB::transaction(function () use ($request, $data) {
            $request->update([
                'status'       => $data['status'],
                'admin_note'   => $data['admin_note'] ?? null,
                'processed_at' => now(),
            ]);

            if ($data['status'] === 'approved') {
                $savedAccount = PayoutAccount::find($request->replace_payout_account_id);
                if ($savedAccount) {
                    $savedAccount->update([
                        'bank_name'           => $request->bank_name,
                        'bank_account_name'   => $request->bank_account_name,
                        'bank_account_number' => $request->bank_account_number,
                    ]);
                }
            }
        });

        activity_log(
            action: 'account_change_update',
            subject: $request,
            properties: [
                'old' => ['status' => 'pending'],
                'new' => ['status' => $data['status'], 'admin_note' => $data['admin_note'] ?? null],
            ],
            logName: 'admin_payout_management',
        );

        return true;
    }

    public function getTeacherEarningsSummaries(?string $fromDate = null, ?string $toDate = null, ?string $currency = 'ALL'): array
    {
        $paidStatusId = (int) (DB::table('orders_status')->where('is_success', true)->value('id') ?? 2);

        $query = DB::table('orders_detail as od')
            ->join('orders as o', 'o.id', '=', 'od.order_id')
            ->join('courses as c', 'c.id', '=', 'od.course_id')
            ->join('teacher as t', 't.id', '=', 'c.teacher_id')
            ->where('o.status_id', $paidStatusId);

        if ($currency && $currency !== 'ALL') {
            $query->where('o.currency', $currency);
        }

        if ($fromDate) {
            $query->whereDate('od.created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('od.created_at', '<=', $toDate);
        }

        $exRate = $currency === 'ALL' ? 'IFNULL(o.exchange_rate, 1)' : '1';

        $results = $query->selectRaw("
            c.teacher_id,
            t.name as teacher_name,
            t.slug as teacher_slug,
            COUNT(DISTINCT o.id) as orders_count,
            SUM(od.price * {$exRate}) as gross_amount,
            SUM(
                LEAST(
                    od.price * {$exRate}, 
                    IF(o.total > 0, (o.discount * {$exRate}) * (od.price / o.total), 0)
                )
            ) as allocated_discount,
            SUM(
                GREATEST(0, (od.price * {$exRate}) - LEAST(od.price * {$exRate}, IF(o.total > 0, (o.discount * {$exRate}) * (od.price / o.total), 0)))
            ) as net_revenue,
            SUM(
                GREATEST(0, (od.price * {$exRate}) - LEAST(od.price * {$exRate}, IF(o.total > 0, (o.discount * {$exRate}) * (od.price / o.total), 0))) 
                * (IFNULL(t.commission_rate, 0) / 100)
            ) as teacher_revenue
        ")
        ->groupBy('c.teacher_id', 't.name', 't.slug')
        ->having('gross_amount', '>', 0)
        ->get();

        return $results->map(function ($row) {
            return [
                'teacher_id' => $row->teacher_id,
                'teacher_name' => $row->teacher_name,
                'teacher_slug' => $row->teacher_slug,
                'orders_count' => $row->orders_count,
                'gross_amount' => (float) $row->gross_amount,
                'allocated_discount' => (float) $row->allocated_discount,
                'net_revenue' => (float) $row->net_revenue,
                'teacher_revenue' => (float) $row->teacher_revenue,
                'platform_revenue' => (float) max(0, $row->net_revenue - $row->teacher_revenue),
            ];
        })->toArray();
    }

    public function getDailyEarningsSummary(string $fromDate, string $toDate, ?string $currency = 'ALL'): array
    {
        $paidStatusId = (int) (DB::table('orders_status')->where('is_success', true)->value('id') ?? 2);

        $query = DB::table('orders_detail as od')
            ->join('orders as o', 'o.id', '=', 'od.order_id')
            ->join('courses as c', 'c.id', '=', 'od.course_id')
            ->join('teacher as t', 't.id', '=', 'c.teacher_id')
            ->where('o.status_id', $paidStatusId)
            ->whereBetween('od.created_at', [$fromDate, $toDate]);

        if ($currency && $currency !== 'ALL') {
            $query->where('o.currency', $currency);
        }

        $exRate = $currency === 'ALL' ? 'IFNULL(o.exchange_rate, 1)' : '1';

        $results = $query->selectRaw("
            DATE(od.created_at) as date,
            SUM(od.price * {$exRate}) as gross_amount,
            SUM(
                LEAST(
                    od.price * {$exRate}, 
                    IF(o.total > 0, (o.discount * {$exRate}) * (od.price / o.total), 0)
                )
            ) as allocated_discount,
            SUM(
                GREATEST(0, (od.price * {$exRate}) - LEAST(od.price * {$exRate}, IF(o.total > 0, (o.discount * {$exRate}) * (od.price / o.total), 0)))
            ) as net_revenue,
            SUM(
                GREATEST(0, (od.price * {$exRate}) - LEAST(od.price * {$exRate}, IF(o.total > 0, (o.discount * {$exRate}) * (od.price / o.total), 0))) 
                * (IFNULL(t.commission_rate, 0) / 100)
            ) as teacher_revenue
        ")
        ->groupBy('date')
        ->get();

        return $results->mapWithKeys(function ($row) {
            return [$row->date => [
                'gross_amount' => (float) $row->gross_amount,
                'net_revenue' => (float) $row->net_revenue,
                'teacher_revenue' => (float) $row->teacher_revenue,
                'platform_revenue' => (float) max(0, $row->net_revenue - $row->teacher_revenue),
            ]];
        })->toArray();
    }
}