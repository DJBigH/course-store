@php
    $badgeLabel = $label ?? __('packages::teacher.package_features.upgrade_badge');
    $badgeClass = $class ?? '';
@endphp

<span class="{{ trim('teacher-upgrade-badge ' . $badgeClass) }}">{!! $badgeLabel !!}</span>
