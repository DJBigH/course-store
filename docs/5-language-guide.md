# Hướng Dẫn Sử Dụng Dự Án Với 5 Ngôn Ngữ

Tài liệu này mô tả cách dự án đang hoạt động với 5 ngôn ngữ:

- `vi` - Tiếng Việt
- `en` - English
- `ko` - 한국어
- `ja` - 日本語
- `zh` - 中文

## 1. Cách route đa ngôn ngữ hoạt động

Phần client của website đang chạy theo locale ở đầu URL:

- `/vi/...`
- `/en/...`
- `/ko/...`
- `/ja/...`
- `/zh/...`

Ví dụ:

- Trang chủ: `/vi`, `/en`, `/ko`, `/ja`, `/zh`
- Chi tiết khóa học: `/{locale}/khoa-hoc/{slug}`

Middleware `SetLocale` sẽ đọc locale từ URL và set `app()->getLocale()` tương ứng cho toàn bộ request.

## 2. Ngôn ngữ mặc định và fallback

Ngôn ngữ mặc định của app là:

- `vi`

Nguyên tắc fallback hiện tại:

- Nếu đang ở `en/ko/ja/zh` mà nội dung của ngôn ngữ đó trống, hệ thống sẽ ưu tiên fallback về `vi`
- Nếu `vi` cũng trống, hệ thống mới fallback tiếp sang các ngôn ngữ còn lại theo thứ tự nội bộ của model

Điều này giúp tránh lỗi `404` hoặc trang trống khi một bản dịch chưa được nhập đủ.

## 3. Dữ liệu đa ngôn ngữ nào đang hỗ trợ thật trong admin

Hiện tại các module sau đã hỗ trợ nhập nội dung thật trong admin:

- `Courses`
  - `name`, `slug`, `detail`, `supports`
  - có các biến thể: `_en`, `_ko`, `_ja`, `_zh`
- `Lessons`
  - `name`, `slug`, `description`
  - có các biến thể: `_en`, `_ko`, `_ja`, `_zh`
- `Categories`
  - `name`, `slug`
  - có các biến thể: `_en`, `_ko`, `_ja`, `_zh`
- `Teacher`
  - `name`, `slug`, `description`
  - có các biến thể: `_en`, `_ko`, `_ja`, `_zh`

Trong các form admin, bạn sẽ thấy tab:

- `VI`
- `EN`
- `KO`
- `JA`
- `ZH`

Khi nhập ở tab nào, hệ thống sẽ lưu vào cột ngôn ngữ tương ứng.

## 4. Quy tắc hiển thị nội dung theo ngôn ngữ

Khi người dùng truy cập một locale bất kỳ:

- Tên khóa học, bài học, chuyên mục, giảng viên sẽ dùng field đúng theo locale đó nếu có
- Nếu field đó trống, hệ thống dùng bản `vi`
- `slug` cũng hoạt động tương tự, nên nếu `slug_ko/slug_ja/slug_zh` trống thì route vẫn có thể dùng `slug` tiếng Việt

Ví dụ:

- Đang ở `/ja/...`
- Nếu `name_ja` có dữ liệu thì hiện tiếng Nhật
- Nếu `name_ja` trống thì hiện `name` tiếng Việt

## 5. Cách thêm hoặc sửa nội dung đa ngôn ngữ trong admin

Quy trình nên dùng:

1. Vào admin và tạo hoặc sửa `Course`, `Lesson`, `Category`, hoặc `Teacher`
2. Nhập nội dung đầy đủ ở tab `VI`
3. Nhập thêm ở các tab `EN`, `KO`, `JA`, `ZH` nếu đã có bản dịch
4. Nếu một ngôn ngữ chưa dịch xong, có thể để trống
5. Frontend sẽ tự fallback về `vi`

Khuyến nghị:

- Luôn nhập `VI` đầy đủ trước
- Chỉ nhập `slug_*` khi bạn muốn URL riêng cho ngôn ngữ đó
- Nếu không nhập `slug_*`, hệ thống vẫn dùng `slug` tiếng Việt làm fallback

