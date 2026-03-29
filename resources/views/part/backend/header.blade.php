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

            <div class="dropdown-menu dropdown-menu-end shadow p-0" aria-labelledby="notificationDropdown"
                style="width: 360px;">
                <div class="dropdown-header fw-bold border-bottom px-3 py-2">
                    Thông báo
                </div>

                <div class="notification-list">
                    @forelse($admin->notifications()->latest()->limit(20)->get() as $notification)
                        <a href="{{ route('admin.notifications.read', $notification->id) }}"
                            class="notification-item d-flex px-3 py-2 {{ $notification->read_at ? '' : 'unread' }}">
                            <div class="me-2 mt-1">
                                <i class="fas fa-circle text-primary" style="font-size: 6px"></i>
                            </div>

                            <div class="flex-grow-1">
                                @php
                                    $notificationTitle = notificationText($notification, 'title', 'Thong bao moi');
                                    $notificationMessage = notificationText($notification, 'message', 'Ban co thong bao moi');
                                @endphp
                                @if ($notificationTitle !== '')
                                    <div class="fw-semibold small mb-1">{{ $notificationTitle }}</div>
                                @endif
                                <div class="notification-text small">
                                    {{ $notificationMessage }}
                                </div>
                                <div class="notification-time text-muted small mt-1">
                                    {{ $notification->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="text-center text-muted py-3">
                            Không có thông báo
                        </div>
                    @endforelse
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
