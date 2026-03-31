<?php

return [
    'checkout' => [
        'page_title' => '注文のお支払い',
        'page_name' => 'チェックアウト',

        'notice_complete_payment' => 'コースを有効化するには、お支払いを完了してください',

        // Order info
        'order_info' => '注文情報',
        'order_code' => '注文コード',
        'subtotal' => '小計',
        'discount' => '割引',
        'order_time' => '注文時間',
        'status' => 'ステータス',
        'total_payment' => '支払合計',

        // Course details
        'course_details' => 'コース詳細',
        'course' => 'コース',
        'price' => '価格',
        'instructor' => '講師',
        'course_status' => 'ステータス',
        'active' => '有効',
        'inactive' => '無効',

        // Actions
        'back_home' => 'ホームへ戻る',
        'buy_another_course' => '他のコースを購入',

        // Payment methods
        'choose_payment_method' => '支払い方法を選択',
        'qr_transfer' => 'QR 銀行振込',
        'maintenance' => 'メンテナンス中',
        'payment_under_maintenance' => ':gateway は現在メンテナンス中です。',

        // Bank transfer
        'bank_transfer' => '銀行振込',
        'bank_name' => '銀行名',
        'bank_account' => '口座番号',
        'bank_account_name' => '口座名義',
        'bank_account_name_bank' => 'Nguyen Duy Khanh',
        'amount' => '金額',
        'transfer_content' => '振込内容',
        'transfer_note' => '注文の支払い',
        'transfer_note_qr' => '注文支払い',

        'download_qr' => 'QR をダウンロード',

        'after_transfer_notice' => '銀行振込が完了したら、',
        'confirm_paid' => '「支払い済み」',
        'complete_order_notice' => 'をクリックして注文を完了してください。',

        'i_have_paid' => '支払い済み',
        'cancel_order' => '注文をキャンセル',
        'cancel_confirm' => 'この注文を本当にキャンセルしますか？',

        // VNPay
        'vnpay_notice' => 'VNPay 決済ページへ移動して、支払いを完了します。',
        'pay_with_vnpay' => 'VNPay で支払う',

        // MoMo
        'momo_notice' => 'MoMo 決済ページへ移動して、支払いを完了します。',
        'pay_with_momo' => 'MoMo で支払う',
        'momo_not_configured' => 'MoMo sandbox はまだ設定されていません。.env に sandbox 認証情報を追加してください。',
        'momo_invalid_amount' => '支払い金額が無効です。',
        'momo_create_failed' => '現在、MoMo 取引を作成できません。',
        'momo_invalid_return' => 'MoMo の応答から注文を特定できません。',
        'momo_payment_success' => 'MoMo の支払いが完了しました。',
        'momo_payment_failed' => 'MoMo の支払いに失敗しました。',
        'momo_order_info' => '注文 :code の支払い',
    ],

    'coupons' => [
        'title' => '割引コード',
        'placeholder' => '割引コードを入力...',
        'apply' => '適用',
    ],

];

