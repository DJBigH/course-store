# DỰ ÁN WEBSITE HỌC TRỰC TUYẾN

## Dành cho người dùng

- Hiển thị danh sách khóa học
- Hiển thị thông tin chi tiết khóa học
- Xem video bài giảng
- Download tài liệu bài giảng
- Học thử bài giảng
- Đăng ký/Đăng nhập
- Trang tài khoản: Thông tin cá nhân, khóa học của tôi,...
- Mua khóa học
- Giỏ hàng
- Hiển thị danh sách tin tức
- Hiển thị chi tiết tin tức

## Dành cho quản trị

- Quản lý danh mục (\*)
- Quản lý học viên (\*)
- Quản lý khóa học (\*)
- Quản lý giảng viên (\*)
- Quản lý bài giảng (\*)
- Quản lý danh mục tin tức (\*)
- Quản lý tin tức (\*)
- Kích hoạt khóa học cho học viên (\*)
- Quản lý file tài liệu (\*)
- Quản lý video (\*)
- Quản lý đơn hàng (\*)
- Quản lý người dùng (Quản lý hệ thống)
- Phân quyền quản trị hệ thống
- Báo cáo, thống kê,...

## API

- Xây dựng API hoàn chỉnh

## Phân tích Database

1. Table categories => Quản lý danh mục

- id => int
- name => varchar(200)
- slug => varchar(200)
- parent_id => int
- created_at => timestamp
- updated_at => timestamp

2. Table courses => Quản lý khóa học

- id => int
- name => varchar(255)
- slug => varchar(255)
- detail => text
- teacher_id => int
- thumbnail => varchar(255) => Để cuối
- price => float
- sale_price => float
- code => varchar(100)
- durations => float
- is_document => tinyint
- supports => text
- status => tinyint
- created_at => timestamp
- updated_at => timestamp

3. Table lessons => Quản lý bài giảng

- id => int
- name => varchar(255)
- slug => varchar(255)
- video_id => int
- course_id => int
- document_id => int
- parent_id => int
- is_trial => tinyint
- views => int
- position => int
- duration => float
- description => text
- created_at => timestamp
- updated_at => timestamp

4. Table categories_courses => Trung gian liên kết giữa danh mục và khóa học

- id => int
- category_id => int
- course_id => int
- created_at => timestamp
- updated_at => timestamp

5. Table teacher => Giảng viên

- id => int
- name => varchar(100)
- slug => varchar(100)
- description => text
- exp => float
- image => varchar(255)
- created_at => timestamp
- updated_at => timestamp

6. Table videos => Quản lý video bài giảng

- id => int
- name => varchar(255)
- url => varchar(255)
- created_at => timestamp
- updated_at => timestamp

7. Table documents => Quản lý tài liệu bài giảng

- id => int
- name => varchar(255)
- url => varchar(255)
- size => float
- created_at => timestamp
- updated_at => timestamp

8. Table categories_posts => Quản lý danh mục tin tức

- id => int
- name => varchar(200)
- slug => varchar(200)
- parent_id => int
- created_at => timestamp
- updated_at => timestamp

9. Table posts => Quản lý tin tức

- id => int
- title => varchar(255)
- slug => varchar(255)
- content => text
- exceprt => text
- thumbnail => varchar(255)
- category_id => int
- created_at => timestamp
- updated_at => timestamp

10. Table students => Quản lý học viên

- id => int
- name => varchar(100)
- email => varchar(100)
- phone => varchar(20)
- password => varchar(100)
- address => varchar(200)
- status => tinyint(1)
- created_at => timestamp
- updated_at => timestamp

11. Table students_courses => Trung gian học viên và khóa học

- id => int
- course_id => int
- student_id => int
- created_at => timestamp
- updated_at => timestamp

12. Table orders => Quản lý đơn đăng ký của học viên

- id => int
- student_id => int
- total => float
- status => tinyint(1)
- created_at => timestamp
- updated_at => timestamp

13. Table orders_detail => Chi tiết đơn hàng

- id => int
- order_id => int
- course_id => int
- price => float
- created_at => timestamp
- updated_at => timestamp

14. Table orders_status => Quản lý trạng thái đơn hàng

- id => int
- name => varchar(200)
- created_at => timestamp
- updated_at => timestamp

15. Table users => Quản trị hệ thống

- id => int
- name => varchar(100)
- email => varchar(100)
- password => varchar(100)
- group_id => int
- created_at => timestamp
- updated_at => timestamp

16. Table groups => Quản trị nhóm người dùng

- id => int
- name => varchar(100)
- permissions => text
- created_at => timestamp
- updated_at => timestamp

17. Table modules => Danh sách các module trong trang quản trị

- id => int
- name => varchar(100)
- title => varchar(200)
- role => text

18. Table options => Quản lý các thiết lập

- id => int
- name => varchar(100)
- value => text

## Cài đặt Project và kết nối với Github

### Cài đặt Laravel

composer create-project laravel/laravel .