## 6. Quy tắc tạo slug cho nhiều ngôn ngữ

Admin đang dùng helper slug dùng chung trong layout backend:

- `getSlugByLocale(title, locale)`
- `window.AdminSlug.byLocale(title, locale)`

Quy tắc:

- `vi`: bỏ dấu tiếng Việt rồi tạo slug ASCII
- `en/ko/ja/zh`: dùng rule Unicode-safe, giữ ký tự chữ hợp lệ và chuẩn hóa dấu cách thành `-`

Ví dụ:

```js
getSlugByLocale('Khóa học Laravel', 'vi')
getSlugByLocale('안녕하세요', 'ko')
getSlugByLocale('こんにちは', 'ja')
getSlugByLocale('中文标题', 'zh')
```

## 7. Notification (chuông thông báo) theo ngôn ngữ

Chuông thông báo hiện đã hỗ trợ tự hiển thị theo locale hiện tại.

Nguyên tắc:

- Notification mới sẽ lưu `title_translations` và `message_translations`
- Khi render, hệ thống tự chọn bản dịch đúng theo `app()->getLocale()`
- Nếu notification cũ không có bản dịch, hệ thống fallback về `title/message` cũ

Điều này áp dụng cho:

- chuông thông báo ở client
- chuông thông báo ở admin
- trang danh sách notification

Lưu ý:

- Notification cũ đã lưu trong DB trước khi thêm tính năng này sẽ không tự có bản dịch mới

## 8. Email theo ngôn ngữ

Mail notification hiện có thể bám theo locale tại thời điểm gửi.

Các luồng đã hỗ trợ:

- email xác thực tài khoản
- email quên mật khẩu
- email thông báo đổi mật khẩu

Nguyên tắc:

- Locale được gắn ngay lúc gọi notification
- Nếu mail chạy qua queue, worker vẫn giữ đúng locale đã được gắn trước đó

Hiện tại locale mail đang dựa trên:

- locale của request tại thời điểm gửi

Nếu muốn mail luôn theo “ngôn ngữ tài khoản”, cần thêm cột `locale` vào DB cho `students/users`.

## 9. File ngôn ngữ

Các file dịch đang nằm ở:

- `resources/lang/{locale}`
- `modules/*/resources/lang/{locale}`

Ví dụ:

- `resources/lang/vi`
- `resources/lang/en`
- `resources/lang/ko`
- `resources/lang/ja`
- `resources/lang/zh`

Khi thêm text mới:

1. Thêm key vào file `vi`
2. Bổ sung key tương ứng cho `en/ko/ja/zh`
3. Trong Blade hoặc PHP, dùng `__()` để gọi key

## 10. Khi deploy hoặc migrate

Nếu project mới được clone hoặc vừa thêm cột ngôn ngữ:

1. Chạy migrate

```bash
php artisan migrate
```

2. Nếu cần seed:

```bash
php artisan db:seed
```

3. Nên cache lại cấu hình/view trên production:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 11. Checklist khi test đa ngôn ngữ

Nên test tối thiểu các mục sau cho cả `vi/en/ko/ja/zh`:

1. Đổi ngôn ngữ từ dropdown ngoài client
2. Vào chi tiết khóa học bằng URL locale tương ứng
3. Kiểm tra fallback khi field `*_en`, `*_ko`, `*_ja`, `*_zh` bị trống
4. Kiểm tra chuông thông báo hiển thị đúng ngôn ngữ
5. Kiểm tra email xác thực / reset password ở đúng ngôn ngữ
6. Kiểm tra slug ở admin tự sinh đúng cho từng tab ngôn ngữ

## 12. Tóm tắt nguyên tắc vận hành

- `VI` là nguồn dữ liệu gốc an toàn nhất
- `EN/KO/JA/ZH` là bản dịch bổ sung
- Nếu thiếu bản dịch, hệ thống fallback về `VI`
- Notification mới tự đổi theo locale hiện tại
- Mail hỗ trợ locale theo request lúc gửi
- Admin đã hỗ trợ nhập dữ liệu thật cho 5 ngôn ngữ ở các module chính

