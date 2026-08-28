<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Teacher\src\Models\Teacher;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $update = $request->all();
        Log::debug('Telegram Webhook Update:', $update);

        if (!isset($update['message'])) {
            return response()->json(['status' => 'ok']);
        }

        $message = $update['message'];
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';

        // Handle commands
        if (str_starts_with($text, '/')) {
            $this->handleCommand($chatId, $text);
        } else {
            // Default response: provide Chat ID
            $this->sendMessage($chatId, "Chào bạn! Chat ID của bạn là: <code>{$chatId}</code>\nHãy copy ID này và dán vào phần cấu hình Telegram trong Hồ sơ của bạn.");
        }

        return response()->json(['status' => 'ok']);
    }

    protected function handleCommand($chatId, $text)
    {
        $command = explode(' ', $text)[0];
        $teacher = Teacher::where('telegram_chat_id', $chatId)->first();

        switch ($command) {
            case '/start':
                $this->sendMessage($chatId, "Chào mừng bạn đến với Hệ thống Thông báo Giáo viên! Chat ID của bạn là: <code>{$chatId}</code>\nDùng /status để kiểm tra trạng thái.");
                break;

            case '/status':
                if (!$teacher) {
                    $this->sendMessage($chatId, "Bạn chưa đăng ký Chat ID này trong hệ thống. Chat ID của bạn: <code>{$chatId}</code>");
                } else {
                    $status = $teacher->getTelegramPackageStatus();
                    $msg = "✅ <b>Trạng thái kết nối:</b> Đã kết nối\n";
                    $msg .= "👨‍🏫 <b>Giảng viên:</b> {$teacher->name}\n";
                    $msg .= "📦 <b>Gói:</b> " . ($status['status'] === 'active' ? 'Đang hoạt động' : 'Đã hết hạn/Khóa') . "\n";
                    if ($status['expires_at']) {
                        $msg .= "🕒 <b>Hạn dùng:</b> " . $status['expires_at']->format('d/m/Y H:i');
                    }
                    $this->sendMessage($chatId, $msg);
                }
                break;

            case '/hsd':
                if (!$teacher) {
                    $this->sendMessage($chatId, "Bạn chưa đăng ký Chat ID này trong hệ thống. Chat ID của bạn: <code>{$chatId}</code>");
                } else {
                    $status = $teacher->getTelegramPackageStatus();
                    if ($status['expires_at']) {
                        $msg = "📅 <b>Hạn sử dụng gói Telegram:</b>\n";
                        $msg .= "Hết hạn lúc: <b>" . $status['expires_at']->format('H:i d/m/Y') . "</b>\n";
                        if ($status['status'] !== 'active') {
                            $msg .= "\n⚠️ <i>Gói của bạn đã hết hạn, vui lòng gia hạn để nhận thông báo.</i>";
                        }
                        $this->sendMessage($chatId, $msg);
                    } else {
                        $this->sendMessage($chatId, "Bạn chưa có gói Telegram nào hoặc gói vĩnh viễn không giới hạn.");
                    }
                }
                break;

            case '/order':
                if (!$teacher) {
                    $this->sendMessage($chatId, "Bạn chưa đăng ký Chat ID này trong hệ thống.");
                } else {
                    $status = $teacher->getTelegramPackageStatus();
                    if ($status['status'] !== 'active') {
                        $this->sendMessage($chatId, "❌ Gói của bạn đã hết hạn. Vui lòng gia hạn để xem báo cáo.");
                        break;
                    }

                    // Get orders for today
                    $today = Carbon::today();
                    $orders = \Illuminate\Support\Facades\DB::table('orders')
                        ->join('order_details', 'orders.id', '=', 'order_details.order_id')
                        ->join('courses', 'order_details.course_id', '=', 'courses.id')
                        ->where('courses.teacher_id', $teacher->id)
                        ->where('orders.status_id', 2) // Paid status
                        ->whereDate('orders.payment_date', $today)
                        ->select('orders.total', 'orders.currency')
                        ->get();

                    $count = $orders->count();
                    $totalVnd = $orders->where('currency', 'VND')->sum('total');
                    
                    $msg = "📊 <b>Báo cáo đơn hàng hôm nay (" . $today->format('d/m/Y') . "):</b>\n\n";
                    $msg .= "📦 <b>Tổng đơn:</b> {$count} đơn hàng\n";
                    $msg .= "💰 <b>Doanh thu:</b> " . number_format($totalVnd) . " VND\n";
                    if ($count > 0) {
                        $msg .= "\n💪 <i>Chúc bạn một ngày làm việc hiệu quả!</i>";
                    } else {
                        $msg .= "\n<i>Chưa có đơn hàng nào hôm nay. Cố gắng lên nhé!</i>";
                    }
                    $this->sendMessage($chatId, $msg);
                }
                break;

            default:
                $this->sendMessage($chatId, "Lệnh không hợp lệ. Các lệnh hỗ trợ: /status, /hsd, /order");
                break;
        }
    }

    protected function sendMessage($chatId, $text)
    {
        $botToken = config('services.telegram.bot_token');
        Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ]);
    }
}
