<?php

namespace App\Services;

use App\Models\AiUsageLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AiUsageService
{
    /**
     * Get the configured monthly limit from config (which reads .env AI_MONTHLY_REQUEST_LIMIT).
     * 0 or negative numbers indicate unlimited.
     */
    public function getMonthlyLimit(): int
    {
        return (int) config('services.openai.monthly_limit', 250);
    }

    /**
     * Check if monthly usage is unlimited.
     */
    public function isUnlimited(): bool
    {
        return $this->getMonthlyLimit() <= 0;
    }

    /**
     * Get the total AI requests used in a given month (default current month 'YYYY-MM').
     */
    public function getMonthlyUsage(?string $yearMonth = null): int
    {
        $yearMonth = $yearMonth ?: Carbon::now()->format('Y-m');

        return AiUsageLog::where('year_month', $yearMonth)->count();
    }

    /**
     * Calculate remaining requests in the current month.
     */
    public function getRemainingRequests(?string $yearMonth = null): int
    {
        if ($this->isUnlimited()) {
            return -1; // -1 represents unlimited
        }

        $limit = $this->getMonthlyLimit();
        $used = $this->getMonthlyUsage($yearMonth);

        return max(0, $limit - $used);
    }

    /**
     * Check whether the monthly request limit has been reached.
     */
    public function hasReachedLimit(?string $yearMonth = null): bool
    {
        if ($this->isUnlimited()) {
            return false;
        }

        return $this->getMonthlyUsage($yearMonth) >= $this->getMonthlyLimit();
    }

    /**
     * Record an AI request execution in the database.
     */
    public function recordUsage(string $action, ?int $userId = null, int $tokens = 0, ?string $ip = null, ?array $metadata = null): AiUsageLog
    {
        $now = Carbon::now();

        return AiUsageLog::create([
            'user_id' => $userId ?: auth()->id(),
            'action' => $action,
            'year_month' => $now->format('Y-m'),
            'tokens' => $tokens,
            'ip_address' => $ip ?: request()->ip(),
            'metadata' => $metadata,
        ]);
    }

    /**
     * Get full usage statistics for API responses and UI indicators.
     */
    public function getUsageStats(): array
    {
        $limit = $this->getMonthlyLimit();
        $used = $this->getMonthlyUsage();
        $remaining = $this->isUnlimited() ? null : max(0, $limit - $used);
        $percentage = ($limit > 0) ? round(min(100, ($used / $limit) * 100), 1) : 0;

        return [
            'limit' => $limit,
            'used' => $used,
            'remaining' => $remaining,
            'is_unlimited' => $this->isUnlimited(),
            'has_reached_limit' => $this->hasReachedLimit(),
            'percentage' => $percentage,
            'month' => Carbon::now()->format('F Y'),
            'year_month' => Carbon::now()->format('Y-m'),
        ];
    }
}
