<?php

namespace Modules\Finances\src\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Modules\Finances\src\Models\PayoutAccount;
use Modules\Finances\src\Models\PayoutAccountChangeRequest;
use Modules\Finances\src\Models\PayoutRequest;
use Modules\Finances\src\Support\FinanceCalculator;
use Modules\Orders\src\Models\Order;
use Modules\Orders\src\Models\OrderDetail;
use Modules\Teacher\src\Models\TeacherApplication;
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

        if ($fromDate) {
            $query->whereDate('od.created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('od.created_at', '<=', $toDate);
        }

        // 1. Get Target Currency Rate
        $targetRate = 1.0;
        if ($currency && $currency !== 'ALL') {
            $rateKey = 'currency_rate_' . strtolower($currency);
            $targetRate = (float) (DB::table('settings')->where('key', $rateKey)->value('value') ?: 1.0);
        }

        $exRate = 'IFNULL(o.exchange_rate, 1)';
        
        $summary = $query->selectRaw("
            SUM(od.price * {$exRate}) as course_gross,
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

        // 1b. Calculate Upgrade Teacher Revenue
        $upgradeTeacherRevenue = 0.0;
        $upgradeQuery = DB::table('orders as o')
            ->join('teacher_applications as ta', 'ta.id', '=', 'o.orderable_id')
            ->leftJoin('teacher as t', 't.id', '=', 'ta.teacher_id')
            ->where('o.status_id', $paidStatusId)
            ->where('o.orderable_type', 'Modules\Teacher\src\Models\TeacherApplication');

        if ($teacherId) {
            $upgradeQuery->where('ta.teacher_id', $teacherId);
        }
        if ($fromDate) {
            $upgradeQuery->whereDate('o.payment_complete_date', '>=', $fromDate);
        }
        if ($toDate) {
            $upgradeQuery->whereDate('o.payment_complete_date', '<=', $toDate);
        }

        $upgradeTeacherRevenue = 0.0; // Teacher gets 0% from system upgrades/registrations

        // 2. Total Gross from ALL successful orders (including non-course ones)
        $grossQuery = DB::table('orders as o')
            ->where('o.status_id', $paidStatusId);

        // Filter by date range
        if ($fromDate) {
            $grossQuery->whereDate('o.payment_complete_date', '>=', $fromDate);
        }
        if ($toDate) {
            $grossQuery->whereDate('o.payment_complete_date', '<=', $toDate);
        }

        // If filtering by teacher, we only include their course sales and their own upgrades
        if ($teacherId) {
            $grossQuery->where(function ($q) use ($teacherId) {
                $q->whereExists(function ($sub) use ($teacherId) {
                    $sub->select(DB::raw(1))
                        ->from('orders_detail as sod')
                        ->join('courses as sc', 'sc.id', '=', 'sod.course_id')
                        ->whereRaw('sod.order_id = o.id')
                        ->where('sc.teacher_id', $teacherId);
                })->orWhere(function ($sub) use ($teacherId) {
                    $sub->where('o.orderable_type', 'Modules\Teacher\src\Models\TeacherApplication')
                        ->whereExists(function ($ta) use ($teacherId) {
                            $ta->select(DB::raw(1))
                                ->from('teacher_applications as sta')
                                ->whereRaw('sta.id = o.orderable_id')
                                ->where('sta.teacher_id', $teacherId);
                        });
                });
            });
        }

        $totalGross = $grossQuery->sum(DB::raw("o.total * {$exRate}"));

        // Convert to target currency if not ALL
        $finalGross = (float) $totalGross / $targetRate;
        $totalTeacherRevenueRaw = (float) ($summary->teacher_revenue ?? 0) + (float) $upgradeTeacherRevenue;
        $finalTeacherRevenue = $totalTeacherRevenueRaw / $targetRate;
        $finalDiscount = (float) ($summary->allocated_discount ?? 0) / $targetRate;

        $platformRevenue = max(0, $finalGross - $finalTeacherRevenue - $finalDiscount);

        return [
            'gross_amount' => $finalGross,
            'allocated_discount' => $finalDiscount,
            'net_revenue' => $finalGross - $finalDiscount,
            'teacher_revenue' => $finalTeacherRevenue,
            'platform_revenue' => $platformRevenue,
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

    public function createAccountChangeRequest(int $teacherId, array $data): PayoutAccountChangeRequest
    {
        return PayoutAccountChangeRequest::create([
            'teacher_id' => $teacherId,
            'replace_payout_account_id' => $data['replace_payout_account_id'],
            'bank_name' => $data['bank_name'],
            'bank_account_name' => $data['bank_account_name'],
            'bank_account_number' => $data['bank_account_number'],
            'status' => 'pending',
        ]);
    }

    public function getAdminEarnings(array $filters)
    {
        $paidStatusId = (int) (DB::table('orders_status')->where('is_success', true)->value('id') ?? 2);

        // We query Orders directly to include both Course orders (via details) and Upgrade orders
        $query = Order::query()
            ->where('status_id', $paidStatusId)
            ->with(['detail.courses.teacher', 'orderable.teacher', 'orderable.package', 'bundle']);

        if (!empty($filters['currency']) && $filters['currency'] !== 'ALL') {
            $query->where('currency', $filters['currency']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('payment_complete_date', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('payment_complete_date', '<=', $filters['to_date']);
        }

        if (!empty($filters['teacher_id'])) {
            $teacherId = $filters['teacher_id'];
            $query->where(function ($q) use ($teacherId) {
                $q->whereHas('detail.courses', function ($sub) use ($teacherId) {
                    $sub->withTrashed()->where('teacher_id', $teacherId);
                })->orWhere(function ($sub) use ($teacherId) {
                    $sub->where('orderable_type', 'Modules\Teacher\src\Models\TeacherApplication')
                        ->whereHasMorph('orderable', [TeacherApplication::class], function ($ta) use ($teacherId) {
                            $ta->where('teacher_id', $teacherId);
                        });
                });
            });
        }

        $orders = $query->latest('payment_complete_date')->paginate(20);

        // Flatten orders into items for the view
        $items = collect();
        foreach ($orders as $order) {
            if ($order->detail->isNotEmpty()) {
                foreach ($order->detail as $detail) {
                    // Check teacher filter for each detail if active
                    if (!empty($filters['teacher_id']) && ($detail->courses?->teacher_id != $filters['teacher_id'])) {
                        continue;
                    }
                    
                    $detail->finance_breakdown = FinanceCalculator::breakdown($detail, $detail->courses?->teacher?->commission_rate ?? 0);
                    if ($order->bundle_id && $order->bundle) {
                        $detail->bundle_name = $order->bundle->name;
                    }
                    $items->push($detail);
                }
            } elseif ($order->orderable_type === 'Modules\Teacher\src\Models\TeacherApplication') {
                $app = $order->orderable;
                if ($app) {
                    // Check teacher filter
                    if (!empty($filters['teacher_id']) && ($app->teacher_id != $filters['teacher_id'])) {
                        continue;
                    }

                    // Create a virtual item for the upgrade/new registration
                    $isUpgrade = $app->type === 'upgrade';
                    $typeLabel = $isUpgrade ? ' (Nâng cấp)' : ' (Đăng ký mới)';
                    
                    $virtualItem = (object) [
                        'id' => 'UPGRADE_' . $order->id,
                        'order' => $order,
                        'courses' => (object) [
                            'name_locale' => ($app->package?->name ?? 'Gói giảng viên') . $typeLabel,
                            'teacher' => $app->teacher,
                        ],
                        'finance_breakdown' => FinanceCalculator::breakdownForUpgrade($order, 0), // Teacher gets 0%
                    ];
                    $items->push($virtualItem);
                }
            }
        }

        // We wrap it back into a LengthAwarePaginator to keep the view happy, 
        // but note that the number of items might differ slightly from the order count.
        // However, usually it's 1 item per order.
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $orders->total(),
            $orders->perPage(),
            $orders->currentPage(),
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );
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

        if ($fromDate) {
            $query->whereDate('od.created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('od.created_at', '<=', $toDate);
        }

        // Get Target Currency Rate
        $targetRate = 1.0;
        if ($currency && $currency !== 'ALL') {
            $rateKey = 'currency_rate_' . strtolower($currency);
            $targetRate = (float) (DB::table('settings')->where('key', $rateKey)->value('value') ?: 1.0);
        }

        $exRate = 'IFNULL(o.exchange_rate, 1)';

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
        ->get();

        // 1b. Get Upgrade Earnings per teacher
        $upgradeQuery = DB::table('orders as o')
            ->join('teacher_applications as ta', 'ta.id', '=', 'o.orderable_id')
            ->leftJoin('teacher as t', 't.id', '=', 'ta.teacher_id')
            ->where('o.status_id', $paidStatusId)
            ->where('o.orderable_type', 'Modules\Teacher\src\Models\TeacherApplication');

        if ($fromDate) {
            $upgradeQuery->whereDate('o.payment_complete_date', '>=', $fromDate);
        }
        if ($toDate) {
            $upgradeQuery->whereDate('o.payment_complete_date', '<=', $toDate);
        }

        $upgradeResults = $upgradeQuery->selectRaw("
            ta.teacher_id,
            t.name as teacher_name,
            t.slug as teacher_slug,
            COUNT(o.id) as orders_count,
            SUM(o.total * {$exRate}) as gross_amount,
            SUM(o.discount * {$exRate}) as allocated_discount,
            SUM(GREATEST(0, (o.total * {$exRate}) - (o.discount * {$exRate}))) as net_revenue,
            0 as teacher_revenue
        ")
        ->groupBy('ta.teacher_id', 't.name', 't.slug')
        ->get();

        // Merge results
        $summaries = collect();
        
        // Add course results
        foreach ($results as $row) {
            $summaries->put($row->teacher_id, [
                'teacher_id' => $row->teacher_id,
                'teacher_name' => $row->teacher_name,
                'teacher_slug' => $row->teacher_slug,
                'orders_count' => (int) $row->orders_count,
                'gross_amount' => (float) $row->gross_amount,
                'allocated_discount' => (float) $row->allocated_discount,
                'net_revenue' => (float) $row->net_revenue,
                'teacher_revenue' => (float) $row->teacher_revenue,
            ]);
        }

        // Merge upgrade results
        foreach ($upgradeResults as $row) {
            if (!$row->teacher_id) continue;
            
            if ($summaries->has($row->teacher_id)) {
                $existing = $summaries->get($row->teacher_id);
                $existing['orders_count'] += (int) $row->orders_count;
                $existing['gross_amount'] += (float) $row->gross_amount;
                $existing['allocated_discount'] += (float) $row->allocated_discount;
                $existing['net_revenue'] += (float) $row->net_revenue;
                $existing['teacher_revenue'] += (float) $row->teacher_revenue;
                $summaries->put($row->teacher_id, $existing);
            } else {
                $summaries->put($row->teacher_id, [
                    'teacher_id' => $row->teacher_id,
                    'teacher_name' => $row->teacher_name,
                    'teacher_slug' => $row->teacher_slug,
                    'orders_count' => (int) $row->orders_count,
                    'gross_amount' => (float) $row->gross_amount,
                    'allocated_discount' => (float) $row->allocated_discount,
                    'net_revenue' => (float) $row->net_revenue,
                    'teacher_revenue' => (float) $row->teacher_revenue,
                ]);
            }
        }

        return $summaries->map(function ($item) use ($targetRate) {
            $item['gross_amount'] /= $targetRate;
            $item['allocated_discount'] /= $targetRate;
            $item['net_revenue'] /= $targetRate;
            $item['teacher_revenue'] /= $targetRate;
            $item['platform_revenue'] = max(0, $item['net_revenue'] - $item['teacher_revenue']);
            return $item;
        })->values()->toArray();
    }

    public function getDailyEarningsSummary(string $fromDate, string $toDate, ?string $currency = 'ALL'): array
    {
        $paidStatusId = (int) (DB::table('orders_status')->where('is_success', true)->value('id') ?? 2);

        $query = DB::table('orders_detail as od')
            ->join('orders as o', 'o.id', '=', 'od.order_id')
            ->join('courses as c', 'c.id', '=', 'od.course_id')
            ->join('teacher as t', 't.id', '=', 'c.teacher_id')
            ->where('o.status_id', $paidStatusId);
        if ($fromDate) {
            $query->whereDate('od.created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('od.created_at', '<=', $toDate);
        }

        // Get Target Currency Rate
        $targetRate = 1.0;
        if ($currency && $currency !== 'ALL') {
            $rateKey = 'currency_rate_' . strtolower($currency);
            $targetRate = (float) (DB::table('settings')->where('key', $rateKey)->value('value') ?: 1.0);
        }

        $exRate = 'IFNULL(o.exchange_rate, 1)';

        $results = $query->selectRaw("
            DATE(od.created_at) as date,
            SUM(od.price * {$exRate}) as course_gross,
            SUM(
                GREATEST(0, (od.price * {$exRate}) - LEAST(od.price * {$exRate}, IF(o.total > 0, (o.discount * {$exRate}) * (od.price / o.total), 0))) 
                * (IFNULL(t.commission_rate, 0) / 100)
            ) as teacher_revenue
        ")
        ->groupBy('date')
        ->get();

        // Also get total daily gross from all orders
        $dailyGross = DB::table('orders as o')
            ->where('o.status_id', $paidStatusId)
            ->whereBetween('o.payment_complete_date', [$fromDate, $toDate])
            ->selectRaw("DATE(o.payment_complete_date) as date, SUM(o.total * {$exRate}) as total_gross")
            ->groupBy('date')
            ->pluck('total_gross', 'date');

        $allDates = collect($results->pluck('date'))
            ->merge($dailyGross->keys())
            ->unique();

        return $allDates->mapWithKeys(function ($date) use ($results, $dailyGross, $targetRate) {
            $courseRow = $results->firstWhere('date', $date);
            $totalGrossBase = (float) ($dailyGross[$date] ?? ($courseRow->course_gross ?? 0));
            $teacherRevenueBase = (float) ($courseRow->teacher_revenue ?? 0);
            
            $totalGross = $totalGrossBase / $targetRate;
            $teacherRevenue = $teacherRevenueBase / $targetRate;

            return [$date => [
                'gross_amount' => $totalGross,
                'teacher_revenue' => $teacherRevenue,
                'platform_revenue' => (float) max(0, $totalGross - $teacherRevenue),
            ]];
        })->toArray();
    }
}