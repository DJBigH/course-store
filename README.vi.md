# Nền tảng khóa học Laravel (Đa ngôn ngữ VI/EN)
[English](README.md)
Đây là nền tảng khóa học được xây dựng bằng **Laravel**, hỗ trợ:

- Client đa ngôn ngữ (Tiếng Việt / English)
- Trang quản trị (Admin Panel)
- Xác thực email (Email Verification)
- Queue xử lý gửi mail
- CKEditor
- Laravel File Manager
- Toastify

---

# Yêu cầu hệ thống

- PHP >= 8.x
- Composer
- Node.js + NPM
- MySQL / MariaDB

---

# Hướng dẫn cài đặt sau khi clone

## 1) Clone project và cài đặt dependency

```bash
git clone <LINK_REPO_CUA_BAN>
cd <TEN_THU_MUC_PROJECT>

composer install
npm install
2) Tạo file .env
cp .env.example .env
php artisan key:generate
3) Cấu hình Database

Mở file .env và chỉnh:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=TEN_DATABASE
DB_USERNAME=TEN_USER
DB_PASSWORD=MAT_KHAU
4) Migrate & Seed dữ liệu
php artisan migrate --seed
✅ Tài khoản Admin mặc định (được tạo bởi Seeder)

Email: admin@gmail.com

Password: 12345678

5) Tạo storage link (bắt buộc cho upload & file manager)
php artisan storage:link
6) Build Frontend

Chạy môi trường development:

npm run dev

Chạy production:

npm run build
7) Chạy project
php artisan serve
Truy cập:

Client Tiếng Việt:
http://127.0.0.1:8000/vi

Client English:
http://127.0.0.1:8000/en

Admin Panel:
http://127.0.0.1:8000/admin/

Xác thực Email (QUAN TRỌNG)

Học viên đăng ký bắt buộc phải xác thực email.

Bạn cần cấu hình SMTP trong .env.

Ví dụ dùng Gmail:

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"

Sau khi chỉnh sửa .env, chạy:

php artisan config:clear
php artisan cache:clear
Queue (BẮT BUỘC nếu dùng gửi mail)

Project sử dụng Laravel Queue để gửi email xác thực.

Chạy worker:

php artisan queue:work

⚠️ Lưu ý: Nếu không chạy queue, email xác thực sẽ không gửi.

CKEditor & Laravel File Manager

Đảm bảo đã chạy:

php artisan storage:link

Nếu package file manager yêu cầu publish config:

php artisan vendor:publish

(Tùy package bạn đang sử dụng)

Cấu hình đa ngôn ngữ (VI / EN)

Client sử dụng prefix ngôn ngữ:

/vi

/en

File ngôn ngữ nằm tại:

resources/lang/vi/
resources/lang/en/

Nếu thiếu bản dịch, kiểm tra các file:

resources/lang/vi/*.php
resources/lang/en/*.php
Cấu trúc chính

Client: giao diện người dùng

Admin: quản lý khóa học, đơn hàng, học viên

Orders: quản lý đơn hàng

Students: quản lý học viên

Dashboard: thống kê

Xử lý lỗi thường gặp
1) Lỗi thiếu bảng failed_jobs

Chạy:

php artisan queue:table
php artisan migrate
2) Không gửi được mail

Kiểm tra lại SMTP trong .env

Kiểm tra đã chạy queue chưa:

php artisan queue:work
3) Không load được CSS/JS

Chạy lại:

npm run dev

hoặc

npm run build
Demo giao diện

Ảnh demo đặt tại:

docs/images/

Ví dụ:

home.png

course.png

course_detail.png

login_register.png

checkout.png

admin.png

Tác giả

Author: BigK
Contact: YOUR_EMAIL


---

Nếu bạn muốn mình làm thêm:

- README dạng chuyên nghiệp hơn (badge, version, license)
- Thêm mục Deployment (Server, VPS, Nginx, Supervisor cho queue)
- Hoặc viết luôn hướng dẫn deploy production chuẩn

Nói mình biết, mình làm cho bạn full chuẩn GitHub luôn.
```
