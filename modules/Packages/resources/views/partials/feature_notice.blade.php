@php
    $noticeTitle = $title ?? __('packages::teacher.package_features.upsell_title');
    $noticeMessage = $message ?? __('packages::teacher.package_features.feature_locked');
    $noticeSubmessage = $submessage ?? __('packages::teacher.package_features.upsell_submessage');
    $noticeVariant = $variant ?? 'warning';
    $noticeUpgradeUrl = $upgradeUrl ?? route('teacher.dashboard.package.upgrade');
    $noticeShowUpgrade = $showUpgrade ?? true;
@endphp

<div class="alert alert-{{ $noticeVariant }} border-0 mb-4">
    <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center">
        <div>
            <strong>{{ $noticeTitle }}</strong>
            <div class="mt-1 text-muted">{{ $noticeMessage }}</div>
            @if ($noticeSubmessage)
                <div class="mt-2 small text-muted">{{ $noticeSubmessage }}</div>
            @endif
        </div>
        @if ($noticeShowUpgrade)
            <a href="{{ $noticeUpgradeUrl }}" class="btn btn-sm btn-warning">
                {{ __('packages::teacher.package_features.upgrade_cta') }}
            </a>
        @endif
    </div>
</div>
