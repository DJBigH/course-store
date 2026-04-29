<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Modules\Orders\src\Models\Order;
use Modules\Students\src\Models\Student;
use Modules\Settings\src\Models\Setting;

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

            $reply = "📊 <b>BÁO CÁO TÌNH TRẠNG WEBSITE</b>\n\n";
            $reply .= "🖥️ <b>Trạng thái:</b> {$maintenanceMode}\n";
            $reply .= "👥 <b>Tổng học viên:</b> " . number_format($totalStudents) . "\n";
            $reply .= "🛒 <b>Đơn hàng hôm nay:</b> " . number_format($todayOrders) . "\n";
            $reply .= "💰 <b>Doanh thu hôm nay:</b> " . number_format($todayRevenue) . " VND\n\n";
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
