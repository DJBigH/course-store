<style>
    #layoutSidenav_nav {
        overflow: hidden;
    }

    #layoutSidenav_nav .sb-sidenav {
        width: 100%;
        overflow-x: hidden;
    }

    #layoutSidenav_nav .sb-sidenav .sb-sidenav-menu {
        padding-bottom: 5rem;
        overflow-x: hidden;
        scrollbar-width: thin;
        scrollbar-color: rgba(148, 163, 184, 0.45) transparent;
    }

    #layoutSidenav_nav .sb-sidenav .sb-sidenav-menu::-webkit-scrollbar {
        width: 8px;
    }

    #layoutSidenav_nav .sb-sidenav .sb-sidenav-menu::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, 0.35);
        border-radius: 999px;
    }

    #layoutSidenav_nav .sb-sidenav .sb-sidenav-menu .nav {
        gap: 0.18rem;
        padding: 0 0.5rem 0.75rem;
    }

    #layoutSidenav_nav .sb-sidenav .sb-sidenav-menu .sb-sidenav-menu-heading {
        margin: 0.2rem 0 0.1rem;
        padding-left: 1rem;
        padding-right: 1rem;
    }

    #layoutSidenav_nav .sb-sidenav .sb-sidenav-menu .nav > .nav-link,
    #layoutSidenav_nav .sb-sidenav .sb-sidenav-menu .nav > a.nav-link {
        min-height: 46px;
    }

    #layoutSidenav_nav .sb-sidenav .sb-sidenav-menu .nav .nav-link {
        width: calc(100% - 0.5rem);
        margin-left: 0.25rem;
        margin-right: 0.25rem;
        box-sizing: border-box;
    }

    #layoutSidenav_nav .sb-sidenav .sb-sidenav-collapse-arrow {
        flex-shrink: 0;
    }

    #layoutSidenav_nav .sb-sidenav .sb-sidenav-footer {
        min-height: 72px;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 0.25rem;
        line-height: 1.35;
        overflow: hidden;
        border-top: 1px solid rgba(148, 163, 184, 0.14);
        padding-left: 1rem;
        padding-right: 1rem;
        background: rgba(15, 23, 42, 0.4);
        backdrop-filter: blur(6px);
    }

    #layoutSidenav_nav .sb-sidenav .sb-sidenav-footer .small {
        margin-bottom: 0;
        font-size: 0.76rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.56);
    }

    #layoutSidenav_nav .sb-sidenav .sidebar-user-name,
    #layoutSidenav_nav .sb-sidenav .sidebar-link-label {
        display: block;
        min-width: 0;
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    #layoutSidenav_nav .sb-sidenav .sidebar-user-name {
        color: rgba(255, 255, 255, 0.92);
        font-weight: 600;
    }

    #layoutSidenav_nav .sb-sidenav .sb-sidenav-menu-nested {
        margin-left: 0.5rem;
        margin-right: 0.25rem;
        padding-bottom: 0.55rem;
    }

    #layoutSidenav_nav .sb-sidenav .sb-sidenav-menu-nested .nav-link {
        width: auto;
        min-height: 40px;
        padding-right: 1rem;
        border-radius: 12px;
    }

    #layoutSidenav_nav .sb-sidenav .sb-sidenav-menu-nested .nav-link:hover,
    #layoutSidenav_nav .sb-sidenav .sb-sidenav-menu-nested .nav-link.active {
        background: rgba(255, 255, 255, 0.08);
    }
