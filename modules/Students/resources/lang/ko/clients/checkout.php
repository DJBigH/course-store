<?php

return [
    'checkout' => [
        'page_title' => '주문 결제',
        'page_name' => '결제',

        'notice_complete_payment' => '강의를 활성화하려면 결제를 완료해 주세요',

        // Order info
        'order_info' => '주문 정보',
        'order_code' => '주문 코드',
        'subtotal' => '소계',
        'discount' => '할인',
        'order_time' => '주문 시간',
        'status' => '상태',
        'total_payment' => '총 결제 금액',

        // Course details
        'course_details' => '강의 상세',
        'course' => '강의',
        'price' => '가격',
        'instructor' => '강사',
        'course_status' => '상태',
        'active' => '활성',
        'inactive' => '비활성',

        // Actions
        'back_home' => '홈으로 돌아가기',
        'buy_another_course' => '다른 강의 구매',

        // Payment methods
        'choose_payment_method' => '결제 수단 선택',
        
        'payment_under_maintenance' => ':gateway is currently under maintenance.',

        // Bank transfer
        'bank_transfer' => '계좌이체 결제',
        'bank_name' => '은행',
        'bank_account' => '계좌번호',
        'bank_account_name' => '예금주',
        'bank_account_name_bank' => 'Nguyen Duy Khanh',
        'amount' => '금액',
        'transfer_content' => '이체 내용',
        'transfer_note' => '주문 결제',
        'transfer_note_qr' => '주문 결제',

        'download_qr' => 'QR 다운로드',

        'after_transfer_notice' => '계좌이체를 완료한 후',
        'confirm_paid' => '“결제 완료”',
        'complete_order_notice' => '버튼을 눌러 주문을 완료해 주세요.',

        'i_have_paid' => '결제 완료',
        'cancel_order' => '주문 취소',
        'cancel_confirm' => '이 주문을 취소하시겠습니까?',

        // VNPay
        'vnpay_notice' => '결제를 완료하기 위해 VNPay 결제 페이지로 이동합니다.',
        'pay_with_vnpay' => 'VNPay로 결제',

        // MoMo
        'momo_notice' => '결제를 완료하기 위해 MoMo 결제 페이지로 이동합니다.',
        'pay_with_momo' => 'MoMo로 결제',
        'momo_not_configured' => 'MoMo sandbox가 아직 설정되지 않았습니다. .env에 sandbox 자격 증명을 추가해 주세요.',
        'momo_invalid_amount' => '결제 금액이 올바르지 않습니다.',
        'momo_create_failed' => '현재 MoMo 거래를 생성할 수 없습니다.',
        'momo_invalid_return' => 'MoMo 응답에서 주문을 확인할 수 없습니다.',
        'momo_payment_success' => 'MoMo 결제가 완료되었습니다.',
        'momo_payment_failed' => 'MoMo 결제에 실패했습니다.',
        'momo_order_info' => '주문 :code 결제',
    ],

    'coupons' => [
        'title' => '할인 코드',
        'placeholder' => '할인 코드를 입력하세요...',
        'apply' => '적용',
    ],

];

