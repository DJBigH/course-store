<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Truy cập bị chặn - Security Firewall</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f1f5f9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', system-ui, sans-serif;
        }
        .firewall-card {
            max-width: 500px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            padding: 40px;
            text-align: center;
            border-top: 6px solid #dc2626;
        }
        .shield-icon {
            font-size: 60px;
            color: #dc2626;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="firewall-card">
        <i class="fa-solid fa-shield-halved shield-icon"></i>
        <h3 class="fw-bold text-dark mb-2">Truy cập bị từ chối!</h3>
        <p class="text-secondary mb-4">Địa chỉ IP của bạn đã bị tường lửa hệ thống đưa vào danh sách đen do phát hiện hành vi bất thường.</p>
        
        <div class="bg-light p-3 rounded-4 mb-4 border">
            <small class="text-muted d-block text-uppercase fw-bold mb-1">Your Blocked IP</small>
            <code class="fs-5 fw-bold text-danger">{{ $ip }}</code>
        </div>

        <p class="small text-muted mb-0">Nếu bạn cho rằng đây là một sự nhầm lẫn, vui lòng liên hệ quản trị viên.</p>
    </div>
</body>
</html>
