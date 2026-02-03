@php
    $student = auth('students')->user();
    $unreadCount = $student?->unreadNotifications()->count() ?? 0;
    $notifications = $student ? $student->notifications()->latest()->take(10)->get() : collect();
@endphp

<header class="header">
    <div class="action-bar">
        <div class="container">
            <div class="row align-items-center">
                <div class="d-none d-lg-block col-lg-2">
                    <form>
                        <input type="text" placeholder="Bạn tìm gì..." />
                        <button type="submit" class="btn btn-primary">Tìm</button>
                    </form>
                </div>
                <div class="d-none d-lg-block col-lg-7">
                    <div class="d-flex">
                        <p class="slogan">
                            <i class="fas fa-phone"></i>Tư vấn & hỗ trợ:
                            <a href="#">{{ setting('phone', '012345678') }}</a>
                        </p>
                        <p class="mail">
                            <i class="far fa-envelope"></i>
                            <a href="#">{{ setting('email', 'bigk@gmail.com') }}</a>
                        </p>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="social">
                        @if (auth('students')->check())
                            <div class="d-flex align-items-center gap-3">

                                {{-- 🔔 Chuông thông báo --}}
                                <div class="dropdown">
                                    <button class="btn btn-light position-relative" type="button"
                                        id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-bell"></i>

                                        {{-- Badge số thông báo --}}
                                        @if ($unreadCount > 0)
                                            <span
                                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                                {{ $unreadCount }}
                                            </span>
                                        @endif
                                    </button>

                                    <ul class="dropdown-menu dropdown-menu-end notification-dropdown"
                                        aria-labelledby="notificationDropdown" style="width: 320px;">

                                        <li class="dropdown-header fw-bold">
                                            Thông báo
                                        </li>

                                        @forelse($notifications as $notification)
                                            <li>
                                                <a class="dropdown-item notification-item {{ is_null($notification->read_at) ? 'fw-bold' : '' }}"
                                                    href="{{ route('students.notifications.read', $notification->id) }}">

                                                    <div class="notification-title">
                                                        {{ $notification->data['title'] ?? 'Thông báo' }}
                                                    </div>

                                                    <div class="notification-message">
                                                        {{ $notification->data['message'] ?? '' }}
                                                    </div>

                                                    <div class="notification-time">
                                                        {{ $notification->created_at->diffForHumans() }}
                                                    </div>
                                                </a>
                                            </li>
                                        @empty
                                            <li class="dropdown-item text-muted small">
                                                Không có thông báo
                                            </li>
                                        @endforelse

                                    </ul>
                                </div>

                                {{-- 👤 User dropdown --}}
                                <div class="dropdown">
                                    <button class="btn btn-primary dropdown-toggle d-flex align-items-center gap-2"
                                        type="button" id="userDropdown" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="fas fa-user-circle"></i>
                                        <span>{{ auth('students')->user()->name }}</span>
                                    </button>

                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2"
                                                href="{{ route('students.account.index') }}">
                                                <i class="fas fa-user-circle"></i> Tài khoản của tôi
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2 text-danger"
                                                href="#"
                                                onclick="document['form-logout'].submit(); return false;">
                                                <i class="fas fa-sign-out-alt"></i> Đăng xuất
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                            </div>
                        @else
                            <button class="btn btn-primary">
                                <a href="{{ route('clients-register') }}" class="text-white"
                                    style="text-decoration: none !important"><i class="fas fa-user"></i> Đăng ký</a>
                            </button>
                            <button class="btn btn-primary">
                                <a href="{{ route('clients-login') }}" class="text-white"
                                    style="text-decoration: none !important"><i class="fas fa-key"></i> Đăng nhập</a>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">

            {{-- Logo --}}
            <a class="navbar-brand" href="{{ route('home') }}">
                <img src="{{ asset('clients/assets/logo.png') }}" alt="Logo" />
            </a>

            {{-- Toggle mobile --}}
            <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            {{-- Menu --}}
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav me-auto">

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}"
                            href="{{ route('home') }}">
                            <i class="fas fa-home"></i>
                            Home
                        </a>
                    </li>

                    <li class="nav-item dropdown dropdown-hover">
                        <a class="nav-link
        {{ request()->routeIs('courses.*') ? 'active' : '' }}"
                            href="#" id="coursesDropdown" role="button">
                            <i class="fas fa-tv"></i>
                            Danh mục khóa học
                        </a>

                        <ul class="dropdown-menu">
                            @foreach ($courseCategories as $category)
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item" href="{{ route('categories.category', $category->slug) }}">
                                        {{ $category->name }}
                                    </a>
                                    {{-- <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('courses.category', $category->slug) }}">
                                                {{ $category->name }} ({{ $category->courses_count }})
                                            </a>
                                        </li>
                                    </ul> --}}
                                </li>
                            @endforeach

                        </ul>
                    </li>


                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('coupons.*') ? 'active' : '' }}"
                            href="{{ route('coupons.home') }}">
                            <i class="fas fa-ticket-alt"></i>
                            Mã giảm giá
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('coupons.*') ? 'active' : '' }}"
                            href="{{ route('contacts.home') }}">
                            <i class="fas fa-phone-alt"></i>
                            Liên hệ
                        </a>
                    </li>

                </ul>
            </div>
        </div>
    </nav>

</header>
<form action="{{ route('clients-logout') }}" method="post" name="form-logout">@csrf</form>
