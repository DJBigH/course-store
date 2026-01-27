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
                            <a href="#">0123456789</a>
                        </p>
                        <p class="mail">
                            <i class="far fa-envelope"></i>
                            <a href="#">BigK@gmail.com</a>
                        </p>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="social">
                        @if (auth('students')->check())
                            <div class="dropdown">
                                <button class="btn btn-primary dropdown-toggle d-flex align-items-center gap-2"
                                    type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
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
                                            href="#" onclick="document['form-logout'].submit(); return false;">
                                            <i class="fas fa-sign-out-alt"></i> Đăng xuất
                                        </a>
                                    </li>
                                </ul>
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
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                            <i class="fas fa-home"></i>
                            Home
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('courses.*') ? 'active' : '' }}"
                            href="{{ route('courses.home') }}">
                            <i class="fas fa-tv"></i>
                            Khóa học
                        </a>
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
                            href="{{ route('contacts.index') }}">
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
