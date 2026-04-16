# Đề xuất các tính năng mới tăng giá trị cho Gói Giảng Viên (Teacher Packages)

Dữ liệu hiện tại tôi phân tích từ `[modules/Teacher/src/Models/TeacherPackage.php]` cho thấy nền tảng của bạn đã khá giống một hệ thống LMS chuyên nghiệp (giống Udemy/Kajabi) với các quyền lợi chia khóa học, gửi email promotion, xuất analytics, gán chứng chỉ. 

Để **ép** hoặc **hấp dẫn** giảng viên phải bỏ tiền mua gói (hoặc nâng cấp gói cao hơn), bạn cần đánh vào **2 tử huyệt** của họ: **Tăng doanh thu (Upsell/Marketing)** và **Tăng chất lượng đào tạo (Premium Learning)**.

Dưới đây là các ý tưởng tính năng rất đáng tiền để bổ sung:

## 🔥 1. Nhóm tính năng Tăng Doanh Thu & Marketing (Rất dễ bán gói)

| Tính Năng (Feature) | Mô Tả & Lợi Ích | File Cần Tác Động |
| :--- | :--- | :--- |
| **Order Bumps / Upsells**<br>`can_create_upsells` | Cho phép giảng viên bán kèm thêm 1 sản phẩm phụ (VD: Tài liệu PDF, hoặc 1 giờ Coaching) ngay tại trang Checkout. Tăng AOV (Giá trị trung bình đơn) cực mạnh. | `TeacherPackage.php`, `CheckoutController.php` |
| **Sub-Account / Co-instructors**<br>`can_add_co_instructors` | Cho phép giảng viên VIP thêm tài khoản Trợ giảng (TA) để vào chấm Quiz, trả lời QA mà không cần giao pass chính. Phù hợp cho trung tâm/nhóm. | `Teacher.php`, `TeacherPackage.php` |
| **Drip Content (Mở khóa dần)**<br>`can_drip_content` | Thay vì mua là xem được hết, bài học sẽ mở khóa dần theo thời gian (ví dụ: Ngày 1 mở Bài 1, Ngày 7 mở Bài 2). Chống học dồn, chống tải lậu hàng loạt và hoàn tiền. | `Lesson.php`, `StudentLessonController.php` |

## 🌟 2. Nhóm tính năng Quản lý Hệ thống AI & Công cụ (Premium)

| Tính Năng (Feature) | Mô Tả & Lợi Ích | File Cần Tác Động |
| :--- | :--- | :--- |
| **AI Quiz & Content Generator**<br>`can_use_ai_tools` | Tích hợp AI giúp giảng viên bấm 1 nút tạo ngay ra bộ 10 câu hỏi Quiz từ Nội dung bài học, hoặc làm tóm tắt bài học. Rất tốn tài nguyên nên chỉ gọi (API) cho gói Premium. | `TeacherPackage.php`, Tạo AI Controller riêng |
| **Giới hạn Video Storage (GB)**<br>`storage_limit_gb` | Hiện tại bạn đang giới hạn số khóa học (`course_limit`). Nếu rẽ sang giới hạn tổng dung lượng Video (Free = 2GB, Premium = 50GB), đây là lý do mạnh nhất ép họ phải nâng cấp (Vì video rất tốn dung lượng máy chủ). | `TeacherPackage.php`, `UploadController.php` |

## 🎓 3. Nhóm tính năng Trải nghiệm Học sinh (Giữ chân học viên)

| Tính Năng (Feature) | Mô Tả & Lợi Ích | File Cần Tác Động |
| :--- | :--- | :--- |
| **Live Classes / Webinars**<br>`can_host_live_sessions` | Cho phép Giảng viên lên lịch các buổi Live Zoom/Google Meet. Nút tham gia chỉ hiện cho học viên đã mua khóa và đúng giờ mới sáng lên. | Thêm bảng `live_sessions`, `TeacherPackage.php` |
| **File Assignments**<br>`can_manage_assignments` | Mở rộng của Quiz. Thay vì chép trắc nghiệm, học viên phải "Upload File ZIP/PDF" (Bài tập thực hành/đồ án) để giảng viên chấm. Rất cần cho dân IT/Design. | Thêm bảng `course_assignments`, `TeacherPackage.php` |
| **Private Community / QA Board**<br>`can_create_communities` | Một group thảo luận riêng tư (như Group FB thu nhỏ) dành riêng cho học viên của 1 khóa học. Những khóa VIP mới có quyền lợi này. | `TeacherPackage.php`, Thêm Module Community |

---
**Nhận xét nhanh:** 
- Nếu bạn muốn làm nhanh mà ra tiền thật sự cho giảng viên: Nên làm **Drip Content** (Mở khóa bài học theo từng ngày) và **Assignments (Nộp Đồ án)**.
- Hai tính năng này cực kỳ phù hợp với cái Quiz chúng ta vừa hoàn thiện.

Bạn tham khảo xem ưng ý với mô hình nào để ta lên kế hoạch triển khai nhé!
