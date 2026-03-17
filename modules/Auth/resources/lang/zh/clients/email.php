<?php

return [
    'common' => [
        'system_notice' => '系统通知',
        'security_notice' => '安全通知',
        'automated_email' => '系统自动邮件',
        'all_rights_reserved' => '保留所有权利。',
    ],
    'verify' => [
        'subject' => '请验证您的邮箱地址',
        'greeting' => ':name，您好：',
        'intro' => '请先验证您的邮箱地址，以激活并继续使用学员账户。',
        'action' => '验证邮箱',
        'outro' => '如果这不是您创建的账户，您可以忽略此邮件。',
        'resend' => [
            'success' => '激活邮件已发送到您的收件箱。',
        ],
    ],
    'reset_password' => [
        'subject' => '密码重置请求',
        'greeting' => '您好：',
        'line' => '我们收到了您的账户密码重置请求。',
        'action' => '重置密码',
        'expire_notice' => '此重置链接将在 :count 分钟后失效。',
        'outro' => '如果这不是您本人发起的请求，您可以忽略此邮件。',
    ],
    'password_changed' => [
        'subject' => '您的账户密码已更改',
        'greeting' => '您好：',
        'line_1' => '您的账户密码刚刚已成功更改。',
        'line_2' => '如果这是您本人操作的，则无需执行其他操作。',
        'action' => '重新登录',
        'line_3' => '如果这不是您本人操作，请立即联系支持团队，并尽快修改密码以保护账户安全。',
    ],
];
