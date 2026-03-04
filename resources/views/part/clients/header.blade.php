@php
    $student = auth('students')->user();
    $unreadCount = $student?->unreadNotifications()->count() ?? 0;
    $notifications = $student ? $student->notifications()->latest()->take(20)->get() : collect();

    $localeOptions = [
        'vi' => ['flag' => '🇻🇳', 'short' => 'VI', 'label' => 'Tiếng Việt'],
        'en' => ['flag' => '🇺🇸', 'short' => 'EN', 'label' => 'English'],
        'ko' => ['flag' => '🇰🇷', 'short' => 'KO', 'label' => '한국어'],
        'ja' => ['flag' => '🇯🇵', 'short' => 'JA', 'label' => '日本語'],
        'zh' => ['flag' => '🇨🇳', 'short' => 'ZH', 'label' => '中文'],
    ];
    $supportedLocales = array_keys($localeOptions);

    $currentLocale = app()->getLocale();
    if (!in_array($currentLocale, $supportedLocales)) {
        $currentLocale = 'vi';
    }

    // path hiện tại (không có domain), ví dụ: "vi/courses/abc"
    $path = trim(request()->path(), '/');
    $segments = $path === '' ? [] : explode('/', $path);

    // nếu segment đầu là locale thì bỏ ra
    if (!empty($segments) && in_array($segments[0], $supportedLocales)) {
        array_shift($segments);
    }

    $restPath = implode('/', $segments); // ví dụ: "courses/abc"
    $localeUrls = [];
    foreach ($supportedLocales as $locale) {
        $localeUrls[$locale] = url($locale . ($restPath ? '/' . $restPath : ''));
    }

    // giữ query string ?page=2...
    $qs = request()->getQueryString();
    if ($qs) {
        foreach ($localeUrls as $locale => $localeUrl) {
            $localeUrls[$locale] = $localeUrl . '?' . $qs;
        }
    }
@endphp

