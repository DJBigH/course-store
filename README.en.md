# Course Store

Laravel-based online course platform with:

- multilingual client
- admin panel
- email verification
- queued mail
- in-app notifications
- CKEditor + Laravel File Manager

Supported client locales:

- `vi`
- `en`
- `ko`
- `ja`
- `zh`

[Default README](README.md) | English | [한국어](README.ko.md) | [日本語](README.ja.md) | [中文](README.zh.md)

[Detailed 5-language guide](docs/5-language-guide.md)

## Requirements

- PHP `>= 8.x`
- Composer
- Node.js + NPM
- MySQL or MariaDB

## Quick Start

### 1. Clone and install dependencies

```bash
git clone <REPO_URL>
cd course-store
composer install
npm install
```

### 2. Create environment file

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configure database in `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Run migrations and seeders

```bash
php artisan migrate --seed
```

Default admin account:

- Email: `admin@gmail.com`
- Password: `12345678`

### 5. Create storage link

```bash
php artisan storage:link
```

### 6. Build frontend assets

Development:

```bash
npm run dev
```

Production:

```bash
npm run build
```

### 7. Start the app

```bash
php artisan serve
```

Default URLs:

- Client: `http://127.0.0.1:8000/vi`
- Admin: `http://127.0.0.1:8000/admin`

## Mail and Queue

Configure SMTP in `.env`:

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

Clear cache after editing `.env`:

```bash
php artisan config:clear
php artisan cache:clear
```

Run queue worker:

```bash
php artisan queue:work
```

## Multilingual Notes

- Client routes use locale prefixes: `/vi`, `/en`, `/ko`, `/ja`, `/zh`
- If a translated field is empty, the system falls back to Vietnamese
- New notifications can render in the current locale
- Mail can follow the locale at send time

## Production Notes

Before deploying:

1. Set `APP_DEBUG=false`
2. Use a real queue driver (`database` or `redis`)
3. Build assets with `npm run build`
4. Cache config, routes, and views

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Common Commands

```bash
php artisan serve
php artisan migrate
php artisan db:seed
php artisan queue:work
php artisan test
```

## Docs Links

- [README.md](README.md)
- [README.en.md](README.en.md)
- [README.ko.md](README.ko.md)
- [README.ja.md](README.ja.md)
- [README.zh.md](README.zh.md)
- [docs/5-language-guide.md](docs/5-language-guide.md)
- [note.md](note.md)

## Contact

- Author: BigK
- Email: `khanhbeotixiu9x@gmail.com`
