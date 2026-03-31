@php
    $isExpanded = activeSidebar($name, $includes ?? []);
@endphp

<a class="nav-link collapsed {{ $isExpanded ? 'is-active' : '' }}" href="#" data-bs-toggle="collapse"
    data-bs-target="#collapse{{ $name }}" aria-expanded="{{ $isExpanded ? 'true' : 'false' }}"
    aria-controls="collapse{{ $name }}">
    <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
    <span class="sidebar-link-label">{{ $title }}</span>
    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
</a>

<div class="collapse {{ $isExpanded ? 'show' : '' }}" id="collapse{{ $name }}" data-bs-parent="#sidenavAccordion">
    <nav class="sb-sidenav-menu-nested nav">
        @php
            use Illuminate\Support\Facades\Route;
        @endphp

        @if (Route::has($name . '.index'))
            <a class="nav-link {{ activeMenu($name . '.index') ? 'active' : '' }}" href="{{ route($name . '.index') }}">
                <span class="sidebar-link-label">Danh sách</span>
            </a>
        @endif

        @if (Route::has($name . '.add'))
            <a class="nav-link {{ activeMenu($name . '.add') ? 'active' : '' }}" href="{{ route($name . '.add') }}">
                <span class="sidebar-link-label">Thêm mới</span>
            </a>
        @endif
    </nav>
</div>

