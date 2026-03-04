<?php

return [
    //Menu
    'menu' => [
        'dashbroad' => 'ダッシュボード',
        'profile' => '個人情報',
        'my_course' => 'マイコース',
        'coupons' => 'クーポン',
        'order' => '注文',
        'change_password' => 'パスワード変更',
        'logout' => 'ログアウト',
    ],
    //Core (shared)
    'core' => [
        'status' => 'ステータス',
        'action' => '操作',
        'filter' => '絞り込み',
        'reset' => 'リセット',
        'all' => 'すべて',
        'all_status' => 'すべてのステータス',
        'search' => '検索',
        'from_date' => '開始日',
        'to_date' => '終了日',
        'total' => '合計金額',
        'time' => '時間',
        'back' => '戻る',
        'discount' => '割引',
        'remaining' => '残り',
        'uses' => '回',
        'days' => '日',
        'expired' => '期限切れ',
        'no_time_limit' => '無期限',
        'no_use_limit' => '利用回数無制限',
        'no_data' => 'データなし',
        'active' => '有効',
        'inactive' => '無効',
        'instructor' => '講師',
        'course' => 'コース',
        'order' => '注文',
        'coupon' => 'クーポン',
        'price' => '価格',
    ],

    //Account (Dashboard)
    'account' => [
        'title' => 'アカウント概要',
        'welcome' => 'おかえりなさい。受講者アカウントの概要はこちらです。',
        'courses' => 'コース',
        'courses_unit' => '件',
        'coupons' => 'クーポン',
        'coupons_unit' => '枚',
        'orders' => '注文',
        'orders_unit' => '件',
    ],

    //Profile
    'profile' => [
        'title' => '個人情報',
        'edit' => '情報を編集',
        'cancel' => 'キャンセル',
        'full_name' => '氏名',
        'email' => 'メールアドレス',
        'phone' => '電話番号',
        'address' => '住所',
        'status' => 'ステータス',
        'registered_at' => '登録日時',
        'activated_at' => '有効化日時',
        'update_title' => '個人情報を更新',

        'placeholder_full_name' => '氏名を入力...',
        'placeholder_email' => 'メールアドレスを入力...',
        'placeholder_phone' => '電話番号を入力...',
        'placeholder_address' => '住所を入力...',

        'save' => '変更を保存',
        'note_reload' => '* 更新後は再読み込み、または F5 を押してください',
    ],

    //My Course
    'my_course' => [
        'title' => 'マイコース',
        'instructor' => '講師',
        'all_instructors' => 'すべての講師',
        'search_course' => 'コースを検索',
        'placeholder_course_name' => 'コース名を入力...',
        'course_name' => 'コース名',
        'status' => 'ステータス',
        'action' => '操作',
        'updated_at' => '最終更新',
        'active' => '有効',
        'stop_update' => '更新停止',
        'enter_course' => '学習を始める',
        'empty' => 'まだ受講中のコースはありません',
    ],

    //Coupons
    'coupons' => [
        'title' => 'マイクーポン',
        'discount' => '割引',
        'remaining' => '残り',
        'uses' => '回',
        'no_use_limit' => '利用回数無制限',
        'exp' => '有効',
        'days' => '日',
        'expired' => '期限切れ',
        'no_time_limit' => '無期限',
        'no_remaining_uses' => '残り回数なし',
        'available' => '利用可能',
        'issued_at' => '発行日:',
        'empty' => 'クーポンはまだありません',
    ],

    //Order
    'order' => [
        'title' => '注文',
        'status' => 'ステータス',
        'all_status' => 'すべてのステータス',
        'no_data' => 'データなし',
        'order_code' => '注文コード',
        'placeholder_order_code' => '注文コードを入力...',
        'from_date' => '開始日',
        'to_date' => '終了日',
        'total' => '合計金額',
        'placeholder_total' => '合計金額を入力...',
        'reset' => 'リセット',
        'filter' => '絞り込み',

        'table_order_code' => '注文コード',
        'table_total' => '合計金額',
        'table_status' => 'ステータス',
        'table_time' => '時間',
        'table_action' => '操作',

        'empty' => 'まだ注文はありません',
    ],

    //Order Details
    'order_detail' => [
        'title' => '注文詳細',
        'code' => '注文ID',
        'order_info' => '注文情報',
        'order_code' => '注文コード',
        'subtotal' => '小計',
        'coupon_discount' => 'クーポン割引',
        'total_payment' => '支払合計',
        'ordered_at' => '注文日時',
        'status' => 'ステータス',
        'payment_expired_at' => '支払期限',
        'pay' => '今すぐ支払う',

        'coupon_applied' => '適用されたクーポン',
        'discount' => '割引',
        'for_order' => 'この注文に適用',

        'course_info' => 'コース情報',
        'course_name' => 'コース名',
        'price' => '価格',
        'instructor' => '講師',
        'course_status' => 'ステータス',
        'active' => '有効',
        'inactive' => '無効',

        'no_detail' => '詳細データはありません',
        'back' => '戻る',
        'download_invoice' => '請求書をダウンロード',
    ],

    'change_password' => [
        'title' => 'パスワード変更',
        'error' => '入力内容を確認してください',
        'old_password' => '現在のパスワード',
        'old_password_placeholder' => '現在のパスワードを入力...',
        'new_password' => '新しいパスワード',
        'new_password_placeholder' => '新しいパスワードを入力...',
        'confirm_password' => '新しいパスワードの確認',
        'confirm_password_placeholder' => '新しいパスワードを再入力...',
        'submit' => 'パスワードを変更',
    ],

    'logout' => [
        'confirm_logout' => '本当にログアウトしますか？',
    ],
];
