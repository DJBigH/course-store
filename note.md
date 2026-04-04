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

- Bên admin chưa có chức năng quản lý đơn hàng, quản lý mã khuyến mại ( Tự làm vì trong khóa không dạy) (Done)
- phần trang block = status của học viên có thể đổi lại thành không có quyền hạn vào xem hoặc j đó nếu vì chưa phát triền xong
- trang lịch sử nhập mã khuyến mãi ( Done )
- Notification (Done và có thể thêm nữa trong tl)
- Làm trang tổng quan (Done)
- Làm thêm cái options thiết lập cấu hình cho website (Done)
- Trong khóa học project này nếu không dạy đổi giao diện email thì hay làm lại giao diện đó việt hóa nó (Done)
- cái sắp xếp bài giảng chưa kéo được bài giảng ở module dưới lên module trên ( có j check lại hoặc note là chỉ kéo được bài giảng của module đó )
- Làm cái ngẫu nhiên code ở khóa học (Done)
- Làm trang quản lý liên hệ (Done)
- Với cấp mã khuyến mãi thì nếu đó có giá trị thời gian thì check là nếu nó hết time rồi thì không cho áp mã (Done)
- Làm trang thông tin cá nhân ở bên admin (Done)
- Import, Export cho toàn bộ (Để làm sau)
- Xóa đơn hàng thì xóa luôn khóa học mà học viên đã mua (Cái này note lại lúc nào thấy cấn thì làm còn đâu làm thế kia vẫn ổn)
- Làm 1 trang log tổng dành cho super admin (Done)
- Làm trang lịch sử hoạt động (Course (Done), User (Done), Cate (Done), Lesson (Done), Teacher (Done), Students (Done), Order, Copouns (Done), Contact (Done), Config (subject id == null))
- Thêm cái setting sửa banner (Yêu cầu 3 ảnh nếu sửa 1 thì chỉ cập nhập 1, 3 cái sub banner) (DOne)
- Suy nghĩ xem có cách nào liên kết được bảng user với teacher để phân quyền ko
- Phân quyền admin/giáo viên
- Tạo thêm 1 nơi để setting phân trang được (Không biết có nên làm không)
- Làm đa ngôn ngữ ( Tiếng Anh/Tiếng Việt ) ( Tương lai có thể thêm cái tiếng khác nữa ) (Done)
- Thêm hay cập nhập thời gian ở phần mã giảm giá không vào db (Done)
- Thêm remember login ( Done )
- Chi tiết hóa đơn hủy thanh toán nhưng ở dưỡi vẫn là đã thanh toán ( Done )
- Thêm cái nhận biết là thành toán = j ( Nếu sửa ở admin thì bên clients cần sửa luôn không) ( Done )
- Chỉnh lại khóa học khi chuyển thành đã ra mắt thì bên clients những người đã mua khóa học ấn vào sẽ bị trang 404 ( tìm xem hướng giải nào ok nhất ) ( Hướng tạo thêm 1 trường là khóa nhưng học viên đã mua vẫn xem được và khóa học viên đã mua ko xem dc) ( Done )
- Cái phần bảo mật nếu tài khoản admin bị lộ như kiểu sale, content bị lộ thì sử lý như nào ( Có bước khóa tài khoản rồi còn j nx...) ( Done )
- Chỉ có super admin chỉnh được tất cả các nhóm quyền khác mà có quyền chỉnh quyền thì không chỉnh được quyền của super admin ( super admin là quyền cao nhất chỉ đăng nhập vào super admin mới chỉnh được quyền của nó còn đâu không quyền nào chỉnh đươc nó) ( Done )
- ở trang danh sách khóa học, bài giảng khi click vào tên thì mở thêm 1 trang mở link đó ( Làm sau )
- Trong tất cả module thì module nào có xóa thì thêm cái xóa mềm cho tôi và với tất cả cái xóa hiện lên trên thì sẽ là xóa mềm hết ở trong thùng rác có hiện button xóa vĩnh viễn ( Done )
- Thêm quyền xóa mềm ( Done )
- Check lại mã giảm giá ( Done )
- Thêm phần quản lý học viên xem học viên đã bật 2FA chưa ( Done )
- Làm bên admin bảo mật hơn ( Done )
- Trong config thêm cái kiểm soát momo, vnpay, captcha ( Done )
- Thêm config tắt gửi mail ( Khi tắt sẽ xóa hết dữ liệu trong mail để lại mỗi biến và khi bật lại sẽ phải nhập lại key thì mới được) thêm cái test mail (Gửi 1 cái mail test đến chính bản thân mình xem nó có hoạt động không) ( Done )
- Check xem còn thiếu config nào không ( Done hiện tại 29-03-2026 có thể thấy cần j thêm sau khi test case)
- Trong config chatbot xem có cách nào train được con bot thẳng trên website quản trị không ko được thì thôi ( Bỏ )
- Check lại xem có thiếu quyền nào không, có quyền nào bị trùng nhau ko ( quyền trong nhóm cấu hình ) ( Done )
- Cái seed group thì tạo super admin và admin ( Done )
- Thêm chức năng light mode/ dark mode ( Done )
- check lại cái log xem có thiếu j không ( Done hiện tại 30-03-2026 có thể thấy cần j thêm sau khi test case )
- Đổi Chỉnh sửa lại Người thực hiện thì sẽ là tên ( quyền hạn kiểu: bigk(super-admin))
- Check cái notify thì cần thêm cái j nx ko ( Done )
- Thêm cái lịch sử đăng nhập, lịch sử thao tác ở bên admin và thêm 1 cái nữa check đăng nhập khác thiếp bị hoặc khác ip sẽ báo lên notify ( không dùng email để gửi, gửi lên log là được) ( Done )
- Check lại responsive ( Done)
- Viết seed quyền cho giáo viên
- Thêm màn riêng dành cho giáo viên ( Đăng nhập riêng chỉ (super admin và admin và teacher mới có quyền đăng nhập hoặc thêm quyền đăng nhập vào trang đó))
- Giờ tôi muốn là cái gói giáo viên tôi thêm bao nhiêu gói thì bên kia đổ dữ liệu từng đẩy gói limit 5 gói và thêm 1 cái nút kiểu sắp xếp xem nó đứng thứ mấy và thêm nút tích kiểu viết là gói hot hay được quan tâm nhiều nhất và thêm nút ẩn nữa bạn xem logic như nào làm giúp tôi
- Làm cái mã giảm giá dành riêng cho đăng ký giáo viên 
    Clients:
