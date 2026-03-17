<?php

return [
    'checkout' => [
        'page_title' => 'Thanh toán đơn hàng',
        'page_name' => 'Thanh toán',

        'notice_complete_payment' => 'Vui lòng hoàn tất thanh toán để kích hoạt khóa học',

        // Order info
        'order_info' => 'Thông tin đơn hàng',
        'order_code' => 'Mã đơn hàng',
        'subtotal' => 'Tạm tính',
        'discount' => 'Giảm giá',
        'order_time' => 'Thời gian đặt',
        'status' => 'Trạng thái',
        'total_payment' => 'Tổng thanh toán',

        // Course details
        'course_details' => 'Chi tiết khóa học',
        'course' => 'Khóa học',
        'price' => 'Giá',
        'instructor' => 'Giảng viên',
        'course_status' => 'Trạng thái',
        'active' => 'Đang hoạt động',
        'inactive' => 'Dừng',

        // Actions
        'back_home' => 'Quay lại trang chủ',
        'buy_another_course' => 'Mua khóa học khác',

        // Payment methods
        'choose_payment_method' => 'Chọn hình thức thanh toán',
        'qr_transfer' => 'Chuyển khoản QR',
        'maintenance' => 'Bảo trì',

        // Bank transfer
        'bank_transfer' => 'Thanh toán chuyển khoản',
        'bank_name' => 'Ngân hàng',
        'bank_account' => 'STK',
        'bank_account_name' => 'Chủ TK',
        'bank_account_name_bank' => 'Nguyễn Duy Khánh',
        'amount' => 'Số tiền',
        'transfer_content' => 'Nội dung',
        'transfer_note' => 'Thanh toán đơn',
        'transfer_note_qr' => 'thanh toan don',

        'download_qr' => 'Tải QR',

        'after_transfer_notice' => 'Sau khi chuyển khoản thành công, vui lòng nhấn',
        'confirm_paid' => '“Tôi đã thanh toán”',
        'complete_order_notice' => 'để hoàn tất đơn hàng.',

        'i_have_paid' => 'Tôi đã thanh toán',
        'cancel_order' => 'Hủy đơn hàng',
        'cancel_confirm' => 'Bạn có chắc chắn muốn hủy đơn hàng này không?',

        // VNPay
        'vnpay_notice' => 'Bạn sẽ được chuyển đến cổng thanh toán VNPay để hoàn tất giao dịch.',
        'pay_with_vnpay' => 'Thanh toán bằng VNPay',

        // MoMo
        'momo_notice' => 'Bạn sẽ được chuyển đến cổng thanh toán MoMo để hoàn tất giao dịch.',
        'pay_with_momo' => 'Thanh toán bằng MoMo',
        'momo_not_configured' => 'MoMo sandbox chưa được cấu hình. Vui lòng thêm key sandbox vào .env.',
        'momo_invalid_amount' => 'Số tiền thanh toán không hợp lệ.',
        'momo_create_failed' => 'Không thể khởi tạo giao dịch MoMo lúc này.',
        'momo_invalid_return' => 'Không xác định được đơn hàng từ MoMo.',
        'momo_payment_success' => 'Thanh toán MoMo thành công.',
        'momo_payment_failed' => 'Thanh toán MoMo không thành công.',
        'momo_order_info' => 'Thanh toán đơn hàng :code',
    ],

    'coupons' => [
        'title' => 'Mã giảm giá',
        'placeholder' => 'Nhập mã giảm giá...',
        'apply' => 'Áp dụng',
    ],

];
