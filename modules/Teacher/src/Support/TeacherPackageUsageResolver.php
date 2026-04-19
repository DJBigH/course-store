<?php

namespace Modules\Teacher\src\Support;

class TeacherPackageUsageResolver
{
    public function build(int $used, ?int $limit, bool $featureEnabled = true): array
    {
        $hasLimit = $featureEnabled && $limit !== null;
        $isUnlimited = $featureEnabled && $limit === null;
        $remaining = $hasLimit ? max($limit - $used, 0) : null;

        return [
            'used' => $used,
            'limit' => $featureEnabled ? $limit : 0,
            'feature_enabled' => $featureEnabled,
            'is_feature_locked' => !$featureEnabled,
            'has_limit' => $hasLimit,
            'is_unlimited' => $isUnlimited,
            'remaining' => $remaining,
            'can_create_draft' => $featureEnabled,
            'can_create' => $featureEnabled && ($limit === null || $used < $limit),
            'can_publish_more' => $featureEnabled && ($limit === null || $used < $limit),
            'is_over_limit' => $hasLimit && $used > $limit,
            'over_limit_by' => $hasLimit ? max($used - $limit, 0) : 0,
        ];
    }
}
