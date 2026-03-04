# Course Store

Laravel 기반 온라인 강의 플랫폼입니다.

주요 기능:

- 다국어 클라이언트
- 관리자 페이지
- 이메일 인증
- 메일 큐 처리
- 사이트 내 알림
- CKEditor + Laravel File Manager

지원 언어:

- `vi`
- `en`
- `ko`
- `ja`
- `zh`

[README](README.md) | [Tiếng Việt](README.vi.md) | [English](README.en.md) | 한국어 | [日本語](README.ja.md) | [中文](README.zh.md)

[5개 언어 상세 가이드](docs/5-language-guide.md)

## 시스템 요구사항

- PHP `>= 8.x`
- Composer
- Node.js + NPM
- MySQL 또는 MariaDB

## 빠른 시작

### 1. 프로젝트 클론 및 의존성 설치

```bash
git clone <REPO_URL>
cd course-store
composer install
npm install
```

### 2. 환경 파일 생성

```bash
cp .env.example .env
php artisan key:generate
```

### 3. `.env` 데이터베이스 설정

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. 마이그레이션 및 시더 실행

```bash
php artisan migrate --seed
```

기본 관리자 계정:

- Email: `admin@gmail.com`
- Password: `12345678`

### 5. storage link 생성

```bash
php artisan storage:link
```

### 6. 프론트엔드 빌드

개발:

```bash
npm run dev
```

배포:

```bash
npm run build
```

### 7. 프로젝트 실행

```bash
php artisan serve
```

기본 URL:

- Client: `http://127.0.0.1:8000/vi`
- Admin: `http://127.0.0.1:8000/admin`

## 메일 및 큐

`.env`에 SMTP 설정:

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

설정 변경 후 캐시 정리:

```bash
php artisan config:clear
php artisan cache:clear
```

큐 워커 실행:

```bash
php artisan queue:work
```

## 다국어 동작

- 클라이언트 라우트는 `/vi`, `/en`, `/ko`, `/ja`, `/zh` 접두사를 사용합니다
- 번역 필드가 비어 있으면 베트남어(`vi`)로 fallback 됩니다
- 새 알림은 현재 locale에 맞춰 표시할 수 있습니다
- 메일은 발송 시점 locale을 따를 수 있습니다

## 운영 배포 권장

배포 전:

1. `APP_DEBUG=false`
2. 실제 queue 드라이버 사용 (`database` 또는 `redis`)
3. `npm run build`
4. config / route / view 캐시

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 자주 쓰는 명령어

```bash
php artisan serve
php artisan migrate
php artisan db:seed
php artisan queue:work
php artisan test
```

## Docs Links

- [README.md](README.md)
- [README.vi.md](README.vi.md)
- [README.en.md](README.en.md)
- [README.ko.md](README.ko.md)
- [README.ja.md](README.ja.md)
- [README.zh.md](README.zh.md)
- [docs/5-language-guide.md](docs/5-language-guide.md)
- [note.md](note.md)

## Contact

- Author: BigK
- Email: `khanhbeotixiu9x@gmail.com`
