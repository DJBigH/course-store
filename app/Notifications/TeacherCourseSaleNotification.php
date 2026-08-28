<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Modules\Orders\src\Models\Order;
use Modules\Courses\src\Models\Courses;
use Modules\Students\src\Models\Student;

class TeacherCourseSaleNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Order $order,
        protected Courses $course,
        protected Student $student
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $courseName = $this->course->name_locale ?: $this->course->name;
        $studentName = $this->student->name ?: 'Học viên';
        $orderCode = $this->order->code ?: ('#' . $this->order->id);

        $titleTranslations = [
            'vi' => 'Khóa học đã được bán',
            'en' => 'Course sold',
            'ko' => '강좌가 판매되었습니다',
            'ja' => 'コースが販売されました',
            'zh' => '课程已售出',
        ];

        $messageTranslations = [
            'vi' => "Học viên {$studentName} vừa mua khóa học \"{$courseName}\" của bạn (Đơn hàng {$orderCode}).",
            'en' => "Student {$studentName} just purchased your course \"{$courseName}\" (Order {$orderCode}).",
            'ko' => "{$studentName} 학생이 \"{$courseName}\" 강좌를 구매했습니다 (주문 {$orderCode}).",
            'ja' => "受講生 {$studentName} さんがあなたのコース「{$courseName}」を購入しました (注文 {$orderCode})。",
            'zh' => "学生 {$studentName} 刚刚购买了您的课程 \"{$courseName}\" (订单 {$orderCode})。",
        ];

        return [
            'type' => 'teacher.course.sale',
            'title' => $titleTranslations['vi'],
            'title_translations' => $titleTranslations,
            'message' => $messageTranslations['vi'],
            'message_translations' => $messageTranslations,
            'url' => route('teacher.dashboard.index'), // Thay đổi URL nếu có trang thống kê cụ thể
            'severity' => 'success',
            'icon' => 'fas fa-shopping-cart',
            'entity_type' => 'order',
            'entity_id' => $this->order->id,
            'meta' => [
                'course_id' => $this->course->id,
                'order_code' => $orderCode,
                'student_name' => $studentName,
            ],
        ];
    }
}