### Kết nối với Github

- Đăng ký tài khoản Github (Nếu có rồi hãy đăng nhập)

- Tạo Repository

- Kết nối với folder project trên máy tính

- Push code lên Github

### Quy trình updat code lên github

- git add .
- git commit -m "Noi dung update"
- git push

## Cài đặt Laravel Module và Repository

### Cài đặt Laravel Module

### Cài đặt Repository cho Laravel Module

## Viết Artisan Console cho Laravel Module

`php artisan make:module ten_module`

## Tích hợp Layout Admin

## Xây dựng Module quản lý Users

### Tạo Migrations - Seeder - Chuẩn bị giao diện

### Tạo Repository và các phương thức cần thiết

- Hiển thị danh sách User (Có phân trang)
- Thêm user
- Sửa user
- Xóa user
- Lấy thông tin 1 user

### Tạo FormRequest và các phương thức Validation

### Viết chức năng thêm user

### Viết chức năng hiển thị user

### Viết chức năng cập nhật user

### Viết chức năng xóa user

## Xây dựng Module quản lý danh mục

Hàm tạo slug javascript

```javascript
function getSlug(title) {
    //Đổi chữ hoa thành chữ thường
    slug = title.toLowerCase();

    //Đổi ký tự có dấu thành không dấu
    slug = slug.replace(/á|à|ả|ạ|ã|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/gi, "a");
    slug = slug.replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/gi, "e");
    slug = slug.replace(/i|í|ì|ỉ|ĩ|ị/gi, "i");
    slug = slug.replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/gi, "o");
    slug = slug.replace(/ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự/gi, "u");
    slug = slug.replace(/ý|ỳ|ỷ|ỹ|ỵ/gi, "y");
    slug = slug.replace(/đ/gi, "d");
    //Xóa các ký tự đặt biệt
    slug = slug.replace(
        /\`|\~|\!|\@|\#|\||\$|\%|\^|\&|\*|\(|\)|\+|\=|\,|\.|\/|\?|\>|\<|\'|\"|\:|\;|_/gi,
        "",
    );
    //Đổi khoảng trắng thành ký tự gạch ngang
    slug = slug.replace(/ /gi, "-");
    //Đổi nhiều ký tự gạch ngang liên tiếp thành 1 ký tự gạch ngang
    //Phòng trường hợp người nhập vào quá nhiều ký tự trắng
    slug = slug.replace(/\-\-\-\-\-/gi, "-");
    slug = slug.replace(/\-\-\-\-/gi, "-");
    slug = slug.replace(/\-\-\-/gi, "-");
    slug = slug.replace(/\-\-/gi, "-");
    //Xóa các ký tự gạch ngang ở đầu và cuối
    slug = "@" + slug + "@";
    slug = slug.replace(/\@\-|\-\@|\@/gi, "");
    return slug;
}
```

## Xây dựng Module quản lý khóa học

## Xây dựng Module quản lý giảng viên

## Thiết lập ràng buộc khóa học và giảng viên

- Ràng buộc khóa ngoại
  => Nếu giảng viên bị xóa => Các khóa học liên quan đến giảng viên sẽ bị xóa

- Ràng buộc hình ảnh

* 1 hình ảnh sử dụng nhiều nơi => Xóa 1 bản ghi => Xóa ảnh
* Tạo 1 module Media (Database) => Khi chọn ảnh ở các module => Bật popup của module media

## Hoàn thiện các câu lệnh Artisan Console

### Tạo Module

`php artisan make:module TenModule`

### Tạo Controller

```
php artisan module:make-controller TenController TenModule
```

### Tạo Middleware

```
php artisan module:make-middleware TenMiddleware TenModule
```

### Tạo Request

```
php artisan module:make-request TenRequest TenModule
```

### Tạo Model

```
php artisan module:make-model TenModel TenModule
```

### Tạo Migration

```
php artisan module:make-migration TenMigration TenModule
```

### Tạo Seeder

```
php artisan module:make-seeder TenSeeder TenModule
```

## Xây dựng chức năng mã giảm giá

- Quản lý danh sách mã giảm giá

* code: Mã giảm giá
* course_id: Khóa học sẽ được giảm giá
* user_id: Khách hàng được giảm giá
* start_date: Thời gian bắt đầu mã giảm giá
* end_date: Thời gian kết thúc mã giảm giá
* total_condition: Điều kiện giảm giá
* count: Số lượng mã giảm giá
* type: Loại giảm giá (Số tiền, phần trăm)
* discount: Giá trị giảm giá

- Ý tưởng triển khai:

* Nhập mã giảm giá ==> Verify mã giảm giá (Server kiểm tra)
* Nếu đúng: Giảm giá ở trang thanh toán và trong thông tin thanh toán (STK, QR Code)
* Nếu sai: Thông báo lỗi

- Lưu ý:

