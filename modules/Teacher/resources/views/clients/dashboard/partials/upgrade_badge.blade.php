@php
    $badgeLabel = $label ?? __('teacher::dashboard.package_features.upgrade_badge');
    $badgeClass = $class ?? '';
@endphp

<span class="{{ trim('teacher-upgrade-badge ' . $badgeClass) }}">{{ $badgeLabel }}</span>
