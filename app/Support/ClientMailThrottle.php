<?php

namespace App\Support;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class ClientMailThrottle
{
    public function key(string $action, array $parts = []): string
    {
        $normalized = collect($parts)
            ->filter(fn($value) => $value !== null && $value !== '')
            ->map(fn($value) => Str::lower(trim((string) $value)))
            ->values()
            ->implode('|');

        return 'client-mail:' . Str::slug($action) . ':' . sha1($normalized);
    }

    public function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        return RateLimiter::tooManyAttempts($key, $maxAttempts);
    }

    public function hit(string $key, int $decaySeconds): void
    {
        RateLimiter::hit($key, $decaySeconds);
    }

    public function availableIn(string $key): int
    {
        return RateLimiter::availableIn($key);
    }

    public function clear(string $key): void
    {
        RateLimiter::clear($key);
    }
}
