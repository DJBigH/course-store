# Course Store

Laravel ベースのオンライン学習プラットフォームです。

主な機能:

- 多言語クライアント
- 管理画面
- メール認証
- メールキュー
- サイト内通知
- CKEditor + Laravel File Manager

対応言語:

- `vi`
- `en`
- `ko`
- `ja`
- `zh`

[Tiếng Việt](README.md) | [English](README.en.md) | [한국어](README.ko.md) | [中文](README.zh.md)

[5言語ガイド](docs/5-language-guide.md)

## 必要環境

- PHP `>= 8.x`
- Composer
- Node.js + NPM
- MySQL または MariaDB

## クイックスタート

### 1. クローンと依存関係のインストール

```bash
git clone <REPO_URL>
cd course-store
composer install
npm install
```

### 2. 環境ファイルの作成

```bash
cp .env.example .env
php artisan key:generate
```

### 3. `.env` でデータベース設定

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. マイグレーションとシード

```bash
php artisan migrate --seed
```

初期管理者アカウント:

- Email: `admin@gmail.com`
- Password: `12345678`

### 5. storage link 作成

```bash
php artisan storage:link
```

### 6. フロントエンドビルド

開発:

```bash
npm run dev
```

本番:

```bash
npm run build
```

### 7. 起動

```bash
php artisan serve
```

デフォルト URL:

- Client: `http://127.0.0.1:8000/vi`
- Admin: `http://127.0.0.1:8000/admin`

## メールとキュー

`.env` に SMTP を設定:

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

変更後はキャッシュをクリア:

```bash
php artisan config:clear
php artisan cache:clear
```

キューワーカー:

```bash
php artisan queue:work
```

## 多言語動作

- クライアントルートは `/vi`, `/en`, `/ko`, `/ja`, `/zh`
- 翻訳フィールドが空の場合はベトナム語にフォールバック
- 新しい通知は現在の locale に合わせて表示可能
- メールは送信時の locale を保持可能

## 本番デプロイ前の推奨

1. `APP_DEBUG=false`
2. 実運用 queue ドライバを使用 (`database` または `redis`)
3. `npm run build`
4. config / route / view をキャッシュ

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## よく使うコマンド

```bash
php artisan serve
php artisan migrate
php artisan db:seed
php artisan queue:work
php artisan test
```

## ドキュメント

- [README.md](README.md)
- [README.en.md](README.en.md)
- [README.ko.md](README.ko.md)
- [README.ja.md](README.ja.md)
- [README.zh.md](README.zh.md)
- [docs/5-language-guide.md](docs/5-language-guide.md)
