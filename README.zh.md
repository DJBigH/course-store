# Course Store

这是一个基于 Laravel 的在线课程平台。

主要功能：

- 多语言客户端
- 管理后台
- 邮件验证
- 邮件队列
- 站内通知
- CKEditor + Laravel File Manager

支持语言：

- `vi`
- `en`
- `ko`
- `ja`
- `zh`

[README](README.md) | [Tiếng Việt](README.vi.md) | [English](README.en.md) | [한국어](README.ko.md) | [日本語](README.ja.md) | 中文

[5语言详细指南](docs/5-language-guide.md)

## 系统要求

- PHP `>= 8.x`
- Composer
- Node.js + NPM
- MySQL 或 MariaDB

## 快速开始

### 1. 克隆并安装依赖

```bash
git clone <REPO_URL>
cd course-store
composer install
npm install
```

### 2. 创建环境文件

```bash
cp .env.example .env
php artisan key:generate
```

### 3. 在 `.env` 中配置数据库

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. 执行迁移和种子

```bash
php artisan migrate --seed
```

默认管理员账号：

- Email: `admin@gmail.com`
- Password: `12345678`

### 5. 创建 storage link

```bash
php artisan storage:link
```

### 6. 构建前端资源

开发：

```bash
npm run dev
```

生产：

```bash
npm run build
```

### 7. 启动项目

```bash
php artisan serve
```

默认地址：

- Client: `http://127.0.0.1:8000/vi`
- Admin: `http://127.0.0.1:8000/admin`

## 邮件与队列

在 `.env` 中配置 SMTP：

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

修改后清理缓存：

```bash
php artisan config:clear
php artisan cache:clear
```

启动队列：

```bash
php artisan queue:work
```

## 多语言说明

- 客户端路由使用 `/vi`, `/en`, `/ko`, `/ja`, `/zh`
- 当某个翻译字段为空时，系统会回退到越南语
- 新通知可以按当前 locale 显示
- 邮件可以跟随发送时的 locale

## 生产环境建议

部署前建议：

1. 设置 `APP_DEBUG=false`
2. 使用真实队列驱动（`database` 或 `redis`）
3. 执行 `npm run build`
4. 缓存 config / route / view

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 常用命令

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
