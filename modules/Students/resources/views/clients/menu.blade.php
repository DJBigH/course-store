<ul class="nav flex-column" style="border: none">
    <li class="nav-item">
        <a href="{{ route('students.account.index') }}"
            class="nav-link {{ activeMenu('students.account.index') ? 'active' : '' }}">
            <i class="fa-solid fa-gauge"></i>
            Tổng quan
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('students.account.profile') }}"
            class="nav-link {{ activeMenu('students.account.profile') ? 'active' : '' }}">
            <i class="fa-solid fa-user"></i>
            Thông tin cá nhân
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('students.account.my-courses') }}"
            class="nav-link {{ activeMenu('students.account.my-courses') ? 'active' : '' }}">
            <i class="fa-solid fa-book-open"></i>
            Khóa học của tôi
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('students.account.my-coupon') }}"
            class="nav-link {{ activeMenu('students.account.my-coupon') ? 'active' : '' }}">
            <i class="fa-solid fa-ticket-alt"></i>
            Mã giảm giá
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('students.account.my-order') }}"
            class="nav-link {{ activeMenu('students.account.my-order') ? 'active' : '' }}">
            <i class="fa-solid fa-receipt"></i>
            Đơn hàng
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('students.account.change-password') }}"
            class="nav-link {{ activeMenu('students.account.change-password') ? 'active' : '' }}">
            <i class="fa-solid fa-lock"></i>
            Đổi mật khẩu
        </a>
    </li>
    <li class="nav-item">
        <form action="{{ route('clients-logout') }}" method="POST" class="d-inline">
            @csrf
            <a href="#" class="nav-link text-danger"
                onclick="if(confirm('Bạn có chắc chắn muốn đăng xuất?')) this.closest('form').submit(); return false;">
                <i class="bi bi-box-arrow-right me-1"></i> Đăng xuất
            </a>
        </form>

    </li>
</ul>
