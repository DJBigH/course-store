<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Modules\Settings\src\Models\Setting;
use Modules\Settings\src\Support\SystemHealthService;

class CheckSystemHealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:health-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kiểm tra sức khỏe hệ thống và gửi cảnh báo qua Telegram nếu có lỗi nghiêm trọng';

    /**
     * Execute the console command.
     */
    public function handle(SystemHealthService $healthService)
    {
        $this->info('Đang kiểm tra sức khỏe hệ thống...');
        
        $snapshot = $healthService->getSnapshot();
        $criticalIssues = [];

        // Kiểm tra ổ đĩa
        if (($snapshot['disk']['status'] ?? '') === 'critical') {
            $criticalIssues[] = "💾 *Dung lượng ổ đĩa:* Rất thấp ({$snapshot['disk']['percent']}%). Còn trống {$snapshot['disk']['free']}.";
        }

        // Kiểm tra hàng đợi
        if (($snapshot['queue']['status'] ?? '') === 'critical') {
            $criticalIssues[] = "⚡ *Hàng đợi (Queue):* Ngừng hoạt động. Lần cuối thấy: " . ($snapshot['queue']['last_seen'] ?? 'Không rõ') . ".";
        }

        // Kiểm tra database
        if (($snapshot['database']['status'] ?? '') === 'critical') {
            $criticalIssues[] = "🗄️ *Cơ sở dữ liệu:* Lỗi kết nối hoặc phản hồi chậm.";
        }

        // Kiểm tra Mail (chỉ khi đã cấu hình)
        if (($snapshot['mail']['status'] ?? '') === 'critical') {
            $criticalIssues[] = "📧 *Dịch vụ Mail:* Lỗi kết nối SMTP.";
        }

        if (empty($criticalIssues)) {
            $this->info('Hệ thống ổn định. Không có lỗi nghiêm trọng.');
            return;
        }

        $this->warn('Phát hiện ' . count($criticalIssues) . ' lỗi nghiêm trọng!');
        $this->sendTelegramAlert($criticalIssues);
    }

    protected function sendTelegramAlert(array $issues)
    {
        try {
            $isEnabled = Setting::where('key', 'telegram_bot_enabled')->value('value');
            $botToken = config('services.telegram.bot_token');
            $chatId = config('services.telegram.chat_id');

            if (!$isEnabled || !$botToken || !$chatId) {
                $this->error('Telegram Bot chưa được cấu hình hoặc bị tắt.');
                return;
            }

            // Chống spam: Chỉ gửi 1 lần mỗi 4 tiếng cho cùng một nhóm lỗi (hoặc thay đổi)
            $issueHash = md5(implode('|', $issues));
            if (Cache::has('sent_health_alert_' . $issueHash)) {
                $this->info('Cảnh báo này đã được gửi gần đây. Bỏ qua để tránh spam.');
                return;
            }

            $message = "🚨 *[CẢNH BÁO SỨC KHỎE HỆ THỐNG]*\n\n";
            $message .= "Hệ thống phát hiện các vấn đề nghiêm trọng sau:\n\n";
            foreach ($issues as $issue) {
                $message .= "• {$issue}\n";
            }
            $message .= "\n⚠️ *Hành động:* Vui lòng kiểm tra trang quản trị ngay lập tức.";

            $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown'
            ]);

            if ($response->successful()) {
                $this->info('Đã gửi cảnh báo qua Telegram.');
                Cache::put('sent_health_alert_' . $issueHash, true, now()->addHours(4));
            } else {
                $this->error('Lỗi khi gửi Telegram: ' . $response->body());
            }
        } catch (\Throwable $e) {
            $this->error('Lỗi hệ thống khi gửi cảnh báo: ' . $e->getMessage());
        }
    }
}
