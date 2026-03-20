<?php

return [
    'common' => [
        'system_notice' => '시스템 알림',
        'security_notice' => '보안 알림',
        'automated_email' => '자동 발송 이메일',
        'all_rights_reserved' => '모든 권리 보유.',
    ],
    'verify' => [
        'subject' => '이메일 주소를 인증해 주세요',
        'greeting' => '안녕하세요 :name님,',
        'intro' => '학생 계정을 활성화하고 계속 이용하려면 이메일 주소를 인증해 주세요.',
        'action' => '이메일 인증',
        'outro' => '이 계정을 직접 만든 것이 아니라면 이 이메일은 무시하셔도 됩니다.',
        'resend' => [
            'success' => '활성화 이메일이 받은편지함으로 전송되었습니다.',
        ],
    ],
    'reset_password' => [
        'subject' => '비밀번호 재설정 요청',
        'greeting' => '안녕하세요,',
        'line' => '회원님의 계정에 대한 비밀번호 재설정 요청이 접수되었습니다.',
        'action' => '비밀번호 재설정',
        'expire_notice' => '이 링크는 :count분 후 만료됩니다.',
        'outro' => '직접 요청하지 않았다면 이 이메일을 무시하셔도 됩니다.',
    ],
    'password_changed' => [
        'subject' => '계정 비밀번호가 변경되었습니다',
        'greeting' => '안녕하세요,',
        'line_1' => '회원님의 계정 비밀번호가 방금 성공적으로 변경되었습니다.',
        'line_2' => '직접 변경한 경우 추가로 하실 작업은 없습니다.',
        'action' => '다시 로그인',
        'line_3' => '직접 변경하지 않았다면 즉시 고객지원에 문의하고 비밀번호를 다시 변경해 계정을 보호해 주세요.',
    ],
];
