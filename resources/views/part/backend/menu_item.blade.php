<a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapse{{ $name }}"
    aria-expanded="false" aria-controls="collapseLayouts">
    <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
    {{ $title }}
    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
</a>

<div class="collapse {{ activeSidebar($name, $includes ?? []) ? 'show' : false }}" id="collapse{{ $name }}"
    aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
    <nav class="sb-sidenav-menu-nested nav">
        @php
            use Illuminate\Support\Facades\Route;
        @endphp

        @if (Route::has($name . '.index'))
            <a class="nav-link {{ activeMenu($name . '.index') ? 'active' : '' }}" href="{{ route($name . '.index') }}">
                Danh sách
            </a>
        @endif

        @if (Route::has($name . '.add'))
            <a class="nav-link {{ activeMenu($name . '.add') ? 'active' : '' }}" href="{{ route($name . '.add') }}">
                Thêm mới
            </a>
        @endif

    </nav>
</div>
