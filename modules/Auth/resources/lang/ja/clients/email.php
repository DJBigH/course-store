<?php

return [
    'common' => [
        'system_notice' => 'システム通知',
        'security_notice' => 'セキュリティ通知',
        'automated_email' => '自動送信メール',
        'all_rights_reserved' => 'All rights reserved.',
    ],
    'verify' => [
        'subject' => 'メールアドレスを確認してください',
        'greeting' => ':name 様',
        'intro' => '受講アカウントを有効化して利用を続けるには、メールアドレスを確認してください。',
        'action' => 'メールを確認する',
        'outro' => 'このアカウントを作成していない場合は、このメールを無視してください。',
        'resend' => [
            'success' => '認証メールを受信箱に送信しました。',
        ],
    ],
    'reset_password' => [
        'subject' => 'パスワード再設定のご案内',
        'greeting' => 'こんにちは、',
        'line' => 'アカウントのパスワード再設定リクエストを受け付けました。',
        'action' => 'パスワードを再設定する',
        'expire_notice' => 'このリンクの有効期限は :count 分です。',
        'outro' => 'このリクエストに心当たりがない場合は、このメールを無視してください。',
    ],
    'password_changed' => [
        'subject' => 'アカウントのパスワードが変更されました',
        'greeting' => 'こんにちは、',
        'line_1' => 'アカウントのパスワードが正常に変更されました。',
        'line_2' => 'ご自身で変更した場合は、追加の操作は不要です。',
        'action' => '再ログイン',
        'line_3' => '心当たりがない場合は、すぐにサポートへ連絡し、アカウント保護のためにパスワードを変更してください。',
    ],
];
