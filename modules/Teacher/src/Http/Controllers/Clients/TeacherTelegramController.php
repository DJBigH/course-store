<?php

namespace Modules\Teacher\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Teacher\src\Http\Controllers\Clients\Traits\TeacherDashboardHelpers;
use Modules\Teacher\src\Models\TelegramPackage;
use Modules\Teacher\src\Models\TeacherTelegramSubscription;
use Modules\Packages\src\Support\PackageLifecycleManager;
use Modules\Packages\src\Support\PackageUsageResolver;
use Modules\Teacher\src\Support\TeacherNotificationCenter;
use Modules\Courses\src\Repositories\CoursesRepositoryInterface;
use Modules\Lessons\src\Repositories\LessonsRepositoryInterface;
use Modules\Lessons\src\Support\LessonReleaseManager;
use Modules\Video\src\Repositories\VideoRepositoryInterface;
use Modules\Document\src\Repositories\DocumentRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TeacherTelegramController extends Controller
{
    use TeacherDashboardHelpers;

    public function __construct(
        protected CoursesRepositoryInterface $courseRepository,
        protected VideoRepositoryInterface $videoRepository,
        protected DocumentRepositoryInterface $documentRepository,
        protected LessonsRepositoryInterface $lessonRepository,
        protected LessonReleaseManager $lessonReleaseManager,
        protected PackageLifecycleManager $packageLifecycleManager,
        protected PackageUsageResolver $packageUsageResolver,
        protected TeacherNotificationCenter $notificationCenter,
    ) {}

    /**
     * Hiển thị danh sách các gói Telegram và trạng thái hiện tại
     */
    public function index()
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $pageTitle = 'Cấu hình & Mua gói Telegram';
        $pageName = 'Telegram';
        
        $packages = TelegramPackage::where('is_active', true)->orderBy('sort_order')->get();
        $subscriptionStatus = $teacher->getTelegramPackageStatus();
        
        return view('teacher::teacher.telegram.index', compact('pageTitle', 'pageName', 'teacher', 'packages', 'subscriptionStatus'));
    }

    /**
     * Xử lý mua/gia hạn gói Telegram
     */
    public function purchase(Request $request, $packageId)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $package = TelegramPackage::findOrFail($packageId);
        if (!$package->is_active) {
            return back()->with('msg_danger', 'Gói này hiện không còn khả dụng.');
        }

        DB::beginTransaction();
        try {
            $price = $package->sale_price ?? $package->price;
            
            // Sử dụng logic cộng dồn từ Model
            $newExpiry = $teacher->addTelegramDuration($package->duration_value, $package->duration_unit);

            // Tạo bản ghi lịch sử đăng ký
            TeacherTelegramSubscription::create([
                'teacher_id' => $teacher->id,
                'telegram_package_id' => $package->id,
                'amount' => $price,
                'started_at' => Carbon::now(),
                'expires_at' => $newExpiry,
                'status' => 'active'
            ]);

            // Log hoạt động
            activity_log(
                action: 'telegram_package_purchased',
                subject: $teacher,
                properties: [
                    'package_id' => $package->id,
                    'price' => $price,
                    'new_expiry' => $newExpiry->toDateTimeString()
                ],
                logName: 'teacher_telegram_management',
                description: "Đã mua gói Telegram: {$package->name}"
            );

            DB::commit();
            return redirect()->route('teacher.dashboard.telegram.index')->with('msg_success', 'Mua gói thành công! Thời hạn sử dụng đã được cập nhật.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('msg_danger', 'Có lỗi xảy ra trong quá trình thanh toán: ' . $e->getMessage());
        }
    }

    /**
     * Tính toán ngày hết hạn dựa trên đơn vị
     */
    protected function calculateExpiry(Carbon $baseDate, int $value, string $unit): Carbon
    {
        $date = $baseDate->copy();
        switch ($unit) {
            case 'minute': return $date->addMinutes($value);
            case 'hour': return $date->addHours($value);
            case 'day': return $date->addDays($value);
            case 'month': return $date->addMonths($value);
            case 'year': return $date->addYears($value);
            default: return $date;
        }
    }

    /**
     * Cập nhật cấu hình Telegram (Chat ID, Bật/Tắt)
     */
    public function updateSettings(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher) {
            return $this->redirectToStatus();
        }

        $request->validate([
            'telegram_chat_id' => 'nullable|string',
            'is_telegram_notifications_enabled' => 'boolean'
        ]);

        $teacher->update([
            'telegram_chat_id' => $request->telegram_chat_id,
            'is_telegram_notifications_enabled' => $request->has('is_telegram_notifications_enabled')
        ]);

        return back()->with('msg_success', 'Đã cập nhật cấu hình Telegram.');
    }

    /**
     * Gửi tin nhắn test Telegram
     */
    public function testConnection(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (!$teacher || !$teacher->telegram_chat_id) {
            return response()->json(['status' => 'error', 'message' => 'Vui lòng cấu hình Chat ID trước.'], 400);
        }

        $status = $teacher->getTelegramPackageStatus();
        if ($status['status'] !== 'active') {
            return response()->json(['status' => 'error', 'message' => 'Bạn cần sở hữu gói Telegram còn hạn để sử dụng tính năng này.'], 403);
        }

        $message = "🔔 <b>KẾT NỐI THÀNH CÔNG</b>\n\n";
        $message .= "Chào <b>{$teacher->name}</b>,\n";
        $message .= "Đây là tin nhắn thử nghiệm từ hệ thống. Bạn sẽ nhận được thông báo về đơn hàng và các hoạt động khác tại đây.";

        try {
            \App\Jobs\SendTelegramTeacherNotification::dispatch($teacher, $message);
            return response()->json(['status' => 'success', 'message' => 'Tin nhắn thử nghiệm đã được gửi đi!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Không thể gửi tin nhắn: ' . $e->getMessage()], 500);
        }
    }
}
