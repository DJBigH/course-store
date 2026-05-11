<?php

namespace Modules\Orders\src\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Orders\src\Models\Order;
use Modules\Orders\src\Models\OrderDetail;
use Modules\Orders\src\Models\OrderStatus;
use Modules\Orders\src\Repositories\OrdersRepositoryInterface;
use Modules\Students\src\Models\Coupons;
use Modules\Students\src\Models\CouponUsage;
use Modules\Students\src\Models\Student;
use Modules\Students\src\Models\StudentsCourses;

class OrdersRepository extends BaseRepository implements OrdersRepositoryInterface
{
    public function getModel()
    {
        return Order::class;
    }

    public function getOrdersByStudent($studentId, $filters = [], $limit)
    {
        @['status_id' => $statusId, 'start_date' => $startDate, 'end_date' => $endDate, 'total' => $total, 'code' => $code] = $filters;
        $query = $this->model->with('status')->where('student_id', $studentId)->latest();
        if ($statusId) {
            $query->where('status_id', $statusId);
        }
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        if ($total && $total >= 0) {
            $query->where('orders.total', '>=', $total);
        }

        if (!empty($code)) {
            $query->where('code', 'like', '%' . $code . '%');
        }
        return $query->paginate($limit)->withQueryString();
    }


    public function getOrder($orderId)
    {
        return $this->model->with(['detail', 'students', 'bundle'])->find($orderId);
    }

    public function updatePaymentDate($orderId, $atributes = [])
    {
        $order = $this->getOrder($orderId);
        if ($order->payment_date) {
            return;
        }
        $this->update($orderId, [
            'payment_date' => date('Y-m-d H:i:s')
        ]);
    }

    public function updateDiscount($orderId, $disscount, $coupon)
    {
        return $this->update($orderId, [
            'discount' => $disscount,
            'coupon' => $coupon,
        ]);
    }

    public function createOrder($data = [])
    {
        $data = $this->enrichCustomerSnapshot($data);
        $data = $this->enrichCurrencyInfo($data);

        return $this->model->create($data);
    }

    public function createOrderWithDetail(array $orderData, array $detailData)
    {
        return DB::transaction(function () use ($orderData, $detailData) {
            $orderData = $this->enrichCustomerSnapshot($orderData);
            $orderData = $this->enrichCurrencyInfo($orderData);
            $detailPrice = $this->normalizeMoneyAmount($detailData['price'] ?? 0);

            $orderData['total'] = 0;
            $order = Order::create($orderData);

            $detail = OrderDetail::create([
                'order_id'  => $order->id,
                'course_id' => $detailData['course_id'],
                'price'     => $detailPrice,
            ]);

            $total = $this->normalizeMoneyAmount($detail->price);

            $order->update([
                'total' => $total,
                'base_total' => $this->calculateBaseTotal($total, $orderData['currency'] ?? 'VND', $orderData['exchange_rate'] ?? 1),
            ]);

            return $order;
        });
    }

    public function createOrderWithDetails(array $orderData, array $detailRows)
    {
        return DB::transaction(function () use ($orderData, $detailRows) {
            $orderData = $this->enrichCustomerSnapshot($orderData);
            $orderData = $this->enrichCurrencyInfo($orderData);
            $orderData['total'] = 0;

            $order = Order::create($orderData);
            $total = 0;

            foreach ($detailRows as $detailRow) {
                $detailPrice = $this->normalizeMoneyAmount($detailRow['price'] ?? 0);

                $detail = OrderDetail::create([
                    'order_id' => $order->id,
                    'course_id' => $detailRow['course_id'],
                    'price' => $detailPrice,
                ]);

                $total += $this->normalizeMoneyAmount($detail->price);
            }

            $order->update([
                'total' => $this->normalizeMoneyAmount($total),
                'base_total' => $this->calculateBaseTotal($total, $orderData['currency'] ?? 'VND', $orderData['exchange_rate'] ?? 1),
            ]);

            return $order;
        });
    }

