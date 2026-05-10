<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Modules\Orders\src\Models\Order;
use Modules\Students\src\Models\Student;
use Modules\Settings\src\Models\Setting;
use Modules\Settings\src\Support\SystemHealthService;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $input = $request->all();

        if (!isset($input['message'])) {
            return response()->json(['status' => 'no_message']);
        }

        $message = $input['message'];
        $text = trim($message['text'] ?? '');
        $chatId = $message['chat']['id'] ?? null;

        $configuredChatId = config('services.telegram.chat_id');
        
        // 1. Kiểm tra Admin Chat ID (Để xem báo cáo tổng quát hệ thống)
        if ($chatId && (string)$chatId === (string)$configuredChatId) {
            return $this->handleAdminCommands($text, $chatId);
        }

        // 2. Kiểm tra Teacher Chat ID (Để xem báo cáo riêng cho giảng viên)
        $teacher = \Modules\Teacher\src\Models\Teacher::where('telegram_chat_id', $chatId)
            ->where('is_telegram_notifications_enabled', true)
            ->first();

        if ($teacher && $teacher->hasTelegramFeature()) {
            return $this->handleTeacherCommands($teacher, $text, $chatId);
        }

        return response()->json(['status' => 'unauthorized_chat']);
    }

    protected function handleAdminCommands($text, $chatId)
    {
        if (strtolower($text) === '/status' || str_contains(strtolower($text), 'tình trạng')) {
            // ... (Giữ nguyên logic cũ cho Admin chuyển vào hàm này)
            $botToken = config('services.telegram.bot_token');

            $todayOrders = Order::whereDate('created_at', today())->count();
            $todayRevenue = Order::whereDate('created_at', today())
                ->where('status', 'completed')
                ->sum('total_amount');
            $totalStudents = Student::count();
            $maintenanceMode = Setting::where('key', 'maintenance_mode')->value('value') === '1' 
                ? '🔴 Đang bảo trì' 
                : '🟢 Đang hoạt động';

            // Lấy thông tin sức khỏe hệ thống
            $healthService = app(SystemHealthService::class);
            $health = $healthService->getSnapshot();
            
            $diskIcon = $health['disk']['status'] === 'healthy' ? '✅' : ($health['disk']['status'] === 'warning' ? '⚠️' : '🚨');
            $dbIcon = $health['database']['status'] === 'healthy' ? '✅' : '🚨';
            $queueIcon = $health['queue']['status'] === 'healthy' ? '✅' : '🚨';
            $mailIcon = $health['mail']['status'] === 'healthy' ? '✅' : '⚠️';
            $cacheIcon = $health['cache']['status'] === 'healthy' ? '✅' : '🚨';

            $reply = "📊 <b>BÁO CÁO TỔNG QUAN WEBSITE</b>\n\n";
            $reply .= "🚩 <b>Trạng thái:</b> {$maintenanceMode}\n";
            $reply .= "👥 <b>Học viên:</b> " . number_format($totalStudents) . "\n";
            $reply .= "🛒 <b>Đơn hàng (Hnay):</b> " . number_format($todayOrders) . "\n";
            $reply .= "💰 <b>Doanh thu (Hnay):</b> " . number_format($todayRevenue) . " đ\n\n";
            
            $reply .= "🛠️ <b>SỨC KHỎE HỆ THỐNG:</b>\n";
            $reply .= "{$diskIcon} <b>Ổ đĩa:</b> " . ($health['disk']['percent'] ?? 0) . "% (" . ($health['disk']['free'] ?? '0B') . " trống)\n";
            $reply .= "{$dbIcon} <b>Database:</b> " . ($health['database']['latency'] ?? 'N/A') . "\n";
            $reply .= "{$queueIcon} <b>Queue (Hàng đợi):</b> " . ($health['queue']['status'] === 'healthy' ? 'Hoạt động' : 'Lỗi/Dừng') . "\n";
            $reply .= "{$mailIcon} <b>Email:</b> " . ($health['mail']['status'] === 'healthy' ? 'Sẵn sàng' : 'Chưa cấu hình/Lỗi') . "\n";
            $reply .= "{$cacheIcon} <b>Cache:</b> " . ($health['cache']['status'] === 'healthy' ? 'Ổn định' : 'Lỗi') . "\n\n";
            
            $reply .= "⏱️ <i>Cập nhật: " . now()->format('H:i:s d/m/Y') . "</i>";

            try {
                $botToken = config('services.telegram.bot_token');
                Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $reply,
                    'parse_mode' => 'HTML'
                ]);
            } catch (\Exception $e) {
                // Ignore
            }
        }

        return response()->json(['status' => 'ok']);
    }

    protected function handleTeacherCommands($teacher, $text, $chatId)
    {
        $text = strtolower($text);
        $botToken = config('services.telegram_teacher.bot_token') ?: config('services.telegram.bot_token');
        $reply = "";

        if ($text === '/hsd' || str_contains($text, 'hạn dùng')) {
            $status = $teacher->getTelegramPackageStatus();
            $reply = "📅 <b>THÔNG TIN GÓI TELEGRAM</b>\n\n";
            $reply .= "👤 <b>Giảng viên:</b> {$teacher->name}\n";
            $reply .= "🏷️ <b>Trạng thái:</b> " . ($status['status'] === 'active' ? '🟢 Đang hoạt động' : '🔴 Đã hết hạn') . "\n";
            
            if ($status['status'] === 'active') {
                $expiry = $status['expires_at'] ? $status['expires_at']->format('d/m/Y H:i') : 'Vĩnh viễn';
                $reply .= "⏳ <b>Hết hạn:</b> {$expiry}\n";
            } else {
                $reply .= "👉 <i>Vui lòng gia hạn tại trang quản trị để tiếp tục nhận thông báo.</i>";
            }
        } 
        elseif ($text === '/order' || str_contains($text, 'đơn hàng')) {
            $todayOrders = \Modules\Orders\src\Models\OrderDetail::whereHas('courses', function($q) use ($teacher) {
                    $q->where('teacher_id', $teacher->id);
                })
                ->whereHas('order', function($q) {
                    $q->where('status_id', 2)->whereDate('created_at', today());
                })
                ->count();

            $todayRevenue = \Modules\Orders\src\Models\OrderDetail::whereHas('courses', function($q) use ($teacher) {
                    $q->where('teacher_id', $teacher->id);
                })
                ->whereHas('order', function($q) {
                    $q->where('status_id', 2)->whereDate('created_at', today());
                })
                ->sum('total_amount'); // Giả định trường này lưu doanh thu, cần check logic finance chính xác hơn nếu cần

            $reply = "💰 <b>BÁO CÁO DOANH THU HÔM NAY</b>\n\n";
            $reply .= "👤 <b>Giảng viên:</b> {$teacher->name}\n";
            $reply .= "🛒 <b>Đơn hàng mới:</b> " . number_format($todayOrders) . "\n";
            $reply .= "💵 <b>Doanh thu tạm tính:</b> " . number_format($todayRevenue) . " đ\n\n";
            $reply .= "📈 <i>Xem chi tiết tại Dashboard Giảng viên.</i>";
        }

        if ($reply) {
            try {
                Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $reply,
                    'parse_mode' => 'HTML'
                ]);
            } catch (\Exception $e) {
                // Ignore
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
