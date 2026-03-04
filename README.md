# Course Store (Tai lieu chinh)

Nen tang khoa hoc truc tuyen xay dung bang Laravel, gom:

- giao dien client da ngon ngu
- trang quan tri (admin)
- dang ky, dang nhap, quen mat khau
- xac thuc email
- queue gui mail
- thong bao trong he thong
- CKEditor + Laravel File Manager

Hien tai du an ho tro 5 ngon ngu phia client:

- `vi`
- `en`
- `ko`
- `ja`
- `zh`

Ban chinh (Tieng Viet) | [English](README.en.md) | [한국어](README.ko.md) | [日本語](README.ja.md) | [中文](README.zh.md)

[Xem tai lieu van hanh 5 ngon ngu](docs/5-language-guide.md)

## Tong quan

Du an su dung `locale` ngay tren URL de phan tach ngon ngu, vi du:

- `http://127.0.0.1:8000/vi`
- `http://127.0.0.1:8000/en`
- `http://127.0.0.1:8000/ko`
- `http://127.0.0.1:8000/ja`
- `http://127.0.0.1:8000/zh`

Neu cac truong da ngon ngu cua `en/ko/ja/zh` chua co du lieu, he thong se uu tien fallback ve tieng Viet de tranh loi `404` va tranh hien thi rong.

## Yeu cau he thong

- PHP `>= 8.x`
- Composer
- Node.js va NPM
- MySQL hoac MariaDB

## Cai dat nhanh

### 1. Tai ma nguon va cai dependency

```bash
git clone <REPO_URL>
cd course-store
composer install
npm install
```

### 2. Tao file moi truong

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Cau hinh co so du lieu trong `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Chay migration va seed du lieu

```bash
php artisan migrate --seed
```

Tai khoan admin mac dinh:

- Email: `admin@gmail.com`
- Mat khau: `12345678`

### 5. Tao lien ket `storage`

```bash
php artisan storage:link
```

### 6. Build tai nguyen giao dien

Chay moi truong phat trien:

```bash
npm run dev
```

Build cho production:

```bash
npm run build
```

### 7. Khoi dong du an

```bash
php artisan serve
```

Duong dan mac dinh:

- Client: `http://127.0.0.1:8000/vi`
- Admin: `http://127.0.0.1:8000/admin`

## Mail va queue

Du an co su dung email cho cac luong:

- xac thuc tai khoan
- quen mat khau
- thong bao he thong lien quan den nguoi dung

### Cau hinh mail trong `.env`

Vi du voi SMTP:

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

Sau khi sua `.env`, nen xoa cache cau hinh:

```bash
php artisan config:clear
php artisan cache:clear
```

### Chay queue worker

Neu mail hoac notification dang duoc dua vao queue:

```bash
php artisan queue:work
```

Khuyen nghi khong dung `sync` tren production.

## Da ngon ngu

He thong dang ho tro giao dien va dich chuoi cho 5 ngon ngu:

- `vi`
- `en`
- `ko`
- `ja`
- `zh`

Nguyen tac hien tai:

- du lieu dong se uu tien ngon ngu hien tai
- neu ngon ngu hien tai chua co du lieu, he thong fallback ve `vi`
- notification moi co the hien theo locale hien tai
- email co the bam theo locale tai thoi diem gui

Vi tri file dich:

- `resources/lang/{locale}`
- `modules/*/resources/lang/{locale}`

Neu ban can tai lieu day du ve slug, fallback, du lieu da ngon ngu, notification va email:

- [docs/5-language-guide.md](docs/5-language-guide.md)

## Khuyen nghi truoc khi dua len production

Truoc khi deploy, nen kiem tra toi thieu cac muc sau:

1. Tat che do debug

```env
APP_DEBUG=false
```

2. Dung queue that (`database` hoac `redis`)

3. Build tai nguyen giao dien cho production

```bash
npm run build
```

4. Cache lai cau hinh, route va view

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

5. Dam bao queue worker dang chay on dinh

6. Kiem tra mail gui duoc tren moi truong that

## Lenh thuong dung

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

## Xu ly loi thuong gap

### 1. Khong gui duoc mail

- kiem tra cau hinh SMTP trong `.env`
- kiem tra queue worker co dang chay khong
- xoa cache cau hinh sau khi sua `.env`

```bash
php artisan config:clear
php artisan cache:clear
php artisan queue:work
```

### 2. Upload hoac file manager khong hoat dong

Dam bao da tao lien ket `storage`:

```bash
php artisan storage:link
```

### 3. CSS hoac JS khong load

Build lai tai nguyen:

```bash
npm run dev
```

hoac:

```bash
npm run build
```

### 4. Loi bang queue hoac `failed_jobs`

Neu thieu bang queue:

```bash
php artisan queue:table
php artisan migrate
```

## Hinh anh demo

Giao dien client:

- Trang chu: `docs/images/home.png`
- Danh sach khoa hoc: `docs/images/course.png`
- Chi tiet khoa hoc: `docs/images/course_detail.png`
- Coupon: `docs/images/coupon.png`
- Dang nhap / Dang ky: `docs/images/login.png`
- Lien he: `docs/images/contact.png`
- Thanh toan: `docs/images/checkout.png`
- Cam on: `docs/images/thanks.png`
- Chi tiet don hang: `docs/images/order_detail.png`
- Bai hoc: `docs/images/lesson.png`

Giao dien admin:

- Trang tong quan: `docs/images/admin.png`

## Lien ket tai lieu

- [README.md](README.md)
- [README.en.md](README.en.md)
- [README.ko.md](README.ko.md)
- [README.ja.md](README.ja.md)
- [README.zh.md](README.zh.md)
- [docs/5-language-guide.md](docs/5-language-guide.md)
- [note.md](note.md)

## Lien he

- Tac gia: BigK
- Email: `khanhbeotixiu9x@gmail.com`
