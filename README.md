# Laravel Course Platform (Multi-language VI/EN)

A Laravel-based course platform with multi-language client (VI/EN) and an admin panel.  
Includes **Email Verification**, **Queue jobs**, **CKEditor**, **Laravel File Manager**, and **Toastify**.

[Tiếng Việt](README.vi.md)

---

## Requirements

- PHP >= 8.x
- Composer
- Node.js + NPM
- MySQL/MariaDB

---

## Quick Start (After Clone)

### 1) Clone project & install dependencies

```bash
git clone <https://github.com/DJBigH/course-store.git> or git clone <git@github.com:DJBigH/course-store.git> for SSH
cd <YOUR_PROJECT_FOLDER>

composer install
npm install

2) Create .env & generate key
```bash
cp .env.example .env
php artisan key:generate

3) Configure Database in .env
Edit .env:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=YOUR_DATABASE
DB_USERNAME=YOUR_USERNAME
DB_PASSWORD=YOUR_PASSWORD

4) Migrate & Seed
```bash
php artisan migrate --seed

✅ Default admin (created by seeder)

Email: admin@gmail.com

Password: 12345678

5) Storage link (important for uploads / file manager)
```bash
php artisan storage:link

6) Build frontend assets
```bash
Development:

npm run dev

Production:

npm run build
7) Run the project
```bash
php artisan serve
```bash
URLs:

Client VI: http://127.0.0.1:8000/vi

Client EN: http://127.0.0.1:8000/en

Admin: http://127.0.0.1:8000/admin/

Email Verification (IMPORTANT)

Students register and must verify email. Configure SMTP in .env.

Example (Gmail SMTP):

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"

Clear config cache after editing .env:

php artisan config:clear
php artisan cache:clear
Queue (IMPORTANT)

This project uses Laravel Queue (e.g. email verification jobs).

Run queue worker:

php artisan queue:work

Tip: Open another terminal tab and keep queue:work running while testing email verification.

CKEditor + Laravel File Manager

Make sure you ran:

php artisan storage:link

If your file manager package requires publish config/assets, run (depends on your package):

php artisan vendor:publish

Nếu bạn dùng gói cụ thể (UniSharp/Laravel Filemanager...), mình có thể viết đúng lệnh publish/config cho chuẩn package.

Multi-language (VI/EN)

Client uses language prefix:

/vi

/en

Translation files:

resources/lang/vi/*.php

resources/lang/en/*.php

If missing translations, check your language keys in these folders.

UI Demo Screenshots

Client:

Home: docs/images/home.png

Course: docs/images/course.png

Course: docs/images/course_detail.png

Coupon: docs/images/coupon.png

Login/Register: docs/images/login_register.png

Contact: docs/images/contact.png

Checkout: docs/images/checkout.png

Thank-you: docs/images/thankyou.png

Order-detail: docs/images/order_detail.png

Lesson: docs/images/lesson.png

Admin:

Admin pages: docs/images/dashbroad.png

Troubleshooting
1) Missing failed_jobs table

If you see error about failed_jobs table:
```bash
php artisan queue:table
php artisan migrate
2) Mail not sending
```bash
Check SMTP credentials in .env

If mails are queued, make sure queue worker is running:

php artisan queue:work
3) Assets not loading
```bash
Run:

npm run dev

(or npm run build for production)

Author

Author: BigK
Contact: <khanhbeotixiu9x@gmail.com>
