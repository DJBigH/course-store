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
                <!-- NHÓM 1: QUẢN LÝ CHUNG -->
                <div class="sb-sidenav-menu-heading">Quản lý chung</div>
                @if (auth()->user()?->hasPermission('dashboard.view'))
                    <a class="nav-link {{ request()->is('admin') ? 'active' : '' }}" href="{{ route('admin.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                        Tổng quan
                    </a>
                    <a class="nav-link {{ request()->is('admin/notifications*') ? 'active' : '' }}" href="{{ route('admin.notifications.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-bell"></i></div>
                        Thông báo hệ thống
                    </a>
                    <a class="nav-link {{ request()->is('admin/announcements*') ? 'active' : '' }}" href="{{ route('admin.announcements.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-bullhorn"></i></div>
                        Gửi thông báo (Broadcast)
                    </a>
                @endif

                <!-- NHÓM 2: NỘI DUNG & TIẾP THỊ -->
                <div class="sb-sidenav-menu-heading">Nội dung & Tiếp thị</div>
                
                {{-- Khóa học --}}
                @if(auth()->user()->hasPermission('courses.view') || auth()->user()->hasPermission('categories.view'))
                    @php $isCourseGroupOpen = request()->is('admin/courses*') || request()->is('admin/categories*') || request()->is('admin/lessons*'); @endphp
                    <div class="sidebar-group {{ $isCourseGroupOpen ? 'is-open' : '' }}" data-sidebar-group="courses">
                        <a class="nav-link collapsed {{ $isCourseGroupOpen ? 'is-active' : '' }}" href="#" data-bs-toggle="collapse" data-bs-target="#collapseCourses" aria-expanded="false" data-sidebar-toggle>
                            <div class="sb-nav-link-icon"><i class="fas fa-graduation-cap"></i></div>
                            <span class="sidebar-link-label">Quản lý Khóa học</span>
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down sidebar-group__icon"></i></div>
                        </a>
                        <div class="collapse {{ $isCourseGroupOpen ? 'show' : '' }}" id="collapseCourses" data-bs-parent="#sidenavAccordion" data-sidebar-content>
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link {{ (request()->is('admin/courses') || request()->is('admin/courses/edit*') || request()->is('admin/courses/create*')) ? 'active' : '' }}" href="{{ route('courses.index') }}">Danh sách khóa học</a>
                                <a class="nav-link {{ request()->is('admin/courses/bundles*') ? 'active' : '' }}" href="{{ route('courses.bundles.index') }}">Quản lý Combo</a>
                                <a class="nav-link {{ request()->is('admin/categories*') ? 'active' : '' }}" href="{{ route('categories.index') }}">Chuyên mục</a>
                                @if(auth()->user()->hasPermission('comments.moderate'))
                                    <a class="nav-link {{ request()->is('admin/courses/comments*') ? 'active' : '' }}" href="{{ route('courses.comments.admin') }}">Bình luận khóa học</a>
                                @endif
                                <a class="nav-link {{ request()->is('admin/courses/ratings*') ? 'active' : '' }}" href="{{ route('courses.ratings.index') }}">Quản lý đánh giá</a>
                            </nav>
                        </div>
                    </div>
                @endif

                {{-- Tiếp thị --}}
                @if(auth()->user()->hasPermission('coupons.view') || auth()->user()->hasPermission('chatbot.view'))
                    @php $isMarketingGroupOpen = request()->is('admin/coupons*') || request()->is('admin/chatbot-knowledge*'); @endphp
                    <div class="sidebar-group {{ $isMarketingGroupOpen ? 'is-open' : '' }}" data-sidebar-group="marketing">
                        <a class="nav-link collapsed {{ $isMarketingGroupOpen ? 'is-active' : '' }}" href="#" data-bs-toggle="collapse" data-bs-target="#collapseMarketing" aria-expanded="false" data-sidebar-toggle>
                            <div class="sb-nav-link-icon"><i class="fas fa-bullhorn"></i></div>
                            <span class="sidebar-link-label">Tiếp thị & Công cụ</span>
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down sidebar-group__icon"></i></div>
                        </a>
                        <div class="collapse {{ $isMarketingGroupOpen ? 'show' : '' }}" id="collapseMarketing" data-bs-parent="#sidenavAccordion" data-sidebar-content>
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link {{ request()->is('admin/coupons*') ? 'active' : '' }}" href="{{ route('coupons.index') }}">Mã giảm giá</a>
                                <a class="nav-link {{ (request()->is('admin/chatbot-knowledge') || request()->is('admin/chatbot-knowledge/edit*')) ? 'active' : '' }}" href="{{ route('chatbot-knowledge.index') }}">Train Bot AI</a>
                                <a class="nav-link {{ request()->is('admin/chatbot-knowledge/unresolved*') ? 'active' : '' }}" href="{{ route('chatbot-knowledge.unresolved') }}">Câu hỏi chưa đáp</a>
                            </nav>
                        </div>
                    </div>
                @endif

                <!-- NHÓM 3: GIẢNG VIÊN & TÀI CHÍNH -->
                <div class="sb-sidenav-menu-heading">Giảng viên & Tài chính</div>
                
                {{-- Giảng viên --}}
                @if (auth()->user()?->hasPermission('teachers.view'))
                    @php $isTeacherGroupOpen = (request()->is('admin/teacher*') || request()->is('admin/teacher-applications*') || request()->is('admin/teacher-packages*') || request()->is('admin/teacher-announcements*')) && !request()->is('admin/teacher-finance*'); @endphp
                    <div class="sidebar-group {{ $isTeacherGroupOpen ? 'is-open' : '' }}" data-sidebar-group="teachers">
                        <a class="nav-link collapsed {{ $isTeacherGroupOpen ? 'is-active' : '' }}" href="#" data-bs-toggle="collapse" data-bs-target="#collapseTeachers" aria-expanded="false" data-sidebar-toggle>
                            <div class="sb-nav-link-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                            <span class="sidebar-link-label">Quản lý Giảng viên</span>
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down sidebar-group__icon"></i></div>
                        </a>
                        <div class="collapse {{ $isTeacherGroupOpen ? 'show' : '' }}" id="collapseTeachers" data-bs-parent="#sidenavAccordion" data-sidebar-content>
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link {{ (request()->is('admin/teacher') || request()->is('admin/teacher/edit*')) ? 'active' : '' }}" href="{{ route('teacher.index') }}">Danh sách GV</a>
                                <a class="nav-link {{ request()->is('admin/teacher/badges*') ? 'active' : '' }}" href="{{ route('teacher.badges.index') }}">
                                    <div class="sb-nav-link-icon"><i class="fas fa-award"></i></div>
                                    Quản lý Huy hiệu
                                </a>
                                <a class="nav-link {{ request()->is('admin/teacher-applications*') ? 'active' : '' }}" href="{{ route('teacher-applications.index') }}">Đơn ứng tuyển</a>
                                <a class="nav-link {{ (request()->is('admin/teacher-packages') || request()->is('admin/teacher-package-features*')) ? 'active' : '' }}" href="{{ route('teacher-packages.index') }}">Gói cước & Tính năng</a>
                                <a class="nav-link {{ request()->is('admin/teacher-packages/grant*') ? 'active' : '' }}" href="{{ route('teacher-packages.grant') }}">Cấp gói đặc quyền</a>
                                <a class="nav-link {{ request()->is('admin/teacher-announcements*') ? 'active' : '' }}" href="{{ route('teacher-announcements.index') }}">Thông báo GV</a>
                                <a class="nav-link {{ request()->is('admin/teacher-finance/cancellations*') ? 'active' : '' }}" href="{{ route('teacher-finance.cancellations.index') }}">Yêu cầu hủy hợp tác</a>
                            </nav>
                        </div>
                    </div>
                @endif

                {{-- Tài chính --}}
                @if (auth()->user()?->hasPermission('teachers.view'))
                    @php $isFinanceGroupOpen = request()->is('admin/teacher-finance/earnings*') || request()->is('admin/teacher-finance/payouts*'); @endphp
                    <div class="sidebar-group {{ $isFinanceGroupOpen ? 'is-open' : '' }}" data-sidebar-group="finance">
                        <a class="nav-link collapsed {{ $isFinanceGroupOpen ? 'is-active' : '' }}" href="#" data-bs-toggle="collapse" data-bs-target="#collapseFinance" aria-expanded="false" data-sidebar-toggle>
                            <div class="sb-nav-link-icon"><i class="fas fa-wallet"></i></div>
                            <span class="sidebar-link-label">Quản lý Tài chính</span>
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down sidebar-group__icon"></i></div>
                        </a>
                        <div class="collapse {{ $isFinanceGroupOpen ? 'show' : '' }}" id="collapseFinance" data-bs-parent="#sidenavAccordion" data-sidebar-content>
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link {{ request()->is('admin/teacher-finance/earnings*') ? 'active' : '' }}" href="{{ route('teacher-finance.earnings') }}">Đối soát doanh thu</a>
                                <a class="nav-link {{ request()->is('admin/teacher-finance/payouts*') ? 'active' : '' }}" href="{{ route('teacher-finance.payouts') }}">Xử lý rút tiền</a>
                            </nav>
                        </div>
                    </div>
                @endif

                <!-- NHÓM 4: HỌC VIÊN & HỖ TRỢ -->
                <div class="sb-sidenav-menu-heading">Học viên & Hỗ trợ</div>
                
                {{-- Bán hàng --}}
                @if(auth()->user()->hasPermission('students.view') || auth()->user()->hasPermission('orders.view'))
                    @php $isSalesGroupOpen = request()->is('admin/students*') || request()->is('admin/orders*'); @endphp
                    <div class="sidebar-group {{ $isSalesGroupOpen ? 'is-open' : '' }}" data-sidebar-group="sales">
                        <a class="nav-link collapsed {{ $isSalesGroupOpen ? 'is-active' : '' }}" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSales" aria-expanded="false" data-sidebar-toggle>
                            <div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i></div>
                            <span class="sidebar-link-label">Học viên & Đơn hàng</span>
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down sidebar-group__icon"></i></div>
                        </a>
                        <div class="collapse {{ $isSalesGroupOpen ? 'show' : '' }}" id="collapseSales" data-bs-parent="#sidenavAccordion" data-sidebar-content>
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link {{ request()->is('admin/students*') ? 'active' : '' }}" href="{{ route('students.index') }}">Danh sách học viên</a>
                                <a class="nav-link {{ request()->is('admin/orders*') ? 'active' : '' }}" href="{{ route('orders.index') }}">Quản lý đơn hàng</a>
                            </nav>
                        </div>
                    </div>
                @endif

                {{-- Hỗ trợ --}}
                @if (auth()->user()?->hasPermission('contacts.view'))
                    @php $isSupportGroupOpen = request()->is('admin/contacts*'); @endphp
                    <div class="sidebar-group {{ $isSupportGroupOpen ? 'is-open' : '' }}" data-sidebar-group="support">
                        <a class="nav-link collapsed {{ $isSupportGroupOpen ? 'is-active' : '' }}" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSupport" aria-expanded="false" data-sidebar-toggle>
                            <div class="sb-nav-link-icon"><i class="fas fa-headset"></i></div>
                            <span class="sidebar-link-label">Hỗ trợ khách hàng</span>
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down sidebar-group__icon"></i></div>
                        </a>
                        <div class="collapse {{ $isSupportGroupOpen ? 'show' : '' }}" id="collapseSupport" data-bs-parent="#sidenavAccordion" data-sidebar-content>
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link {{ request()->is('admin/contacts') ? 'active' : '' }}" href="{{ route('contacts.index') }}">Liên hệ</a>
                                <a class="nav-link {{ request()->is('admin/contacts/support*') ? 'active' : '' }}" href="{{ route('contacts.support-index') }}">Góp ý & Báo cáo</a>
                            </nav>
                        </div>
                    </div>
                @endif

                <!-- NHÓM 5: HỆ THỐNG -->
                <div class="sb-sidenav-menu-heading">Hệ thống & Cấu hình</div>
                @php $isSystemGroupOpen = request()->is('admin/settings*') || request()->is('admin/user*') || request()->is('admin/groups*') || request()->is('admin/permissions*') || request()->is('admin/activelogs*'); @endphp
                <div class="sidebar-group {{ $isSystemGroupOpen ? 'is-open' : '' }}" data-sidebar-group="system">
                    <a class="nav-link collapsed {{ $isSystemGroupOpen ? 'is-active' : '' }}" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSystem" aria-expanded="false" data-sidebar-toggle>
                        <div class="sb-nav-link-icon"><i class="fas fa-cogs"></i></div>
                        <span class="sidebar-link-label">Cấu hình hệ thống</span>
                        <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down sidebar-group__icon"></i></div>
                    </a>
                    <div class="collapse {{ $isSystemGroupOpen ? 'show' : '' }}" id="collapseSystem" data-bs-parent="#sidenavAccordion" data-sidebar-content>
                        <nav class="sb-sidenav-menu-nested nav">
                            @if(auth()->user()->hasPermission('settings.view'))
                                <a class="nav-link {{ request()->is('admin/settings*') ? 'active' : '' }}" href="{{ route('settings.index') }}">Cấu hình website</a>
                            @endif
                            @if(auth()->user()->hasPermission('users.view'))
                                <a class="nav-link {{ request()->is('admin/user*') ? 'active' : '' }}" href="{{ route('user.index') }}">Quản lý người dùng</a>
                            @endif
                            @if(auth()->user()->hasPermission('groups.view') || auth()->user()->hasPermission('permissions.view'))
                                <a class="nav-link {{ request()->is('admin/groups*') ? 'active' : '' }}" href="{{ route('groups.index') }}">Phân quyền</a>
                            @endif
                            @if(auth()->user()->hasPermission('logs.view'))
                                <a class="nav-link {{ request()->is('admin/activelogs*') ? 'active' : '' }}" href="{{ route('activelogs.index') }}">Nhật ký hoạt động</a>
                            @endif
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="sb-sidenav-footer">
            <div class="small">Đăng nhập:</div>
            <div class="sidebar-user-name text-truncate" title="{{ Auth::user()->name ?? 'Admin' }}">
                {{ Auth::user()->name ?? 'Admin' }}
            </div>
        </div>
    </nav>
