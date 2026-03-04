# Course Store

Nền tảng khóa học online xây dựng bằng Laravel, có:

- client đa ngôn ngữ
- trang quản trị (admin)
- xác thực email
- queue gửi mail
- thông báo trong hệ thống
- CKEditor + Laravel File Manager

Hiện dự án hỗ trợ 5 ngôn ngữ phía client:

- `vi`
- `en`
- `ko`
- `ja`
- `zh`

[English](README.en.md) | [한국어](README.ko.md) | [日本語](README.ja.md) | [中文](README.zh.md)

[Tài liệu 5 ngôn ngữ chi tiết](docs/5-language-guide.md)

## Yêu cầu hệ thống

- PHP `>= 8.x`
- Composer
- Node.js + NPM
- MySQL hoặc MariaDB

## Cài đặt nhanh

### 1. Clone và cài dependency

```bash
git clone <REPO_URL>
cd course-store
composer install
npm install
```

### 2. Tạo file môi trường

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Cấu hình database trong `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Migrate và seed

```bash
php artisan migrate --seed
```

Tài khoản admin mặc định:

- Email: `admin@gmail.com`
- Password: `12345678`

### 5. Tạo storage link

```bash
php artisan storage:link
```

### 6. Build frontend assets

Chạy môi trường dev:

```bash
npm run dev
```

Build production:

```bash
npm run build
```

### 7. Chạy project

```bash
php artisan serve
```

URL mặc định:

- Client: `http://127.0.0.1:8000/vi`
- Admin: `http://127.0.0.1:8000/admin`

## Mail và queue

Dự án có dùng email xác thực, quên mật khẩu, và một số mail hệ thống.

### Cấu hình mail trong `.env`

Ví dụ SMTP:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

Sau khi sửa `.env`, nên clear cache:

```bash
php artisan config:clear
php artisan cache:clear
```

### Chạy queue worker

Nếu mail/notification đang queue:

```bash
php artisan queue:work
```

Trong môi trường production, không nên dùng `sync` cho queue.

## Đa ngôn ngữ

Client dùng locale ở đầu URL:

- `/vi`
- `/en`
- `/ko`
- `/ja`
- `/zh`

Nguyên tắc hiện tại:

- nếu bản dịch của `en/ko/ja/zh` trống, hệ thống fallback về `vi`
- notification mới có thể hiện theo locale hiện tại
- email có thể bám theo locale tại thời điểm gửi

File dịch nằm ở:

- `resources/lang/{locale}`
- `modules/*/resources/lang/{locale}`

Nếu bạn cần hướng dẫn đầy đủ về dữ liệu đa ngôn ngữ, slug, fallback, notification và mail:

[Xem tài liệu chi tiết](docs/5-language-guide.md)

## Cấu hình production khuyến nghị

Trước khi deploy production, nên kiểm tra:

1. Tắt debug

```env
APP_DEBUG=false
```

2. Dùng queue thật (`database` hoặc `redis`)

3. Build assets production

```bash
npm run build
```

4. Cache lại config, route, view

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

5. Đảm bảo worker queue đang chạy

## Lệnh thường dùng

```bash
php artisan serve
php artisan migrate
php artisan db:seed
php artisan queue:work
php artisan test
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Xử lý lỗi thường gặp

### 1. Không gửi được mail

- kiểm tra SMTP trong `.env`
- kiểm tra queue worker có đang chạy không
- clear config cache sau khi sửa `.env`

```bash
php artisan config:clear
php artisan cache:clear
php artisan queue:work
```

### 2. Upload / file manager không hoạt động

Đảm bảo đã tạo storage link:

```bash
php artisan storage:link
```

### 3. CSS / JS không load

Chạy lại build assets:

```bash
npm run dev
```

hoặc:

```bash
npm run build
```

### 4. Lỗi bảng queue / failed jobs

Nếu thiếu bảng queue:

```bash
php artisan queue:table
php artisan migrate
```

## Giao diện demo

Client:

- Home: `docs/images/home.png`
- Course: `docs/images/course.png`
- Course Detail: `docs/images/course_detail.png`
- Coupon: `docs/images/coupon.png`
- Login/Register: `docs/images/login.png`
- Contact: `docs/images/contact.png`
- Checkout: `docs/images/checkout.png`
- Thank-you: `docs/images/thanks.png`
- Order Detail: `docs/images/order_detail.png`
- Lesson: `docs/images/lesson.png`

Admin:

- Admin Home: `docs/images/admin.png`

## Ghi chú

- `README.vi.md` là bản ghi chú tiếng Việt cũ hơn
- `note.md` là tài liệu note nội bộ / phác thảo
- tài liệu nên dùng hiện tại là:
  - [README.md](README.md)
  - [README.en.md](README.en.md)
  - [README.ko.md](README.ko.md)
  - [README.ja.md](README.ja.md)
  - [README.zh.md](README.zh.md)
  - [docs/5-language-guide.md](docs/5-language-guide.md)

## Liên hệ

- Author: BigK
- Email: `khanhbeotixiu9x@gmail.com`
