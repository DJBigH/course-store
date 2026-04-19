@php
    $bannerVariant = $variant ?? 'info';
    $bannerTitle = $title ?? '';
    $bannerLines = collect($lines ?? [])->filter(fn ($line) => filled($line))->values();
    $bannerUpgradeUrl = $upgradeUrl ?? null;
    $bannerShowUpgrade = $showUpgrade ?? false;
@endphp

<div class="alert alert-{{ $bannerVariant }} border-0 mb-4">
    <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center">
        <div>
            @if ($bannerTitle !== '')
                <strong>{{ $bannerTitle }}</strong>
            @endif
            @foreach ($bannerLines as $line)
                <div class="{{ $loop->first && $bannerTitle !== '' ? 'mt-1' : '' }} text-muted">{{ $line }}</div>
            @endforeach
        </div>
        @if ($bannerShowUpgrade && $bannerUpgradeUrl)
            <a href="{{ $bannerUpgradeUrl }}" class="btn btn-sm btn-warning">
                {{ __('courses::teacher/messages.package_features.upgrade_cta') }}
            </a>
        @endif
    </div>
</div>