    public function completePayment(Order $order)
    {
        return DB::transaction(function () use ($order) {

            $order->update([
                'status_id'   => 2,
                'payment_date' => now(),
                'payment_complete_date' => now()
            ]);

            $coupon = Coupons::firstWhere('code', $order->coupon);

            if ($coupon) {
                $usage = CouponUsage::create([
                    'coupon_id'  => $coupon->id,
                    'order_id'   => $order->id,
                    'student_id' => $order->student_id,
                    'created_at' => now(),
                ]);

                activity_log(
                    action: 'used',
                    subject: $coupon,
                    properties: [
                        'coupon_id' => $coupon->id,
                        'coupon_code' => $coupon->code,
                        'order_id' => $order->id,
                        'order_code' => $order->code,
                        'student_id' => $order->student_id,
                        'discount' => $order->discount,
                        'usage_id' => $usage->id,
                    ],
                    logName: 'Sử dụng mã',
                    description: 'Coupon được sử dụng thành công'
                );

                activity_log(
                    action: 'apply_coupon',
                    subject: $order,
                    properties: [
                        'coupon_id' => $coupon->id,
                        'coupon_code' => $coupon->code,
                        'student_id' => $order->student_id,
                        'discount' => $order->discount,
                        'usage_id' => $usage->id,
                    ],
                    logName: 'Áp dụng mã giảm giá',
                    description: 'Đơn hàng được áp dụng coupon thành công'
                );
            }


            foreach ($order->detail as $detail) {

                StudentsCourses::create(
                    [
                        'student_id' => $order->student_id,
                        'course_id'  => $detail->course_id,
                        'status' => 1,
                        'created_at' => now(),
                    ]
                );

                if ($detail->course && $detail->course->teacher && $detail->course->teacher->student) {
                    $teacherStudent = $detail->course->teacher->student;
                    $student = $order->students;
                    
                    if ($student) {
                        $teacherStudent->notify(new \App\Notifications\TeacherCourseSaleNotification($order, $detail->course, $student));

                        // Telegram Notification for Teacher
                        $teacher = $detail->course->teacher;
                        if ($teacher && $teacher->hasTelegramFeature() && $teacher->telegram_chat_id && $teacher->is_telegram_notifications_enabled) {
                            $courseName = $detail->course->name_locale ?: $detail->course->name;
                            $studentName = $student->name ?: 'Học viên';
                            $orderCode = $order->code ?: ('#' . $order->id);
                            $totalFormatted = number_format($detail->price) . ' ' . ($order->currency ?: 'VND');

                            $text = "💰 <b>[THÔNG BÁO DOANH THU MỚI]</b>\n\n";
                            $text .= "Chúc mừng <b>{$teacher->name}</b>, bạn vừa có một đơn hàng mới cho khóa học:\n";
                            $text .= "📚 <b>{$courseName}</b>\n\n";
                            $text .= "👤 <b>Học viên:</b> {$studentName}\n";
                            $text .= "🏷️ <b>Mã đơn hàng:</b> {$orderCode}\n";
                            $text .= "💵 <b>Số tiền:</b> {$totalFormatted}\n";
                            $text .= "⏱️ <b>Thời gian:</b> " . now()->format('H:i:s d/m/Y');

                            \App\Jobs\SendTelegramTeacherNotification::dispatch($teacher, $text);
                        }

                        try {
                            $isEnabled = \Modules\Settings\src\Models\Setting::where('key', 'telegram_bot_enabled')->value('value');
                            $botToken = config('services.telegram.bot_token');
                            $chatId = config('services.telegram.chat_id');

                            if ($isEnabled === '1' && $botToken && $chatId) {
                                $courseName = $detail->course->name_locale ?: $detail->course->name;
                                $studentName = $student->name ?: 'Học viên';
                                $orderCode = $order->code ?: ('#' . $order->id);

                                $text = "🎓 <b>[KHÓA HỌC ĐÃ ĐƯỢC BÁN]</b>\n\n";
                                $text .= "👨‍🏫 <b>Giảng viên:</b> {$detail->course->teacher->name}\n";
                                $text .= "📚 <b>Khóa học:</b> {$courseName}\n";
                                $text .= "👤 <b>Học viên:</b> {$studentName}\n";
                                $text .= "🏷️ <b>Đơn hàng:</b> {$orderCode}\n";
                                $text .= "⏱️ <b>Thời gian:</b> " . now()->format('H:i:s d/m/Y');

                                \App\Jobs\SendTelegramNotification::dispatch($chatId, $text, $botToken);
                            }
                        } catch (\Exception $e) {
                            // Fail silently
                        }
                    }
                }
            }

            // Giảm số lượng combo nếu có
            if ($order->bundle_id) {
                $bundle = \Modules\Courses\src\Models\CourseBundle::find($order->bundle_id);
                if ($bundle && $bundle->quantity !== null && $bundle->quantity > 0) {
                    $bundle->decrement('quantity');
                }
            }

            return true;
        });
    }