- Làm trang tổng quan cho cả clients ( Done )
- Giới hạn mã khuyến mãi cho học viên ( Done )
- Từ làm nốt chức năng thanh toán ( Vì trong khóa học dạy thanh toán trực tiếp ) ( Done )
- Cập nhập lại quyền khi học viên đã mua khóa học ( Done )
- Làm trang chủ giống unicode (Done có biến tấu thêm 1 chút ở dưới)
- chức năng khóa học đã mua của học viên nên dùng trang bờ lóc tạm thời hẹ hẹ ( Hình như xử lý rồi )
- Làm cái lọc theo danh mục ở trên menu ( Done )
- Làm trang liên hệ (Done)
- Làm lượt xem khi ấn vào khóa học +1 lượt xem (Done)
- Notification clients (Done)
- Thêm email khi mua hàng (Done)
- Bình luận khóa học (Done)
- Hoàn thành note hướng dẫn (Done)
- Làm chế độ sáng/tối (Done)
- Làm thanh toán = vnpay, momo (Done)
- Làm chức năng vô hiệu hóa tài khoản (Done)
- cái chỗ tìm ở trang chủ xem có cái j thay thế được không chứ nó như bù nhìn ( Done )
- Tất cả những cái phân trang làm mượt nhất có thể  ( Done )
- Làm captcha cho form liên hệ tránh spam và nhớ validate ( Dùng captcha dành cho localhost) ( Done )
- Xử lý tất cả các submit cho nó mượt không phải submit lại trang ( Done )
- Làm chức năng bảo mật 2 lớp ( Done )
- Làm chức năng check localtion khi đăng nhập ( Done )
- Làm lịch sử đăng nhập, lịch sử hoạt động hay thao tác ( Với thao tác thì là đổi mật khẩu, gửi mã xác thực, Chỉnh sửa thông tin ) ( Done )
- Với chức năng đổi mật khẩu kể cả khi không bật 2FA thì mỗi khi đổi mật khẩu thì sẽ gửi email rằng tài khoản đã đổi mật khẩu ( Done )
- Cái đổi mật khẩu cũng có vấn đề nếu là đổi mật khẩu xong họ vẫn không đăng nhập đúng mật khẩu đó (Check trong db thấy thay đổi rồi nhưng nhập đúng mk vừa thay thì lại bị lỗi)
- Quên mật khẩu có vấn đề là khi mới gửi mail xong vào mail đó đổi mật khẩu đã báo token sai hay quá hạn rồi và tôi muốn limit và thời gian token đó để đổi mk là 10p ( Done )
- Làm chức năng xóa tài khoản ( Done )
- Với cái trang mã giảm giá chỉ lấy mã giảm giá nào còn hiệu lực (còn thời gian, còn số lượng, không giới hạn số lượng, không giới hạn thời gian) ( Done )
- Với cái trạng thái đơn hàng thêm đa ngôn ngữ lưu vào db ỏ bảng order_status và thêm seeder ( Done )
- Phần bài giảng thì thêm cái tiến độ học tổng học được bao nhiêu % có tích đánh dấu những bài đã học ( Done )
- bên clients thiếu mấy trang nếu được cố code html css ( Done )
- Check UI/UX xem có trang nào khiến người dùng khó chịu hay không (các trang, light mode, đa ngôn ngữ) (Check lại để khi đẩy lên production tránh fix) ( Done )
- Thêm một con chatbot vào để giúp bán hàng khi không liên hệ được với admin ( Bot tự đọc db các khóa học, mã giảm giá, hay liên quan j đến website không được đọc những thông tin nhạy cảm hay bảo mật) ( Done )
- Kết nối với api của con gemeni thêm cho nó các api xem khóa học, đa ngôn ngữ (nếu thấy ổn thì làm)
- Nếu chatbot ổn thử kết nối với telegram xem nó có thông báo cho mình không 
- Làm 1 cái thông báo tổng cho toàn web từ backend->clients và làm cái popup khi vừa vào web hiện 1 bảng thông tin hay tin tức j đó ( Làm luôn cả chỗ để cho backend ghi ) ( Done )
- Check lại responsive ( Done )
- Chỉnh phiên đăng nhập từ 1 thiết bị sang giới hạn 2 thiết bị ( Done )
- Check cái notify của học viên xem thiếu hay thừa cái j ( Done )
- Thêm màn giáo viên ( Theo 1 ý tưởng mới giống udemy là có thêm 1 trang ở trên menu để đăng ký cho admin duyệt và chọn gói đăng ký  ) ( Done )
- Chưa check validate đăng ký giáo viên
- Làm cái mã giảm giá dành riêng cho đăng ký giáo viên
- Thêm cái danh mục dành cho các gói mua của giáo viên ( Gói hợp tác, Gói theo tháng/năm, Gói j đó...)
 Teacher:
- Làm cái hồ sơ giáo viên
- Làm quản lý học sinh cho giáo viên ( Gán khóa học )
- Làm quản lý bình luận những khóa học của giáo viên đó
- Làm quản lý mã giảm giá
- Làm quản lý đơn hàng
- Với cái xử lý rút tiền thì nhập 1 lần tài khoản sẽ lưu tài khoản đó tôi đa 3 tài khoản ngân hàng khác nhau với tài khoản t4 sẽ phải xin từ admin accpet mới thay đổi và khi thay đổi sẽ thay thay 1 trong 3 tài khoản đó
- Dựa theo các gói thì có làm chức năng giới hạn j với các gói không kiểu ( Gói free có 2 khóa, không nhân bản, không bình luận được,...)
- Góp ý hoặc báo cáo với admin
- Nghĩ xem cần thêm chức năng nào khác không
Tổng kết
- check lại lần cuối trước khi đẩy lên production