<header class="header">
    <div class="action-bar">
        <div class="container">
            <div class="row align-items-center">
                <div class="d-none d-lg-block col-lg-2">
                    <form>
                        <input type="text" placeholder="{{ __('clients/common.search_placeholder') }}" />
                        <button type="submit" class="btn btn-primary">{{ __('clients/common.search') }}</button>
                    </form>
                </div>
                <div class="d-none d-lg-block col-lg-7">
                    <div class="d-flex">
                        <p class="slogan">
                            <i class="fas fa-phone"></i>{{ __('clients/common.support') }}
                            <a href="#">{{ setting('phone', '012345678') }}</a>
                        </p>
                        <p class="mail">
                            <i class="far fa-envelope"></i>
                            <a href="#">{{ setting('email', 'bigk@gmail.com') }}</a>
                        </p>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="social d-flex align-items-center justify-content-end gap-2">

                        {{-- 🌐 LANGUAGE SWITCH --}}
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle d-flex align-items-center gap-2"
                                data-bs-toggle="dropdown">
                                <span>{{ $localeOptions[$currentLocale]['flag'] }}</span>
                                {{ $localeOptions[$currentLocale]['short'] }}
                            </button>

                            <ul class="dropdown-menu dropdown-menu-end">
                                @foreach ($localeOptions as $locale => $option)
                                    <li>
                                        <a class="dropdown-item d-flex gap-2 {{ $currentLocale === $locale ? 'active' : '' }}"
                                            href="{{ $localeUrls[$locale] }}">
                                            {{ $option['flag'] }} {{ $option['label'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        @if (auth('students')->check())
                            <div class="d-flex align-items-center gap-3">

                                {{-- 🔔 Chuông thông báo --}}
                                <div class="nav-item dropdown notification-hover position-relative">

                                    <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown"
                                        role="button">

                                        <i class="fas fa-bell"></i>

                                        {{-- Badge số thông báo --}}
                                        @if ($unreadCount > 0)
                                            <span
                                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                                {{ $unreadCount }}
                                            </span>
                                        @endif
                                    </a>

                                    <ul class="dropdown-menu dropdown-menu-end notification-dropdown"
                                        aria-labelledby="notificationDropdown">

                                        <li class="dropdown-header fw-bold">
                                            {{ __('clients/common.notifications') }}
                                        </li>

                                        @forelse($notifications as $notification)
                                            <li>
                                                <a class="dropdown-item notification-item {{ is_null($notification->read_at) ? 'unread' : '' }}"
                                                    href="{{ route('students.notifications.read', $notification->id) }}">

                                                    <div class="notification-content">
                                                        <div class="notification-title">
                                                            {{ notificationText($notification, 'title', __('clients/common.notifications')) }}
                                                        </div>

                                                        <div class="notification-message">
                                                            {{ notificationText($notification, 'message', '') }}
                                                        </div>

                                                        <div class="notification-time">
                                                            {{ $notification->created_at->diffForHumans() }}
                                                        </div>
                                                    </div>

                                                </a>
                                            </li>
                                        @empty
                                            <li class="dropdown-item text-muted small">
                                                {{ __('clients/common.no_notifications') }}
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
                                                href="{{ route('students.account.index',['locale' => app()->getLocale()]) }}">
                                                <i class="fas fa-user-circle"></i>
                                                {{ __('clients/common.my_account') }}
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2 text-danger"
                                                href="#"
                                                onclick="document['form-logout'].submit(); return false;">
                                                <i class="fas fa-sign-out-alt"></i> {{ __('clients/common.logout') }}
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                            </div>
                        @else
                            <button class="btn btn-primary">
                                <a href="{{ route('clients-register',['locale' => app()->getLocale()]) }}" class="text-white"
                                    style="text-decoration: none !important"><i class="fas fa-user"></i>
                                    {{ __('clients/common.register') }}</a>
                            </button>
                            <button class="btn btn-primary">
                                <a href="{{ route('clients-login',['locale' => app()->getLocale()]) }}" class="text-white"
                                    style="text-decoration: none !important"><i class="fas fa-key"></i>
                                    {{ __('clients/common.login') }}</a>
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
            <a class="navbar-brand" href="{{ route('home', ['locale' => app()->getLocale()]) }}">
                <img src="{{ setting('logo') ? asset('storage/' . setting('logo')) : asset('clients/assets/logo.png') }}"
                    alt="Logo" width="20px">
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
                            href="{{ route('home', ['locale' => app()->getLocale()]) }}">
                            <i class="fas fa-home"></i>
                            {{ __('clients/common.home') }}
                        </a>
                    </li>

                    <li class="nav-item dropdown dropdown-hover">
                        <a class="nav-link
        {{ request()->routeIs('courses.*') ? 'active' : '' }}"
                            id="coursesDropdown" role="button">
                            <i class="fas fa-tv"></i>
                            {{ __('clients/common.course_categories') }}
                        </a>

                        <ul class="dropdown-menu">
                            @foreach ($courseCategories as $category)
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item"
                                        href="{{ route('categories.category', [
                                        'locale' => app()->getLocale(),
                                        'slug' => $category->slug_locale]) }}">
                                        {{ $category->name_locale }}
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
                            href="{{ route('coupons.home', ['locale' => app()->getLocale()]) }}">
                            <i class="fas fa-ticket-alt"></i>
                            {{ __('clients/common.coupons') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('coupons.*') ? 'active' : '' }}"
                            href="{{ route('contacts.home', ['locale' => app()->getLocale()]) }}">
                            <i class="fas fa-phone-alt"></i>
                            {{ __('clients/common.contact') }}
                        </a>
                    </li>

                </ul>
            </div>
        </div>
    </nav>

</header>
<form action="{{ route('clients-logout',['locale' => app()->getLocale()]) }}" method="post" name="form-logout">@csrf</form>
