<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Mã xác thực đăng nhập quản trị</title>
</head>

<body style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.6;">
    <div style="max-width: 560px; margin: 0 auto; padding: 24px;">
        <h2 style="margin-bottom: 12px;">Xác thực đăng nhập quản trị</h2>
        <p>Xin chào {{ $user->name }},</p>
        <p>Bạn vừa yêu cầu đăng nhập vào khu vực quản trị. Hãy dùng mã xác thực bên dưới để tiếp tục:</p>

        <div
            style="margin: 24px 0; padding: 18px 24px; border-radius: 16px; background: #eff6ff; border: 1px solid #bfdbfe; text-align: center;">
            <div style="font-size: 30px; font-weight: 700; letter-spacing: 8px; color: #1d4ed8;">
                {{ $code }}
            </div>
        </div>

        <p>Mã này sẽ hết hạn sau {{ $expiresInMinutes }} phút.</p>
        <p>Nếu không phải bạn thực hiện, vui lòng đổi mật khẩu quản trị ngay và kiểm tra lại các phiên đăng nhập.</p>
    </div>
</body>

</html>