* Xử lý bằng ajax ==> Chú ý đến bảo mật
* User có thể thêm mã giảm giá vào phút trót hoặc số lượng sắp hết hoặc nhiều user thêm cùng 1 thời điểm ==> Phản hồi kịp thời cho user và kiểm tra khi xử lý thanh toán tự động ==> Áp dụng: http long polling

## Xây dựng chức năng thanh toán

1. Ví điện tử + cổng thanh toán

- 1pay
- vnpay
- momo

Khi click vào thanh toán ==> Tạo đơn bên cổng thanh toán ==> Đưa ra danh sách các hình thức thanh toán

Sau khi user thanh toán ==> Cổng thanh toán nhận được thông tin ==> Trả về trạng thái cho ứng dụng

Bên phía ứng dụng ==> Cập nhật trạng thái

2. Dịch vụ biến động số dư

- Hiển thị số tài khoản, số tiền, nội dung (Để mã đơn hàng). Hoặc dùng QR Code
- Đăng ký dịch vụ bên thứ 3 cho phép nhận biến động số dư (Sepay, Payos, casso,...)
- Viết webhook trên web và liên kết hợp dịch vụ cung cấp nhận biến động số dư
- Khi nào tài khoản có biến động số dư --> Gửi về bên nhà cung cấp --> Nhà cung cấp sẽ gửi về web --> Cập nhật logic liên quan đến đơn hàng

---------\***\*\*\*\*\*\*\***\*\*\***\*\*\*\*\*\*\***\*\*\***\*\*\*\*\*\*\***\*\*\***\*\*\*\*\*\*\***---------
\*Tự phát triển:

- laravel file manager
- ckeditor
- npm install moment ( cài hay không cũng được )
- khi cài project cần cài thêm queue
  npm install --save toastify-js
- vietqr tạo qr dễ dàng
  CHECKOUT_COUNTDOWN=1

    Admin:

- Bên admin chưa có chức năng quản lý đơn hàng, quản lý mã khuyến mại ( Tự làm vì trong khóa không dạy)
- phần trang block = status của học viên có thể đổi lại thành không có quyền hạn vào xem hoặc j đó nếu vì chưa phát triền xong
- trang lịch sử nhập mã khuyến mãi ( Done )
- Notification (Done và có thể thêm nữa trong tl)
- Làm trang tổng quan
- Làm thêm cái options thiết lập cấu hình cho website (Done)
- Trong khóa học project này nếu không dạy đổi giao diện email thì hay làm lại giao diện đó việt hóa nó (Done)
- cái sắp xếp bài giảng chưa kéo được bài giảng ở module dưới lên module trên ( có j check lại hoặc note là chỉ kéo được bài giảng của module đó )
- Làm cái ngẫu nhiên code ở khóa học (Done)
- Làm trang quản lý liên hệ (Done)
- Với cấp mã khuyến mãi thì nếu đó có giá trị thời gian thì check là nếu nó hết time rồi thì không cho áp mã (Done)
- Làm trang thông tin cá nhân ở bên admin (Done)
- Thêm hay cập nhập thời gian ở phần mã giảm giá không vào db
- Import, Export cho toàn bộ (Để làm sau)
- Xóa đơn hàng thì xóa luôn khóa học mà học viên đã mua (Cái này note lại lúc nào thấy cấn thì làm còn đâu làm thế kia vẫn ổn)
- Làm 1 trang log tổng dành cho super admin (Done)
- Làm trang lịch sử hoạt động (Course (Done), User (Done), Cate (Done), Lesson (Done), Teacher (Done), Students (Done), Order, Copouns (Done), Contact (Done), Config (subject id == null))
- Thêm cái setting sửa banner (Yêu cầu 3 ảnh nếu sửa 1 thì chỉ cập nhập 1, 3 cái sub banner) (DOne)
- Suy nghĩ xem có cách nào liên kết được bảng user với teacher để phân quyền ko
- Phân quyền admin/giáo viên
- Tạo thêm 1 nơi để setting phân trang được (Không biết có nên làm không)
- Làm đa ngôn ngữ ( Tiếng Anh/Tiếng Việt ) ( Tương lai có thể thêm cái tiếng khác nữa ) (Done home page)
    Clients:

- Làm trang tổng quan cho cả clients ( Done )
- Giới hạn mã khuyến mãi cho học viên ( Done )
- Từ làm nốt chức năng thanh toán ( Vì trong khóa học dạy thanh toán trực tiếp ) ( Done )
- Cập nhập lại quyền khi học viên đã mua khóa học ( Done )
- Làm trang chủ giống unicode (Done có biến tấu thêm 1 chút ở dưới)
- chức năng khóa học đã mua của học viên nên dùng trang bờ lóc tạm thời hẹ hẹ ( Hình như xử lý rồi )
- bên clients thiếu mấy trang nếu được cố code html css
- Làm cái lọc theo danh mục ở trên menu ( Done )
- Làm trang liên hệ (Done)
- Làm thanh toán = vnpay, momo
- Làm lượt xem khi ấn vào khóa học +1 lượt xem (Done)
- Notification clients (Done)
