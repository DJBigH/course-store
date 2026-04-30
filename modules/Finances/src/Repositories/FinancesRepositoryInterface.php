<?php

namespace Modules\Finances\src\Repositories;

use App\Repositories\RepositoryInterface;

interface FinancesRepositoryInterface extends RepositoryInterface
{
    public function getEarningsSummary(int $teacherId, ?string $groupBy = null, ?string $fromDate = null, ?string $toDate = null): array;
    public function getCourseEarnings(int $teacherId, ?string $fromDate = null, ?string $toDate = null): array;
    public function getRecentTransactions(int $teacherId, int $limit = 10);
    public function getPayoutSummary(int $teacherId): array;
    public function getAvailableBalance(int $teacherId): float;
    public function getPayoutAccounts(int $teacherId);
    public function getAccountUsage(int $teacherId): array;
    public function getPendingAccountChangeRequests(int $teacherId);
    public function getPayoutHistory(int $teacherId, int $perPage = 15);
    public function createPayoutRequest(int $teacherId, array $data): bool;
    public function createPayoutAccount(int $teacherId, array $data): bool;
    public function createAccountChangeRequest(int $teacherId, array $data): bool;

    // Admin methods
    public function getAdminEarnings(array $filters);
    public function getAdminPayouts(array $filters);
    public function getAdminPayoutSummary(): array;
    public function updatePayoutStatus(int $payoutId, array $data): bool;
    public function updateAccountChangeRequest(int $requestId, array $data): bool;

    public function getTeacherEarningsSummaries(?string $fromDate = null, ?string $toDate = null, ?string $currency = 'ALL'): array;

    public function getDailyEarningsSummary(string $fromDate, string $toDate, ?string $currency = 'ALL'): array;
}