<?php

namespace Modules\Orders\src\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Students\src\Models\Coupons;
use Modules\Students\src\Models\Student;
use Modules\Courses\src\Models\CourseBundle;
use Modules\Finances\src\Models\AffiliateLink;
use Modules\Teacher\src\Models\TeacherApplication;

class Order extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected static function booted()
    {
        static::created(function ($order) {
            try {
                $isEnabled = \Modules\Settings\src\Models\Setting::where('key', 'telegram_bot_enabled')->value('value');
                $botToken = config('services.telegram.bot_token');
                $chatId = config('services.telegram.chat_id');

                if ($isEnabled === '1' && $botToken && $chatId) {
                    $studentName = $order->students?->name ?: $order->customer_name_snapshot ?: 'Khách vãng lai';
                    $totalAmount = number_format($order->total) . ' ' . ($order->currency ?: 'VND');

                    $text = "🛒 <b>[ĐƠN HÀNG MỚI ĐƯỢC TẠO]</b>\n\n";
                    $text .= "📝 <b>Mã đơn:</b> <code>{$order->code}</code>\n";
                    $text .= "👤 <b>Khách hàng:</b> {$studentName}\n";
                    $text .= "💰 <b>Tổng tiền:</b> <b>{$totalAmount}</b>\n";
                    $text .= "⏱️ <b>Thời gian:</b> " . now()->format('H:i:s d/m/Y');

                    \Illuminate\Support\Facades\Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                        'chat_id' => $chatId,
                        'text' => $text,
                        'parse_mode' => 'HTML'
                    ]);
                }
            } catch (\Exception $e) {
                // Fail silently
            }
        });

        static::updated(function ($order) {
            try {
                if ($order->isDirty('status_id')) {
                    $oldStatus = \Modules\Orders\src\Models\OrderStatus::find($order->getOriginal('status_id'));
                    $newStatus = \Modules\Orders\src\Models\OrderStatus::find($order->status_id);

                    if ($newStatus && $newStatus->is_success && (!$oldStatus || !$oldStatus->is_success)) {
                        if (empty($order->payment_complete_date)) {
                            $order->payment_complete_date = now();
                        }
                        $isEnabled = \Modules\Settings\src\Models\Setting::where('key', 'telegram_bot_enabled')->value('value');
                        $botToken = config('services.telegram.bot_token');
                        $chatId = config('services.telegram.chat_id');

                        if ($isEnabled === '1' && $botToken && $chatId) {
                            $studentName = $order->students?->name ?: $order->customer_name_snapshot ?: 'Khách vãng lai';
                            $totalAmount = number_format($order->total) . ' ' . ($order->currency ?: 'VND');

                            $text = "🎉 <b>[ĐƠN HÀNG ĐÃ THANH TOÁN THÀNH CÔNG]</b>\n\n";
                            $text .= "📝 <b>Mã đơn:</b> <code>{$order->code}</code>\n";
                            $text .= "👤 <b>Khách hàng:</b> {$studentName}\n";
                            $text .= "💰 <b>Tổng tiền:</b> <b>{$totalAmount}</b>\n";
                            $text .= "⏱️ <b>Thời gian:</b> " . now()->format('H:i:s d/m/Y');

                            \Illuminate\Support\Facades\Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                                'chat_id' => $chatId,
                                'text' => $text,
                                'parse_mode' => 'HTML'
                            ]);
                        }

                        // Notify Teachers about new sales via Queue
                        try {
                            $orderDetails = $order->detail()->with('courses.teacher')->get();
                            $notifiedTeachers = [];

                            foreach ($orderDetails as $detail) {
                                $course = $detail->courses;
                                if ($course && $course->teacher && $course->teacher->hasTelegramFeature()) {
                                    $teacher = $course->teacher;
                                    
                                    if (in_array($teacher->id, $notifiedTeachers)) continue;

                                    $studentName = $order->students?->name ?: $order->customer_name_snapshot ?: 'Học viên';
                                    $courseName = $course->name_locale ?: $course->name;
                                    
                                    $msg = "💰 <b>BẠN CÓ ĐƠN HÀNG MỚI!</b>\n\n";
                                    $msg .= "🎓 <b>Khóa học:</b> {$courseName}\n";
                                    $msg .= "👤 <b>Học viên:</b> {$studentName}\n";
                                    $msg .= "💵 <b>Giá bán:</b> " . number_format($detail->total_amount) . " đ\n";
                                    $msg .= "⏱️ <b>Thời gian:</b> " . now()->format('H:i d/m/Y');

                                    dispatch(new \App\Jobs\SendTelegramTeacherNotification($teacher, $msg));
                                    $notifiedTeachers[] = $teacher->id;
                                }
                            }
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Telegram Teacher Sale Notification Error: ' . $e->getMessage());
                        }

                        // Unified Activation for Teacher Upgrade
                        if ($order->type === 'teacher_upgrade' && $order->orderable instanceof TeacherApplication) {
                            try {
                                $lifecycleManager = app(\Modules\Packages\src\Support\PackageLifecycleManager::class);
                                
                                // We check if it's already approved to avoid double activation, 
                                // though PackageLifecycleManager handles some of this.
                                if ($order->orderable->status !== 'approved') {
                                    $lifecycleManager->activateTeacherUpgrade($order->orderable);
                                }
                            } catch (\Exception $e) {
                                \Illuminate\Support\Facades\Log::error('Teacher Upgrade Activation Error via Order Observer', [
                                    'order_id' => $order->id,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // Fail silently
            }
        });
    }

    protected $table = 'orders';

    protected $fillable = [
        'id',
        'code',
        'student_id',
        'bundle_id',
        'affiliate_link_id',
        'customer_name_snapshot',
        'customer_email_snapshot',
        'customer_phone_snapshot',
        'customer_address_snapshot',
        'total',
        'discount',
        'coupon',
        'status_id',
        'orderable_id',
        'orderable_type',
        'type',
        'payment_date',
        'payment_complete_date',
        'payment_method',
        'currency',
        'exchange_rate',
        'conversion_fee_pct',
        'base_total',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'total' => 'float',
        'discount' => 'float',
        'exchange_rate' => 'float',
        'conversion_fee_pct' => 'float',
        'base_total' => 'float',
        'payment_date' => 'datetime',
        'payment_complete_date' => 'datetime',
    ];

    public function status(){
        return $this->belongsTo(OrderStatus::class,'status_id','id');
    }

    public function detail(){
        return $this->hasMany(OrderDetail::class, 'order_id','id');
    }
    
    public function coupon(){
        return $this->belongsTo(Coupons::class,'coupon','id');
    }

    public function students(){
        return $this->belongsTo(Student::class,'student_id','id');
    }

    public function bundle()
    {
        return $this->belongsTo(CourseBundle::class, 'bundle_id', 'id');
    }

    public function affiliateLink()
    {
        return $this->belongsTo(AffiliateLink::class, 'affiliate_link_id', 'id');
    }

    public function orderable()
    {
        return $this->morphTo();
    }

    public function getCustomerNameDisplayAttribute(): string
    {
        $name = (string) ($this->students?->name ?: $this->customer_name_snapshot ?: '-');

        if ($name === '-') {
            return $name;
        }

        return $name . $this->accountStatusSuffix();
    }

    public function getCustomerEmailDisplayAttribute(): string
    {
        $email = (string) ($this->students?->email ?: $this->customer_email_snapshot ?: '-');

        if ($email === '-') {
            return $email;
        }

        return $email . $this->accountStatusSuffix();
    }

    public function getCustomerPhoneDisplayAttribute(): string
    {
        return (string) ($this->students?->phone ?: $this->customer_phone_snapshot ?: '-');
    }

    public function getCustomerAddressDisplayAttribute(): string
    {
        return (string) ($this->students?->address ?: $this->customer_address_snapshot ?: '-');
    }

    protected function accountStatusSuffix(): string
    {
        if (!$this->students && ($this->customer_name_snapshot || $this->customer_email_snapshot)) {
            return ' (' . __('students::clients/account.order_detail.account_deleted') . ')';
        }

        if ($this->students && !$this->students->email_verified_at) {
            return ' (' . __('students::clients/account.order_detail.account_deactivated') . ')';
        }

        return '';
    }

    public function getPaymentMethodCodeAttribute(): string
    {
        if (!empty($this->payment_method)) {
            return (string) $this->payment_method;
        }

        if ((float) max(($this->total ?? 0) - ($this->discount ?? 0), 0) <= 0) {
            return 'free';
        }

        return 'unknown';
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        $key = 'orders::teacher/orders.payment.' . $this->payment_method_code;
        $label = __($key);

        return $label !== $key ? $label : $this->payment_method_code;
    }
    public function getPaymentMethodBadgeStyleAttribute(): string
    {
        return match ($this->payment_method_code) {
            'bank', 'bank_transfer' => 'background:#16a34a;color:#ffffff;',
            'vnpay' => 'background:#0f6cbd;color:#ffffff;',
            'momo' => 'background:#a21caf;color:#ffffff;',
            'free' => 'background:#0ea5e9;color:#ffffff;',
            default => 'background:#64748b;color:#ffffff;',
        };
    }
}