</div>

<script>
    (() => {
        const storageKey = 'admin-sidebar-state';
        const groups = document.querySelectorAll('[data-sidebar-group]');
        
        // Khôi phục trạng thái từ localStorage
        try {
            const state = JSON.parse(localStorage.getItem(storageKey)) || {};
            groups.forEach(group => {
                const id = group.getAttribute('data-sidebar-group');
                const content = group.querySelector('[data-sidebar-content]');
                const toggle = group.querySelector('[data-sidebar-toggle]');
                
                if (state[id] === true) {
                    group.classList.add('is-open');
                    if (content) content.classList.add('show');
                    if (toggle) {
                        toggle.classList.remove('collapsed');
                        toggle.setAttribute('aria-expanded', 'true');
                    }
                } else if (state[id] === false) {
                    group.classList.remove('is-open');
                    if (content) content.classList.remove('show');
                    if (toggle) {
                        toggle.classList.add('collapsed');
                        toggle.setAttribute('aria-expanded', 'false');
                    }
                }
            });
        } catch (e) {
            console.error('Error restoring sidebar state:', e);
        }

        // Lắng nghe sự kiện click để lưu trạng thái
        groups.forEach(group => {
            const toggle = group.querySelector('[data-sidebar-toggle]');
            const id = group.getAttribute('data-sidebar-group');

            if (toggle) {
                toggle.addEventListener('click', () => {
                    setTimeout(() => {
                        const isOpen = !toggle.classList.contains('collapsed');
                        group.classList.toggle('is-open', isOpen);
                        
                        const state = JSON.parse(localStorage.getItem(storageKey)) || {};
                        state[id] = isOpen;
                        localStorage.setItem(storageKey, JSON.stringify(state));
                    }, 50);
                });
            }
        });
    })();
</script>


