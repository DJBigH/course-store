<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <div class="sb-sidenav-menu-heading">Core</div>
                <a class="nav-link" href="{{ route('admin.index') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Tổng quan
                </a>

                @include('part.backend.menu_item', [
                    'title' => 'Chuyên mục',
                    'name' => 'categories',
                ])

                @include('part.backend.menu_item', [
                    'title' => 'Khóa học',
                    'name' => 'courses',
                    'includes' => ['/admin/lessons/*'],
                ])

                @include('part.backend.menu_item', [
                    'title' => 'Giảng viên',
                    'name' => 'teacher',
                ])

                @include('part.backend.menu_item', [
                    'title' => 'Người dùng',
                    'name' => 'user',
                ])

                @include('part.backend.menu_item', [
                    'title' => 'Học viên',
                    'name' => 'students',
                ])

            </div>
        </div>
        <div class="sb-sidenav-footer">
            <div class="small">Đăng nhập:</div>
            {{ Auth::user()->name }}
        </div>
    </nav>
</div>
