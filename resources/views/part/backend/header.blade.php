<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <a class="navbar-brand ps-3" href="{{ route('admin.index') }}">{{ setting('site_name', 'BigK Udemy') }}</a>

    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!">
        <i class="fas fa-bars"></i>
    </button>

    <div class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
        <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" target="_blank" class="text-white">Xem website</a>
    </div>

    <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4 align-items-center">
        @php
            $admin = auth()->user();
        @endphp

        <li class="nav-item me-2">
            <button type="button" class="theme-toggle-admin" data-admin-theme-toggle
                title="Switch to dark mode" aria-label="Switch to dark mode" aria-pressed="false">
                <i class="fas fa-moon theme-toggle-admin__icon-dark" aria-hidden="true"></i>
                <i class="fas fa-sun theme-toggle-admin__icon-light" aria-hidden="true"></i>
                <span data-admin-theme-label>Dark mode</span>
            </button>
        </li>

        <li class="nav-item dropdown me-3">
            <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button"
                data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-bell"></i>

                @if ($admin->unreadNotifications->count())
                    <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">
                        {{ $admin->unreadNotifications->count() }}
                    </span>
                @endif
            </a>

            <div class="dropdown-menu dropdown-menu-end shadow p-0 notification-dropdown-menu"
                aria-labelledby="notificationDropdown">
                <div class="dropdown-header fw-bold border-bottom px-3 py-2">
                    Thông báo
                </div>
                <div class="notification-list">
                    @forelse($admin->notifications()->latest()->limit(20)->get() as $notification)
                        @php
                            $notificationTitle = notificationText($notification, 'title', 'Thông báo mới');
                            $notificationMessage = notificationText($notification, 'message', 'Bạn có thông báo mới');
                            $notificationType = notificationTypeLabel($notification->type);
                            $severityClass = notificationSeverityClass($notification);
                        @endphp
                        <a href="{{ route('admin.notifications.read', $notification->id) }}"
                            class="notification-item d-flex px-3 py-2 {{ $notification->read_at ? '' : 'unread' }}">
                            <div class="me-2 mt-1 text-{{ $severityClass }}">
                                <i class="{{ notificationIconClass($notification) }}"></i>
                            </div>

                            <div class="flex-grow-1 min-w-0">
                                @if ($notificationTitle !== '')
                                    <div class="fw-semibold small mb-1">{{ $notificationTitle }}</div>
                                @endif
                                <div class="notification-text small">
                                    {{ $notificationMessage }}
                                </div>
                                <div class="notification-time text-muted small mt-1 d-flex justify-content-between gap-2 flex-wrap">
                                    <span>{{ $notification->created_at->diffForHumans() }}</span>
                                    <span class="badge bg-{{ $severityClass }}">{{ $notificationType }}</span>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="text-center text-muted py-3">
                            Không có thông báo
                        </div>
                    @endforelse
                </div>
                <div class="border-top px-3 py-2 d-flex justify-content-between align-items-center gap-2 notification-dropdown-footer">
                    <a href="{{ route('admin.notifications.index') }}" class="small fw-semibold">Xem tất cả</a>
                    @if ($admin->unreadNotifications->count())
                        <form action="{{ route('admin.notifications.mark-all-read') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-link p-0 text-decoration-none">Đánh dấu đã đọc</button>
                        </form>
                    @endif
                </div>
            </div>
        </li>

        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button"
                data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user fa-fw"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <li>
                    <a class="dropdown-item" href="{{ route('user.show') }}">
                        Thông tin cá nhân
                    </a>
                </li>
                <li>
                    <hr class="dropdown-divider" />
                </li>
                <li>
                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="dropdown-item logout-action">
                            Đăng xuất
                        </button>
                    </form>
                </li>
            </ul>
        </li>
    </ul>
</nav>
