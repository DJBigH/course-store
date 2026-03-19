<?php

return [
    'create' => [
        'success' => 'Thêm thành công',
        'failure' => 'Thêm không thành công',
    ],

    'update' => [
        'success' => 'Cập nhật thành công',
        'failure' => 'Cập nhật không thành công',
    ],

    'delete' => [
        'success' => 'Xóa thành công',
        'failure' => 'Xóa không thành công',
    ],

    'password' => [
        'update' => [
            'success' => 'Cập nhật mật khẩu thành công',
            'failure' => 'Cập nhật mật khẩu không thành công',
        ],
    ],

    'profile' => [
        'update' => [
            'success' => 'Thông tin của bạn đã được cập nhật thành công.',
            'error' => 'Không thể cập nhật vào lúc này. Vui lòng thử lại.',
            'confirm_email_sent' => 'Chúng tôi đã gửi email xác nhận đến địa chỉ mới. Chỉ sau khi xác nhận thành công, email mới mới được cập nhật.',
            'confirm_email_invalid' => 'Liên kết xác nhận email không hợp lệ hoặc đã hết hạn.',
            'confirm_email_conflict' => 'Email mới này đã được sử dụng bởi tài khoản khác.',
            'confirm_email_success' => 'Email mới đã được xác nhận và thông tin tài khoản của bạn đã được cập nhật.',
        ],
    ],

    'verify_coupons' => [
        'coupon_apply_success' => 'Áp mã giảm giá thành công',
        'coupon_remove_success' => 'Xóa mã giảm giá thành công',
        'coupon_remove_failed' => 'Xóa mã giảm giá không thành công',
        'coupon_required' => 'Mã giảm giá bắt buộc phải nhập',
        'coupon_exp' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn',
    ],
];