    public function cancelOrder(Order $order)
    {
        return DB::transaction(function () use ($order) {

            $order->update([
                'status_id' => 4,
            ]);
            return true;
        });
    }

    public function getCategories()
    {
        return $this->model
            ->with(['detail', 'status', 'students', 'bundle'])
            ->select(['id', 'code', 'student_id', 'bundle_id', 'customer_name_snapshot', 'customer_email_snapshot', 'total', 'discount', 'coupon', 'status_id', 'payment_method', 'type', 'currency', 'base_total', 'created_at'])
            ->latest();
    }

    protected function enrichCustomerSnapshot(array $data): array
    {
        if (empty($data['student_id'])) {
            return $data;
        }

        $student = Student::query()->find($data['student_id']);

        if (! $student || ! $this->hasOrderSnapshotColumns()) {
            return $data;
        }

        $data['customer_name_snapshot'] = $data['customer_name_snapshot'] ?? $student->name;
        $data['customer_email_snapshot'] = $data['customer_email_snapshot'] ?? $student->email;
        $data['customer_phone_snapshot'] = $data['customer_phone_snapshot'] ?? $student->phone;
        $data['customer_address_snapshot'] = $data['customer_address_snapshot'] ?? $student->address;

        return $data;
    }

    protected function hasOrderSnapshotColumns(): bool
    {
        return Schema::hasColumn('orders', 'customer_name_snapshot')
            && Schema::hasColumn('orders', 'customer_email_snapshot')
            && Schema::hasColumn('orders', 'customer_phone_snapshot')
            && Schema::hasColumn('orders', 'customer_address_snapshot');
    }

    protected function enrichCurrencyInfo(array $data): array
    {
        $currencyService = app(\Modules\Courses\src\Support\CurrencyService::class);
        $locale = app()->getLocale();
        $currency = $currencyService->getLocaleCurrency($locale);
        
        $rates = \Illuminate\Support\Facades\Cache::get('exchange_rates', []);
        if (empty($rates)) {
            $rates = \Modules\Courses\src\Models\ExchangeRate::pluck('rate', 'code')->toArray();
        }

        $data['currency'] = $data['currency'] ?? $currency;
        $data['exchange_rate'] = $data['exchange_rate'] ?? ($rates[$data['currency']] ?? 1);
        $data['conversion_fee_pct'] = $data['conversion_fee_pct'] ?? (float) \Modules\Settings\src\Models\Setting::getValue('currency_conversion_fee', 0);

        return $data;
    }

    protected function calculateBaseTotal(float $total, string $currency, float $rate): float
    {
        if ($rate <= 0) return $total;
        // base_total is stored as USD for international audit
        return round($total / $rate, 2);
    }

    protected function normalizeMoneyAmount($amount): float
    {
        return round(max((float) $amount, 0), 2);
    }
}
