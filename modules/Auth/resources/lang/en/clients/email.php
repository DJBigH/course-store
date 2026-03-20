<?php

return [
    'common' => [
        'system_notice' => 'System notice',
        'security_notice' => 'Security notice',
        'automated_email' => 'Automated email',
        'all_rights_reserved' => 'All rights reserved.',
    ],
    'verify' => [
        'subject' => 'Verify your email address',
        'greeting' => 'Hello :name,',
        'intro' => 'Please verify your email address to activate and continue using your student account.',
        'action' => 'Verify email',
        'outro' => 'If you did not create this account, you can safely ignore this email.',
        'resend' => [
            'success' => 'The activation email has been sent to your inbox.',
        ],
    ],
    'reset_password' => [
        'subject' => 'Password reset request',
        'greeting' => 'Hello,',
        'line' => 'We received a request to reset the password for your account.',
        'action' => 'Reset password',
        'expire_notice' => 'This reset link will expire in :count minutes.',
        'outro' => 'If you did not request a password reset, no further action is required.',
    ],
    'password_changed' => [
        'subject' => 'Your account password has been changed',
        'greeting' => 'Hello,',
        'line_1' => 'Your account password has just been changed successfully.',
        'line_2' => 'If you made this change, you do not need to do anything else.',
        'action' => 'Sign in again',
        'line_3' => 'If you did not make this change, please contact support immediately and update your password to secure the account.',
    ],
];
