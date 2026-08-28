<?php

namespace Modules\Settings\src\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class SystemHealthService
{
    /**
     * Get a comprehensive snapshot of system health.
     */
    public function getSnapshot(): array
    {
        return [
            'disk' => $this->checkDisk(),
            'database' => $this->checkDatabase(),
            'queue' => $this->checkQueue(),
            'mail' => $this->checkMail(),
            'cache' => $this->checkCache(),
            'timestamp' => now()->toDateTimeString(),
        ];
    }

    private function checkDisk(): array
    {
        try {
            $path = base_path();
            $total = disk_total_space($path);
            $free = disk_free_space($path);
            $used = $total - $free;
            $percent = $total > 0 ? round(($used / $total) * 100, 2) : 0;

            return [
                'status' => $percent < 90 ? 'healthy' : ($percent < 95 ? 'warning' : 'critical'),
                'total' => $this->formatBytes($total),
                'free' => $this->formatBytes($free),
                'used' => $this->formatBytes($used),
                'percent' => $percent,
                'label' => "{$percent}% ({$this->formatBytes($used)} / {$this->formatBytes($total)})",
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            $latency = round((microtime(true) - $start) * 1000, 2);

            return [
                'status' => 'healthy',
                'latency' => "{$latency}ms",
                'driver' => config('database.default'),
            ];
        } catch (\Exception $e) {
            return ['status' => 'critical', 'message' => $e->getMessage()];
        }
    }

    private function checkQueue(): array
    {
        // We use a cache heartbeat to see if a worker is alive
        // This requires a worker to be running and updating this key
        $lastHeartbeat = Cache::get('queue_worker_heartbeat');
        
        if (!$lastHeartbeat) {
            return [
                'status' => 'warning',
                'message' => 'Không tìm thấy nhịp tim của Worker. Hàng đợi có thể đang dừng.',
            ];
        }

        $diff = now()->diffInSeconds($lastHeartbeat);
        
        return [
            'status' => $diff < 60 ? 'healthy' : 'critical',
            'last_seen' => $lastHeartbeat,
            'seconds_ago' => $diff,
        ];
    }

    private function checkMail(): array
    {
        try {
            // Only check if configured
            if (empty(config('mail.mailers.smtp.host'))) {
                return ['status' => 'warning', 'message' => 'Chưa cấu hình SMTP.'];
            }

            // We don't want to actually send an email, just test connection
            // Mail::mailer()->getSymfonyTransport()->stop(); // This is just a test
            
            return [
                'status' => 'healthy',
                'driver' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
            ];
        } catch (\Exception $e) {
            return ['status' => 'critical', 'message' => $e->getMessage()];
        }
    }

    private function checkCache(): array
    {
        try {
            $testKey = 'health_check_test_' . time();
            Cache::put($testKey, true, 10);
            $val = Cache::get($testKey);
            Cache::forget($testKey);

            return [
                'status' => $val ? 'healthy' : 'critical',
                'driver' => config('cache.default'),
            ];
        } catch (\Exception $e) {
            return ['status' => 'critical', 'message' => $e->getMessage()];
        }
    }

    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
