<?php

namespace Modules\Teacher\src\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Students\src\Models\Student;
use Modules\User\src\Models\User;

class TeacherApplication extends Model
{
    protected $table = 'teacher_applications';

    protected static function booted()
    {
        static::created(function ($app) {
            try {
                $isEnabled = \Modules\Settings\src\Models\Setting::where('key', 'telegram_bot_enabled')->value('value');
                $botToken = config('services.telegram.bot_token');
                $chatId = config('services.telegram.chat_id');

                if ($isEnabled === '1' && $botToken && $chatId) {
                    $isUpgrade = $app->type === 'upgrade';
                    $title = $isUpgrade ? "🚀 <b>[YÊU CẦU NÂNG CẤP GÓI]</b>" : "👨‍🏫 <b>[YÊU CẦU ĐĂNG KÝ LÀM GIÁO VIÊN]</b>";
                    
                    $text = "{$title}\n\n";
                    $text .= "👤 <b>Họ tên:</b> {$app->full_name}\n";
                    $text .= "✉️ <b>Email:</b> <code>{$app->email}</code>\n";
                    $text .= "📞 <b>Số điện thoại:</b> <code>{$app->phone}</code>\n";
                    
                    if ($isUpgrade) {
                        $text .= "📦 <b>Gói yêu cầu:</b> " . ($app->package?->name ?? 'N/A') . "\n";
                        if ($app->note) {
                            $text .= "📝 <b>Ghi chú:</b> {$app->note}\n";
                        }
                    }

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

        static::updated(function ($app) {
            try {
                if ($app->isDirty('status') && $app->status === 'cancelled') {
                    $isEnabled = \Modules\Settings\src\Models\Setting::where('key', 'telegram_bot_enabled')->value('value');
                    $botToken = config('services.telegram.bot_token');
                    $chatId = config('services.telegram.chat_id');

                    if ($isEnabled === '1' && $botToken && $chatId) {
                        $isUpgrade = $app->type === 'upgrade';
                        $title = $isUpgrade ? "❌ <b>[THÔNG BÁO HỦY NÂNG CẤP GÓI]</b>" : "❌ <b>[THÔNG BÁO HỦY HỢP TÁC GIẢNG VIÊN]</b>";
                        
                        $text = "{$title}\n\n";
                        $text .= "👤 <b>Giảng viên:</b> {$app->full_name}\n";
                        $text .= "✉️ <b>Email:</b> <code>{$app->email}</code>\n";
                        
                        if ($isUpgrade) {
                            $text .= "📦 <b>Gói đã hủy:</b> " . ($app->package?->name ?? 'N/A') . "\n";
                        }

                        $text .= "⏱️ <b>Thời gian:</b> " . now()->format('H:i:s d/m/Y');

                        \Illuminate\Support\Facades\Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                            'chat_id' => $chatId,
                            'text' => $text,
                            'parse_mode' => 'HTML'
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // Fail silently
            }
        });
    }

    protected $fillable = [
        'student_id',
        'applicant_type',
        'teacher_id',
        'package_id',
        'payment_method',
        'coupon_code',
        'discount_amount',
        'status',
        'full_name',
        'display_name',
        'headline',
        'bio',
        'experience_years',
        'specialties',
        'phone',
        'email',
        'locale',
        'portfolio_url',
        'facebook_url',
        'youtube_url',
        'linkedin_url',
        'custom_links',
        'intro_video_url',
        'cv_file',
        'identity_file',
        'submitted_at',
        'reviewed_at',
        'activates_at',
        'package_started_at',
        'package_expires_at',
        'activated_at',
        'account_created_at',
        'account_credentials_sent_at',
        'reviewed_by',
        'granted_by',
        'claim_token',
        'claim_expires_at',
        'claimed_at',
        'type',
        'note',
        'admin_note',
    ];

    protected $casts = [
        'specialties' => 'array',
        'custom_links' => 'array',
        'discount_amount' => 'float',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'activates_at' => 'datetime',
        'package_started_at' => 'datetime',
        'package_expires_at' => 'datetime',
        'activated_at' => 'datetime',
        'account_created_at' => 'datetime',
        'account_credentials_sent_at' => 'datetime',
        'claim_expires_at' => 'datetime',
        'claimed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }

    public function package()
    {
        return $this->belongsTo(\Modules\Packages\src\Models\Package::class, 'package_id', 'id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'id');
    }

    public function grantedByAdmin()
    {
        return $this->belongsTo(User::class, 'granted_by', 'id');
    }

    public function getDisplayStatusAttribute(): string
    {
        if ($this->status === 'approved' && !$this->activated_at && $this->activates_at) {
            $queuedLabel = __('teacher::portal.status_labels.queued_activation');

            return $queuedLabel !== 'teacher::portal.status_labels.queued_activation'
                ? $queuedLabel
                : 'Approved, waiting for activation';
        }

        return match ($this->status) {
            'draft' => __('teacher::portal.status_labels.draft'),
            'pending_payment' => __('teacher::portal.status_labels.pending_payment'),
            'pending_review' => __('teacher::portal.status_labels.pending_review'),
            'approved' => __('teacher::portal.status_labels.approved'),
            'rejected' => __('teacher::portal.status_labels.rejected'),
            'cancelled' => __('teacher::portal.status_labels.cancelled'),
            default => ucfirst((string) $this->status),
        };
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'vnpay' => __('teacher::portal.payment_methods.vnpay'),
            'momo' => __('teacher::portal.payment_methods.momo'),
            default => __('teacher::portal.payment_methods.bank_transfer'),
        };
    }

    public function getBasePriceAttribute(): float
    {
        return (float) ($this->package?->price ?? 0);
    }

    public function getPayableAmountAttribute(): float
    {
        return max($this->base_price - (float) ($this->discount_amount ?? 0), 0);
    }
}
