<?php

return [
    'checkout' => [
        'page_title' => 'Order Checkout',
        'page_name' => 'Checkout',

        'notice_complete_payment' => 'Please complete the payment to activate the course',

        // Order info
        'order_info' => 'Order Information',
        'order_code' => 'Order Code',
        'subtotal' => 'Subtotal',
        'discount' => 'Discount',
        'order_time' => 'Order Time',
        'status' => 'Status',
        'total_payment' => 'Total Payment',

        // Course details
        'course_details' => 'Course Details',
        'course' => 'Course',
        'price' => 'Price',
        'instructor' => 'Instructor',
        'course_status' => 'Status',
        'active' => 'Active',
        'inactive' => 'Inactive',

        // Actions
        'back_home' => 'Back to Home',
        'buy_another_course' => 'Buy Another Course',

        // Payment methods
        'choose_payment_method' => 'Choose Payment Method',
        'qr_transfer' => 'QR Bank Transfer',
        'maintenance' => 'Under Maintenance',

        // Bank transfer
        'bank_transfer' => 'Bank Transfer Payment',
        'bank_name' => 'Bank',
        'bank_account' => 'Account Number',
        'bank_account_name' => 'Account Holder',
        'bank_account_name_bank' => 'Nguyen Duy Khanh',
        'amount' => 'Amount',
        'transfer_content' => 'Transfer Content',
        'transfer_note' => 'Order payment',
        'transfer_note_qr' => 'order payment',

        'download_qr' => 'Download QR',

        'after_transfer_notice' => 'After completing the bank transfer, please click',
        'confirm_paid' => '“I have paid”',
        'complete_order_notice' => 'to complete your order.',

        'i_have_paid' => 'I have paid',
        'cancel_order' => 'Cancel order',
        'cancel_confirm' => 'Are you sure you want to cancel this order?',

        // VNPay
        'vnpay_notice' => 'You will be redirected to the VNPay payment gateway to complete the transaction.',
        'pay_with_vnpay' => 'Pay with VNPay',

        // MoMo
        'momo_notice' => 'You will be redirected to the MoMo payment gateway to complete the transaction.',
        'pay_with_momo' => 'Pay with MoMo',
        'momo_not_configured' => 'MoMo sandbox is not configured yet. Please add sandbox credentials to .env.',
        'momo_invalid_amount' => 'The payment amount is invalid.',
        'momo_create_failed' => 'Unable to create the MoMo transaction right now.',
        'momo_invalid_return' => 'Unable to resolve the order from the MoMo response.',
        'momo_payment_success' => 'MoMo payment completed successfully.',
        'momo_payment_failed' => 'MoMo payment failed.',
        'momo_order_info' => 'Payment for order :code',
    ],

    'coupons' => [
        'title' => 'Discount Code',
        'placeholder' => 'Enter discount code...',
        'apply' => 'Apply',
    ],

];
