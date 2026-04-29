<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Đang Bảo Trì | Course Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;500;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at top left, #1e1b4b, #0f172a);
            color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 50px 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .icon {
            font-size: 64px;
            margin-bottom: 24px;
            display: inline-block;
            animation: pulse 2s infinite;
        }
        h1 { font-size: 32px; font-weight: 700; margin-bottom: 16px; background: linear-gradient(to right, #818cf8, #c084fc); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        p { font-size: 18px; line-height: 1.6; color: #cbd5e1; margin-bottom: 32px; }
        .badge {
            display: inline-block;
            background: rgba(129, 140, 248, 0.2);
            color: #a5b4fc;
            padding: 8px 20px;
            border-radius: 9999px;
            font-size: 14px;
            font-weight: 500;
            border: 1px solid rgba(129, 140, 248, 0.3);
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="container">
        <span class="icon">🛠️</span>
        <h1>Hệ Thống Đang Bảo Trì</h1>
        <p>{{ $message }}</p>
        <span class="badge">Vui lòng quay lại sau ít phút</span>
    </div>
</body>
</html>
