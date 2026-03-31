<p>Xin chào {{ $application->full_name }},</p>
<p>Tin vui nè: hồ sơ đăng ký giảng viên của bạn đã được <strong>duyệt</strong>.</p>
@if ($plainPassword)
    <p>Tụi mình đã tạo tài khoản học viên/giảng viên cho email này để bạn vào hệ thống ngay:</p>
    <p>
        Email đăng nhập: <strong>{{ $application->email }}</strong><br>
        Mật khẩu tạm thời: <strong>{{ $plainPassword }}</strong>
    </p>
    <p>Vui lòng đăng nhập và đổi mật khẩu ngay sau khi vào hệ thống. Mật khẩu random này ổn để bắt đầu, nhưng đừng để nó sống lâu quá.</p>
@else
    <p>Tài khoản hiện tại của bạn đã được bật quyền giảng viên. Bạn có thể đăng nhập và vào kênh giảng viên để bắt đầu.</p>
@endif
@if (!empty($application->admin_note))
    <p>Ghi chú từ admin: {{ $application->admin_note }}</p>
@endif
<p>Chúc mừng bạn đã chính thức bước vào kênh giảng viên.</p>
<p>Trân trọng,<br>BigK Udemy</p>
