<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <div class="sb-sidenav-menu-heading mt-2">Quản lý</div>

                @if (auth()->user()?->hasPermission('dashboard.view'))
                    <a class="nav-link" href="{{ route('admin.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                        Tổng quan
                    </a>
                @endif

                @if (auth()->user()?->canAnyPermission(['courses.view', 'courses.create', 'courses.edit', 'courses.publish', 'comments.moderate']))
                    @include('part.backend.menu_item', [
                        'title' => 'Khóa học',
                        'name' => 'courses',
                        'includes' => ['/admin/lessons/*'],
                    ])
                @endif

                @if (auth()->user()?->canAnyPermission(['categories.view', 'categories.create', 'categories.edit', 'categories.delete', 'categories.logs']))
                    @include('part.backend.menu_item', [
                        'title' => 'Chuyên mục',
                        'name' => 'categories',
                    ])
                @endif

                @if (auth()->user()?->canAnyPermission(['teachers.view', 'teachers.create', 'teachers.edit', 'teachers.delete', 'teachers.logs']))
                    @include('part.backend.menu_item', [
                        'title' => 'Giảng viên',
                        'name' => 'teacher',
                    ])
                @endif

                @if (auth()->user()?->canAnyPermission(['students.view', 'students.create', 'students.edit', 'students.delete', 'students.logs']))
                    @include('part.backend.menu_item', [
                        'title' => 'Học viên',
                        'name' => 'students',
                    ])
                @endif

                @if (auth()->user()?->canAnyPermission(['users.view', 'users.create', 'users.edit', 'users.delete', 'users.logs']))
                    @include('part.backend.menu_item', [
                        'title' => 'Người dùng',
                        'name' => 'user',
                    ])
                @endif

                @if (auth()->user()?->hasPermission('groups.manage'))
                    <a class="nav-link {{ request()->is('admin/groups*') ? 'active' : '' }}" href="{{ route('groups.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-user-shield"></i></div>
                        Nhóm quyền
                    </a>
                @endif

                @if (auth()->user()?->hasPermission('permissions.manage'))
                    <a class="nav-link {{ request()->is('admin/permissions*') ? 'active' : '' }}" href="{{ route('permissions.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-key"></i></div>
                        Quyền chi tiết
                    </a>
                @endif

                @if (auth()->user()?->canAnyPermission(['orders.view', 'orders.update', 'orders.delete']))
                    @include('part.backend.menu_item', [
                        'title' => 'Đơn hàng',
                        'name' => 'orders',
                    ])
                @endif

                @if (auth()->user()?->canAnyPermission(['coupons.view', 'coupons.create', 'coupons.edit', 'coupons.delete', 'coupons.assign', 'coupons.logs']))
                    @include('part.backend.menu_item', [
                        'title' => 'Mã giảm giá',
                        'name' => 'coupons',
                    ])
                @endif

                @if (auth()->user()?->canAnyPermission(['contacts.view', 'contacts.update', 'contacts.delete', 'contacts.logs']))
                    @include('part.backend.menu_item', [
                        'title' => 'Liên hệ',
                        'name' => 'contacts',
                    ])
                @endif

                @if (auth()->user()?->canAnyPermission(['settings.view', 'settings.update', 'settings.logs']))
                    @include('part.backend.menu_item', [
                        'title' => 'Cấu hình website',
                        'name' => 'settings',
                    ])
                @endif

                @if (auth()->user()?->hasPermission('logs.view'))
                    @include('part.backend.menu_item', [
                        'title' => 'Logs',
                        'name' => 'activelogs',
                    ])
                @endif
            </div>
        </div>
        <div class="sb-sidenav-footer">
            <div class="small">Đăng nhập:</div>
            {{ Auth::user()->name ?? 'Admin' }}
        </div>
    </nav>
</div>
