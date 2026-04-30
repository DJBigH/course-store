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
        if (!$chatId || (string)$chatId !== (string)$configuredChatId) {
            return response()->json(['status' => 'unauthorized_chat']);
        }

        if (strtolower($text) === '/status' || str_contains(strtolower($text), 'tình trạng')) {
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
