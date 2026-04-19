<?php

return [
    'titles' => [
        'earnings' => 'Đối soát doanh thu giảng viên',
        'payouts' => 'Xử lý rút tiền giảng viên',
    ],
    'messages' => [
        'payout_update_success' => 'Cập nhật trạng thái rút tiền thành công.',
        'payout_change_processed' => 'Yêu cầu thay đổi tài khoản này đã được xử lý trước đó.',
        'payout_account_change_success' => 'Xử lý yêu cầu thay đổi tài khoản thành công.',
    ],
    'table' => [
        'id' => 'ID',
        'teacher' => 'Giảng viên',
        'email' => 'Email',
        'amount' => 'Số tiền',
        'bank_info' => 'Thông tin NH',
        'status' => 'Trạng thái',
        'note' => 'Ghi chú',
        'actions' => 'Cập nhật',
        'replace_account' => 'Tài khoản sẽ thay',
        'new_account' => 'Tài khoản mới',
        'submitted_at' => 'Ngày gửi',
        'processed_at' => 'Ngày xử lý',
    ],
    'summary' => [
        'gross' => 'Doanh thu gộp',
        'discount' => 'Discount phân bổ',
        'net' => 'Thực thu sau discount',
        'teacher_revenue' => 'Giảng viên được nhận',
        'requested' => 'Đang chờ xử lý',
        'processing' => 'Đang processing',
        'paid' => 'Đã chi trả',
        'account_change_pending' => 'Chờ đổi tài khoản',
    ],
    'filters' => [
        'teacher' => 'Giảng viên',
        'all_teachers' => 'Tất cả giảng viên',
        'from_date' => 'Từ ngày',
        'to_date' => 'Đến ngày',
        'filter_button' => 'Lọc',
        'all' => 'Tất cả',
        'payout_status' => 'Trạng thái payout',
        'account_change_status' => 'Trạng thái đổi tài khoản',
    ],
    'placeholders' => [
        'admin_note' => 'Ghi chú xử lý...',
    ],
];
