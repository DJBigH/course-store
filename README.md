# Laravel Course Platform (Multi-language)

[Tiếng Việt](README.vi.md)

A Laravel-based course platform with multi-language client (VI/EN) and an admin panel.
Includes Email Verification, Queue jobs, CKEditor, Laravel File Manager, and Toastify.

---

## Requirements

- PHP >= 8.x
- Composer
- Node.js + NPM
- MySQL/MariaDB

---

## Setup (after cloning)

### 1) Install PHP dependencies

```bash
composer install
```

2. Create .env
   cp .env.example .env
   php artisan key:generate

3. Configure database in .env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=YOUR_USER_DATABASE
   DB_USERNAME=YOUR_USER_NAME
   DB_PASSWORD=YOUR_USER_PASSWORD

4. Migrate & seed
   php artisan migrate --seed
   ✅ Default admin (created by seeder)
   Email: admin@gmail.com
   Password: 12345678

5. Frontend build (NPM)
   npm install
   npm run dev
   For production:
   npm run build
   Run the app
   php artisan serve
   URLs:
   Client:
   VI: http://127.0.0.1:8000/vi
   EN: http://127.0.0.1:8000/en
   Admin: http://127.0.0.1:8000/admin/

6. Email Verification (IMPORTANT)
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
   Clear cache:
   php artisan config:clear
   php artisan cache:clear

7. Queue
   This project uses Laravel Queue (e.g., verification email jobs).
   php artisan queue:work (or php artisan queue:listen)

8. CKEditor + Laravel File Manager
   Make sure storage link is created:
   If your file manager package requires publishing assets/config, run vendor publish commands (depends on the package you use).

9. Multi-language (VI/EN)
   Client uses language prefix:
   /vi
   /en
   If some translations are missing, check:
   resources/lang/vi/_.php
   resources/lang/en/_.php

10. UI Demo Screenshots
    Put screenshots in: docs/images/ then update file names below.
    Client:
    Home: docs/images/home.png
    Course: docs/images/course.png
    Course Detail: docs/images/course_detail.png
    Coupon: docs/images/coupon.png
    Login/Register: docs/images/login_register.png
    Contact: docs/images/contact.png
    Checkout: docs/images/checkout.png
    Thank you: docs/images/thankyou.png
    Multi-language: docs/images/multilanguage.png
    Admin:
    Admin pages: docs/images/admin.png

11. Troubleshooting
    Missing failed_jobs table
    Mail not sending
    Check SMTP in .env
    If mails are queued, run:

12. Author

Author: <BigK>

Contact: <khanhbeotixiu9x@gmail.com>