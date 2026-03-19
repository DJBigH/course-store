<ul class="nav flex-column" style="border: none">
    <li class="nav-item">
        <a href="{{ route('students.account.index', ['locale' => app()->getLocale()]) }}"
            class="nav-link {{ activeMenu('students.account.index') ? 'active' : '' }}">
            <i class="fa-solid fa-gauge"></i>
            {{ __('students::clients/account.menu.dashbroad') }}
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('students.account.profile', ['locale' => app()->getLocale()]) }}"
            class="nav-link {{ activeMenu('students.account.profile') ? 'active' : '' }}">
            <i class="fa-solid fa-user"></i>
            {{ __('students::clients/account.menu.profile') }}
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('students.account.my-courses', ['locale' => app()->getLocale()]) }}"
            class="nav-link {{ activeMenu('students.account.my-courses') ? 'active' : '' }}">
            <i class="fa-solid fa-book-open"></i>
            {{ __('students::clients/account.menu.my_course') }}
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('students.account.my-coupon', ['locale' => app()->getLocale()]) }}"
            class="nav-link {{ activeMenu('students.account.my-coupon') ? 'active' : '' }}">
            <i class="fa-solid fa-ticket-alt"></i>
            {{ __('students::clients/account.menu.coupons') }}
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('students.account.my-order', ['locale' => app()->getLocale()]) }}"
            class="nav-link {{ activeMenu('students.account.my-order') ? 'active' : '' }}">
            <i class="fa-solid fa-receipt"></i>
            {{ __('students::clients/account.menu.order') }}
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('students.account.change-password', ['locale' => app()->getLocale()]) }}"
            class="nav-link {{ activeMenu('students.account.change-password') ? 'active' : '' }}">
            <i class="fa-solid fa-lock"></i>
            {{ __('students::clients/account.menu.change_password') }}
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('students.account.activity-history', ['locale' => app()->getLocale()]) }}"
            class="nav-link {{ activeMenu('students.account.activity-history') ? 'active' : '' }}">
            <i class="fa-solid fa-clock-rotate-left"></i>
            {{ __('students::clients/account.menu.activity_history') }}
        </a>
    </li>
    <li class="nav-item">
        <form action="{{ route('clients-logout', ['locale' => app()->getLocale()]) }}" method="POST" class="d-inline">
            @csrf
            <a href="#" class="nav-link text-danger js-logout"
                data-confirm="{{ __('students::clients/account.logout.confirm_logout') }}">
                <i class="bi bi-box-arrow-right me-1"></i>
                {{ __('students::clients/account.menu.logout') }}
            </a>
        </form>
    </li>
</ul>
