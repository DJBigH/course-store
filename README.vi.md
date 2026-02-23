# Nền tảng khóa học Laravel

[English](README.md)

# Laravel Course Platform (Multi-language)

Dự án web bán / quản lý khóa học xây bằng **Laravel** (client & admin), hỗ trợ **đa ngôn ngữ (VI/EN)**, có **đăng nhập/đăng ký + kích hoạt email**, **checkout**, **coupon**, và trang quản trị.

---

## 1) Yêu cầu môi trường

- PHP >= 8.x
- Composer
- Node.js + NPM
- MySQL / MariaDB
- (Khuyến nghị) Redis hoặc database driver cho Queue

---

## 2) Cài đặt sau khi clone

### Bước 1: Clone & cài package PHP

```bash
git clone <your-repo-url>
cd <project-folder>
composer install
```

Bước 2: Tạo file môi trường .env
cp .env.example .env
php artisan key:generate
Bước 3: Cấu hình DB trong .env
Ví dụ:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_project
DB_USERNAME=root
DB_PASSWORD=

Bước 4: Migrate + Seeder (tạo dữ liệu mẫu)
php artisan migrate --seed
✅ Tài khoản admin sau khi chạy seeder:
Email: admin@gmail.com
Password: 12345678

3. Cài đặt Frontend (NPM + Toastify)
   Dự án có dùng toastify-js:
   npm install
   npm install --save toastify-js
   npm run dev
   Nếu deploy production:
   npm run build

4. Chạy server
   php artisan serve
   Mặc định:
   Client:
   VI: http://127.0.0.1:8000/vi
   EN: http://127.0.0.1:8000/en
   Admin: http://127.0.0.1:8000/admin/

5. Mail kích hoạt tài khoản học viên (IMPORTANT)
   Học viên đăng ký sẽ cần email verification → bạn phải cấu hình SMTP trong .env.
   Ví dụ Gmail SMTP:
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=your_email@gmail.com
   MAIL_PASSWORD=your_app_password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=your_email@gmail.com
   MAIL_FROM_NAME="${APP_NAME}"
   Kiểm tra nhanh:
   php artisan config:clear
   php artisan cache:clear

6. Queue (hàng đợi) – gửi mail / job nền
   Dự án có sử dụng Queue (ví dụ: gửi mail kích hoạt).
   Cấu hình driver queue trong .env
   Nếu dùng database (dễ setup nhất):
   QUEUE_CONNECTION=database
   Tạo bảng queue:
   php artisan queue:table
   php artisan migrate
   Chạy worker:
   php artisan queue:work
   Trong môi trường dev, có thể dùng:
   php artisan queue:listen

7. CKEditor + Laravel File Manager
   Dự án sử dụng:
   CKEditor
   Laravel File Manager để upload/chọn ảnh trong editor
   Publish / setup (nếu dự án yêu cầu)
   Tùy package bạn dùng, thường sẽ có các lệnh kiểu:
   php artisan vendor:publish
   php artisan storage:link
   ✅ Lưu ý chung:
   đảm bảo đã chạy php artisan storage:link
   phân quyền thư mục storage/ và bootstrap/cache/ ghi được
   nếu dùng file manager route riêng, đảm bảo middleware/auth đúng cho admin

8. Đa ngôn ngữ (VI/EN)
   Client chạy theo prefix:
   /vi
   /en
   Nếu bạn gặp lỗi thiếu text:
   kiểm tra resources/lang/vi/_.php và resources/lang/en/_.php
   đảm bảo key tồn tại ở cả 2 ngôn ngữ

9. Demo giao diện
   Bạn thêm ảnh demo vào thư mục: docs/images/
   và thay các link bên dưới theo đúng tên ảnh của bạn.
   Client pages
   Home
   Course List
   Course Detail
   Coupon
   Login / Register
   Contact
   Checkout
   Thank you
   Multi-language
   Admin pages
   Admin Dashboard / Manager

10. Troubleshooting
11. Lỗi thiếu bảng failed_jobs
    Nếu dùng queue database mà thiếu bảng:
    php artisan queue:failed-table
    php artisan migrate

12. Không gửi mail được
    kiểm tra đúng SMTP .env
    chạy queue worker nếu mail đang gửi qua queue:
    php artisan queue:work

13. Không load được asset
    npm run dev
    php artisan config:clear
    php artisan cache:clear

14. Tác giả
    Author: <BigK>
    Contact: <khanhbeotixiu9x@gmail.com>

---
