@php
    $state = $featureState ?? null;
    $isMaintenance = $state['is_maintenance'] ?? ($isMaintenance ?? false);
    $isLocked = $state['is_locked'] ?? ($isLocked ?? false);

    $variant = $isMaintenance ? 'secondary' : 'warning';
    
    // Default translations
    $maintTitle = __('teacher::teacher/dashboard.common.maintenance_badge');
    $lockedTitle = $title ?? (__('packages::teacher.package_features.feature_locked') ?: 'Tính năng bị khóa');

    $bannerTitle = $isMaintenance ? "TÍNH NĂNG ĐANG BẢO TRÌ" : $lockedTitle;
    $bannerMessage = $isMaintenance 
        ? (__('teacher::teacher/dashboard.common.maintenance_message') ?? 'Tính năng này đang được nâng cấp, vui lòng quay lại sau.') 
        : $message;

    $showUpgrade = (! $isMaintenance && $isLocked) || ($showUpgrade ?? false);
    $upgradeUrl = $upgradeUrl ?? route('teacher.dashboard.package.upgrade');
    $icon = $isMaintenance ? 'fa-wrench' : 'fa-lock';
@endphp

<div class="alert alert-{{ $variant }} border-0 mb-4 teacher-package-notice {{ $isMaintenance ? 'is-maintenance' : 'is-locked' }}">
    <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center">
        <div class="d-flex align-items-center gap-3">
            <div class="teacher-package-notice__icon">
                <i class="fas {{ $icon }} fs-4"></i>
            </div>
            <div>
                <strong class="d-block mb-1">{{ $bannerTitle }}</strong>
                <div class="text-muted small">{{ $bannerMessage }}</div>
            </div>
        </div>
        @if ($showUpgrade)
            <a href="{{ $upgradeUrl }}" class="btn btn-sm btn-warning fw-bold">
                <i class="fas fa-arrow-up-right-from-square me-1"></i>
                {{ __('packages::teacher.upgrade.upgrade_cta_short') ?? 'Nâng cấp' }}
            </a>
        @endif
    </div>
</div>

<style>
    .teacher-package-notice {
        border-radius: 18px;
        padding: 1.25rem;
        position: relative;
        overflow: hidden;
    }
    
    .teacher-package-notice::before {
        content: "";
        position: absolute;
        inset: 0 auto 0 0;
        width: 4px;
        background: #f59e0b;
    }

    .teacher-package-notice.is-maintenance::before {
        background: #94a3b8;
    }

    .teacher-package-notice__icon {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: rgba(245, 158, 11, 0.18);
        color: #f59e0b;
    }

    .teacher-package-notice.is-maintenance .teacher-package-notice__icon {
        background: rgba(148, 163, 184, 0.18);
        color: #64748b;
    }

    html[data-theme="dark"] .teacher-package-notice.is-locked {
        background: rgba(245, 158, 11, 0.08);
        border: 1px solid rgba(245, 158, 11, 0.15) !important;
    }

    html[data-theme="dark"] .teacher-package-notice.is-maintenance {
        background: rgba(148, 163, 184, 0.08);
        border: 1px solid rgba(148, 163, 184, 0.15) !important;
    }

    html[data-theme="light"] .teacher-package-notice.is-locked {
        background: #fefce8;
        border: 1px solid #fef3c7 !important;
    }

    html[data-theme="light"] .teacher-package-notice.is-maintenance {
        background: #f8fafc;
        border: 1px solid #e2e8f0 !important;
    }
</style>