</style>
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

                @if (auth()->user()
                        ?->canAnyPermission(['courses.view', 'courses.create', 'courses.edit', 'courses.publish', 'comments.moderate']))
                    @include('part.backend.menu_item', [
                        'title' => 'Khóa học',
                        'name' => 'courses',
                        'includes' => ['/admin/lessons/*'],
                    ])
                @endif

                @if (auth()->user()
                        ?->canAnyPermission([
                            'categories.view',
                            'categories.create',
                            'categories.edit',
                            'categories.delete',
                            'categories.logs',
                        ]))
                    @include('part.backend.menu_item', [
                        'title' => 'Chuyên mục',
                        'name' => 'categories',
                    ])
                @endif

                @if (auth()->user()
                        ?->canAnyPermission(['teachers.view', 'teachers.create', 'teachers.edit', 'teachers.delete', 'teachers.logs']))
                    @include('part.backend.menu_item', [
                        'title' => 'Giảng viên',
                        'name' => 'teacher',
                    ])
                    <a class="nav-link {{ request()->is('admin/teacher-applications*') ? 'active' : '' }}"
                        href="{{ route('teacher-applications.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-user-check"></i></div>
                        Ung tuyen giang vien
                    </a>
                    <a class="nav-link {{ request()->is('admin/teacher-packages*') ? 'active' : '' }}"
                        href="{{ route('teacher-packages.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-layer-group"></i></div>
                        Goi giang vien
                    </a>
                    <a class="nav-link {{ request()->is('admin/teacher-package-features*') ? 'active' : '' }}"
                        href="{{ route('teacher-package-features.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-list-check"></i></div>
                        Tinh nang goi (Moi)
                    </a>
                    <a class="nav-link {{ request()->is('admin/teacher-announcements*') ? 'active' : '' }}"
                        href="{{ route('teacher-announcements.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-bullhorn"></i></div>
                        Thong bao teacher
                    </a>
                    <a class="nav-link {{ request()->is('admin/teacher-finance/earnings*') ? 'active' : '' }}"
                        href="{{ route('teacher-finance.earnings') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-chart-line"></i></div>
                        Doi soat doanh thu
                    </a>
                    <a class="nav-link {{ request()->is('admin/teacher-finance/payouts*') ? 'active' : '' }}"
                        href="{{ route('teacher-finance.payouts') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-money-check-dollar"></i></div>
                        Xu ly rut tien
                    </a>
                    <a class="nav-link {{ request()->is('admin/teacher-finance/cancellations*') ? 'active' : '' }}"
                        href="{{ route('teacher-finance.cancellations.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-user-slash"></i></div>
                        Yêu cầu hủy hợp tác
                    </a>
                @endif

                @if (auth()->user()
                        ?->canAnyPermission(['students.view', 'students.create', 'students.edit', 'students.delete', 'students.logs']))
                    @include('part.backend.menu_item', [
                        'title' => 'Học viên',
                        'name' => 'students',
                    ])
                @endif

                @if (auth()->user()
                        ?->canAnyPermission(['users.view', 'users.create', 'users.edit', 'users.delete', 'users.logs']))
                    @include('part.backend.menu_item', [
                        'title' => 'Người dùng',
                        'name' => 'user',
                    ])
                @endif

                @if (auth()->user()
                        ?->canAnyPermission(['groups.view', 'groups.create', 'groups.edit', 'groups.delete', 'groups.manage']))
                    <a class="nav-link {{ request()->is('admin/groups*') ? 'active' : '' }}"
                        href="{{ route('groups.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-user-shield"></i></div>
                        Nhóm quyền
                    </a>
                @endif

                @if (auth()->user()
                        ?->canAnyPermission([
                            'permissions.view',
                            'permissions.create',
                            'permissions.edit',
                            'permissions.delete',
                            'permissions.manage',
                        ]))
                    <a class="nav-link {{ request()->is('admin/permissions*') ? 'active' : '' }}"
                        href="{{ route('permissions.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-key"></i></div>
                        Quyền chi tiết
                    </a>
                @endif

                @if (auth()->user()
                        ?->canAnyPermission(['orders.view', 'orders.update', 'orders.delete']))
                    @include('part.backend.menu_item', [
                        'title' => 'Đơn hàng',
                        'name' => 'orders',
                    ])
                @endif

                @if (auth()->user()
                        ?->canAnyPermission([
                            'coupons.view',
                            'coupons.create',
                            'coupons.edit',
                            'coupons.delete',
                            'coupons.assign',
                            'coupons.logs',
                        ]))
                    @include('part.backend.menu_item', [
                        'title' => 'Mã giảm giá',
                        'name' => 'coupons',
                    ])
                @endif

                @if (auth()->user()
                        ?->canAnyPermission(['contacts.view', 'contacts.update', 'contacts.delete', 'contacts.logs']))
                    @include('part.backend.menu_item', [
                        'title' => 'Liên hệ',
                        'name' => 'contacts',
                    ])
                    <a class="nav-link {{ request()->is('admin/contacts/support*') ? 'active' : '' }}"
                        href="{{ route('contacts.support-index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-life-ring"></i></div>
                        Gop y bao cao
                    </a>
                @endif

                @if (auth()->user()
                        ?->canAnyPermission(['settings.view', 'settings.update', 'settings.logs']))
                    @include('part.backend.menu_item', [
                        'title' => 'Cấu hình website',
                        'name' => 'settings',
                    ])
                @endif

                @if (auth()->user()
                        ?->canAnyPermission(['chatbot.view', 'chatbot.create', 'chatbot.edit', 'chatbot.delete', 'chatbot.logs']))
                    @include('part.backend.menu_item', [
                        'title' => 'Train bot',
                        'name' => 'chatbot-knowledge',
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
            <div class="sidebar-user-name">{{ Auth::user()->name ?? 'Admin' }}</div>
        </div>
    </nav>
</div>


