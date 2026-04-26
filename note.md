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
- Thêm màn riêng dành cho giáo viên ( Đăng nhập riêng chỉ (super admin và admin và teacher mới có quyền đăng nhập hoặc thêm quyền đăng nhập vào trang đó)) ( Done )
- Giờ tôi muốn là cái gói giáo viên tôi thêm bao nhiêu gói thì bên kia đổ dữ liệu từng đẩy gói limit 5 gói và thêm 1 cái nút kiểu sắp xếp xem nó đứng thứ mấy và thêm nút tích kiểu viết là gói hot hay được quan tâm nhiều nhất và thêm nút ẩn nữa bạn xem logic như nào làm giúp tôi
- Làm cái mã giảm giá dành riêng cho đăng ký giáo viên ( Done )
- Thêm cái ai tạo mã giảm giá ( Done )
- admin tạo ra 1 gói dành riêng cho giáo viên đó ( Kiểu tặng gói riêng ý  gói đặc quyền riêng họ) (Done)
- Cho phép khóa tài khoản giáo viên nhưng không khóa tài khoản học viên và ngược lại (Done)
- Sắp xếp lại cái sidebar ( Done )
- Quản lý combo khoá học ( Done thêm cả chức năng Hot để đẩy khoá học đó lên)
- Khi giảng viên huỷ hợp tác thì trang profile của giảng viên đó sẽ không được thấy nữa mà sẽ chuyển thành 404 hoặc tôi cần bạn đưa thêm ý tưởng là nếu họ dừng hợp tác thì tất cả mọi thứ của họ sẽ làm sao ( Đưa ra ý tưởng tốt hơn) (chó thành 404 và chỉ học viên đã mua mới học được) ( Done )
- Thêm tính năng comming soon khoá học ( Done )
- Thêm tính năng gif khoá học hoc viên ( Done )
- Thêm tính năng bán combo theo số lượng va có thời gian ( Done)
- Thêm cái dùng hợp tác hủy tư cách giáo viên ẩn tất cả những thứ liên quan đến giáo viên đó, tài khoản giáo viên sẽ được hạ xuống tài khoản học viên không vô được màn giáo viên, tất cả các khóa học được cấp hay của bản thân sẽ ẩn đi và chỉ có học viên nào mua thì vẫn dùng được ( Done )
- Vi tài khoản học viên sẽ là tài khoản dùng chung để tạo tài khoản giáo viên nên giờ làm cách nào để vào khi admin dùng quản trị sẽ biết rõ tk nào giáo viên tk nào học viên và cho phép admin có thể vào xem tài khoản học viên hay tài khoản giáo viên và có thể đăng nhập vào tài khoản đó để xem kiểm tra cho dễ  ( Done )
- Chưa làm config ngân hàng (bank, momo, vnpay thêm bật/tắt và bên clients và teacher khi tắt cái nào thì hiện bảo trì cái đó)
- Tỷ lệ chuyển đổi: xem bao nhiêu người xem trang, xem bao nhiêu người vào trang j nhiều nhất
- Đối với cái thông báo giảng viên đổi thành thông báo thì làm nó như 1 cái email kiểu viết tạo đúng input tiêu đề, nội dung, có j kèm có button không và dùng đúng giao diện chung của email web và chọn thông báo cho ai và trong đó có 1 cái là thông báo cho học viên hay giảng viên
- Thêm chức năng quản lý huy hiệu ( CURD, xóa mềm có thùng rác để khôi phục và cho tự thêm màu với từng huy hiệu và cho thêm icon và cung cấp nơi xem mã màu và icon để admin dễ dang dung và thay thế )
- Sắp xếp lại nội dung trong dashboard của admin
- Thêm chức năng viết được mô tả các chức năng trong gói đấy kiểu ( AI quiz: mổ tả là gì...)
- Thêm chức năng khóa giảng viên ( Nếu giảng viên vi phạm j đó )
- Cái góp ý / báo cáo đang trả dữ liệu sai
- Ở quản lý học viên thêm cái học viên đó đang học khóa học nào của giảng viên nào để dễ kiểm soát
- Hỏi AI ở 2 màn kia tôi đều đa ngôn ngữ nên số tiền của nó lúc sẽ khác nên bên admin có cần phải làm j để biết đúng số tiền không có cần đa ngôn ngữ không
- Cần thêm tính năng hay lượt bỏ tính năng j không
- Chức năng log có thiếu hay không
- Chức năng phần quyền check xem có thiếu j không
- Làm trang xem chi tiết thông báo
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
- Làm cái trang xem profile của giảng viên ( Giảng viên sẽ hiện ra profile khi đăng ký đổ ra, và có rating giảng viên, có bao nhiêu khóa học, bài giảng trong website )
- Đối với tài khoản đã được nâng lên làm teacher thì sẽ có tất cả các khóa học mà mình tạo ra 
- Đối với tài khoản đã được nâng lên làm teacher sẽ bỏ cái trở thành giáo viên
- Chỉ có super admin mới có quyền xóa nên làm chức năng khóa tài khoản giảng viên vì nếu admin thấy không hoạt động thì khóa lại
- Đối với user, teacher, student thêm quyền khóa trong phân quyền
- Check tất cả phân quyền là khi tắt quyền nào thì ẩn quyền nó ở màn đó đi (VD: tắt quyền sửa học viên thì ẩn sửa đi)
- Với các gói thì xem được chi tiết rõ các gói đó như nào, Thêm so sánh ở đó
- Khi tạo giáo viên yêu cầu phải thêm rõ cái tài khoản ngân hàng, yêu cầu phải có 1 tài khoản ngân hàng mới được tạo quyền 
- Trong profile có thêm cái chứng chỉ nx để ấn vô xem
- À với bình luận họ xem được bình luận và trả lời được chỉ không ẩn/hiện được thôi nhé và thêm cái đánh giá sao ( max 5 sao ) cho tôi thêm cả student lần teacher để student đánh giá ( Chưa xong bên clients )
- Ở trang chủ thêm cái ô button lựa chọn theo nổi bật, nhiều view, giáo viên nổi bật.
- Thêm cái thông báo khi vào màn teacher và popup khi vào màn teacher 

 Teacher:
- Làm cái hồ sơ giáo viên ( Done )
- Làm quản lý học sinh cho giáo viên ( Gán khóa học ) ( Done )
- Làm quản lý bình luận những khóa học của giáo viên đó ( Done )
- Làm quản lý mã giảm giá ( Done )
- Làm quản lý đơn hàng ( Done )
- Với cái xử lý rút tiền thì nhập 1 lần tài khoản sẽ lưu tài khoản đó tôi đa 3 tài khoản ngân hàng khác nhau với tài khoản t4 sẽ phải xin từ admin accpet mới thay đổi và khi thay đổi sẽ thay thay 1 trong 3 tài khoản đó ( Done )
- Dựa theo các gói thì có làm chức năng giới hạn j với các gói không kiểu ( Gói free có 2 khóa, không nhân bản, không bình luận được,...) hay có thêm chức năng j để giới hạn không ( Done )
- Khi đã là giáo viên rồi thì khi đổi gói có cần admin duyệt không hay tự động chuyển gói ( Góp ý cho tôi ) ( Done )
- Làm chức năng góp ý hoặc báo cáo với admin ( kiểu tôi muốn thêm danh mục j đó để phát triển) ( Done )
- Thêm các noti vào cái chuông như kiểu: có bình luận, có người mua khóa học, mã giảm giá sắp hết hạn hay số lượt, gói giáo viên sắp hết hạn, các tính năng mới j đó admin cập nhập dành riêng cho giáo viên ( Done )
- với trường hợp giáo viên mua gói tháng/năm thì khi hết hạn và họ không gia hạn thì xử lý như nào ( Done tự về gói free nếu không gia hạn gói )
- Với trường hợp họ đang dùng gói không giới hạn khóa học hay mã giảm giá thì khi họ hạ gói xuống nó limit thì xử lý như nào ( Done sẽ cho họ active)
- Với trường hợp tài khoản giảng viên họ không dùng nữa và họ không báo với mình thì sao mình không hề biết là họ không dùng nữa ( Done Admin check số ngày hoạt động)
- Nếu bạn muốn, mình có thể làm thêm một bước nữa là cho cột được chọn có icon ✓ Đang so sánh hoặc một thanh màu chạy dọc ở đầu cột để trực quan hơn nữa. ( Done )
- Thêm chức năng xem lại lịch sử thao tác, đôi với quản lý học viên cái giám sát học viên học được bao nhiêu % thì thêm vào đó và phần quyền trong gói giúp tôi ( Done )
- Hướng dẫn cái cấp chứng chỉ ( Done )
- Cái defauth avatar khi tạo giáo viên nằm ở resources/assets/teacher.png ( Done )
- Nghĩ xem cần thêm chức năng nào khác không ( Tôi muốn chức năng nó có thể liên quan đến các gói của giáo viên để gói đó có giá trị hơn khiến giảng viên mua để sử dụng) ( Done hiện tại còn thêm j ở tương lai thêm sau )
- Sắp xếp lại nội dung trong dashboard của teacher ( Done )
- Huy hiệu verified/premium teacher ( Done )
- Đối với cái khuyễn mãi thì làm nó như 1 cái email kiểu viết tạo đúng input tiêu đề, nội dung (ckeditor đúng light mode/dark mode), có j kèm có button không và dùng đúng giao diện chung của email web ( Done )
- Thêm chức năng giao bài quiz ( Done )
- Thêm chức năng tạo câu hỏi = AI ( Done )
- Với những cái notification mà kiểu email thì sẽ xử lý như nào cho nó hiện cái noti ra à hay là cho hiện noti bấm vào thì sẽ có phần đọc noti đó như email tôi đang không biết xử lý cái notification và tôi thêm cho tôi cái 2 checkbox dành cho email và thông báo tại web ( Done )
- Thêm cái preview dành cho bài học ( Xem được video trước và chỉ cần bấm vào thì hiện modal giống phần học thử như bên client xem video có hoạt dộng ổn không ) ( Done )
- Fix 1 số bug liên quan đến tất cả các limit trong gói ( Done )
- Với AI quiz thêm quyền vào trong admin ( Done )
- Yêu cầu rút tiền tôi muốn là cái thêm ngân hàng tách ra riêng và chỉ khi chọn được ngân hàng thì mới nhập được giá tiền và ghi chú và validate (đối với số tiền min là 5k) ( Done )
- Thêm chức năng hủy hợp tác ( Đẩy lên admin + lý do và khi hủy thì tất cả bài giảng ẩn đi chỉ có học viên nào đã mua thì vẫn còn sử dụng và bị đẩy khỏi màn giáo viên gửi mail cảm ơn đã hợp tác tài khoản hạ cấp xuống học viên) ( Done )
- Gộp tất cả các import/execport vào 1 quyền ở trong gói ( Done )
- Check lại toàn bộ màn giáo viên xem cần thêm,sửa,xóa j không và xem có bug hay j không thì sửa luôn (tạo file plan từ logic -> layout -> code) ( Done )
- Check lại tất cả đa ngôn ngữ ( lang ) của màn teacher ( Đặc biệt là vi phải có dấu những j liên quan thì phải tự điều chỉnh lại)
- Khi nhân bản thêm cái xác nhận ( Done )
- Đa ngôn ngữ (VI/EN/JA/KO/ZH) module dashboard ( những chỗ nào bị Mojibake hãy sửa ngay) ( Done )
- Đa ngôn ngữ (VI/EN/JA/KO/ZH) module khóa học của tôi ( những chỗ nào bị Mojibake hãy sửa ngay) ( Done )
- Đa ngôn ngữ (VI/EN/JA/KO/ZH) module bài giảng ( những chỗ nào bị Mojibake hãy sửa ngay) ( Done )
- Đa ngôn ngữ (VI/EN/JA/KO/ZH) module doanh thu (http://127.0.0.1:8000/teacher/doanh-thu) ( những chỗ nào bị Mojibake hãy sửa ngay) ( Done )
- Đa ngôn ngữ (VI/EN/JA/KO/ZH) module yêu cầu rút tiền ( những chỗ nào bị Mojibake hãy sửa ngay) ( Done )
- Đa ngôn ngữ (VI/EN/JA/KO/ZH) module mã giảm giá ( những chỗ nào bị Mojibake hãy sửa ngay) ( Done )
- Đa ngôn ngữ (VI/EN/JA/KO/ZH) module khuyến mãi ( những chỗ nào bị Mojibake hãy sửa ngay) ( Done )
- Đa ngôn ngữ (VI/EN/JA/KO/ZH) module hồ sơ ( những chỗ nào bị Mojibake hãy sửa ngay) ( Done )
- Đa ngôn ngữ (VI/EN/JA/KO/ZH) module thông báo ( những chỗ nào bị Mojibake hãy sửa ngay) ( Done )
- Đa ngôn ngữ (VI/EN/JA/KO/ZH) module link giới thiệu ( những chỗ nào bị Mojibake hãy sửa ngay) ( Done )
- Đa ngôn ngữ (VI/EN/JA/KO/ZH) module học viên ( những chỗ nào bị Mojibake hãy sửa ngay) ( Done )
- Đa ngôn ngữ (VI/EN/JA/KO/ZH) module bình luận ( những chỗ nào bị Mojibake hãy sửa ngay) ( Done )
- Đa ngôn ngữ (VI/EN/JA/KO/ZH) module chứng chỉ ( những chỗ nào bị Mojibake hãy sửa ngay) ( Done )
- Đa ngôn ngữ (VI/EN/JA/KO/ZH) module nhật ký hoạt động ( những chỗ nào bị Mojibake hãy sửa ngay) ( Done )
- Đa ngôn ngữ (VI/EN/JA/KO/ZH) module gói giảng viên ( những chỗ nào bị Mojibake hãy sửa ngay) ( Done )
- Đa ngôn ngữ (VI/EN/JA/KO/ZH) module gớp ý/ báo cáo ( những chỗ nào bị Mojibake hãy sửa ngay) ( Done )
- Đa ngôn ngữ (VI/EN/JA/KO/ZH) module hủy hợp tác ( những chỗ nào bị Mojibake hãy sửa ngay) ( Done )
- Đa ngôn ngữ (VI/EN/JA/KO/ZH) module log ( những chỗ nào bị Mojibake hãy sửa ngay) ( Done )
- 2 nút chuông và tài khoản ở header bị lỗi trong dark mode ( Done )
- Tự làm cái sidebar đa ngôn ngữ ( Done )
- Giá tiền khóa học sẽ đúng với ngôn ngữ của nó và khi tính giá thì theo tiền tệ của ngôn ngữ đó và cho biết là tiền đổi sang tiền nào là bao nhiêu ( và thêm 1 cái là học viên mua = tiền ngôn ngữ nào thì + theo tiền ngôn ngữ đó, thêm % phí chuyển đổi bên admin và cho giảng viên biết là bao nhiêu, bên admin dùng api nào free và update theo ngày để biết tỉ giá tiền quốc tế để dựa vào đó tính toán) (VD: giáo viên nhập giá khóa học 10k thì tự tính toán với tỉ giá mà admin đã set để tính các tiền quốc tế và nếu tôi nhập ở bên tiếng Anh là 100 (bên tiếng anh sẽ là 100$) thì nó tự tính các tiền khác là bao nhiêu) ( Done )
- Check lại chức năng log có chỗ nào thiếu không ( Done )
- Có nên cho giáo viên xóa vĩnh viễn các dữ liệu không nhỉ hay chỉ cho xóa mềm vào thùng rác và khôi phục ( Done )
- Check lại toàn bộ validate xem có chỗ nào thiếu không ( Done )
- Làm trang 403,404 riêng dành cho màn teacher ( Note )
- Fix lại thanh toán giáo viên
Tổng kết
- Tìm tất cả file .bak ( Done )
- check lại lần cuối trước khi đẩy lên production



