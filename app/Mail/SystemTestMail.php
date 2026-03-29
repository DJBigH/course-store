<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SystemTestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName,
        public string $sentAt
    ) {}

    public function build(): self
    {
        return $this
            ->subject('Email kiểm tra từ cài đặt hệ thống')
            ->html(
                '<div style="font-family:Arial,sans-serif;line-height:1.6">'
                    . '<h2>Email kiểm tra</h2>'
                    . '<p>Xin chào ' . e($this->recipientName) . ',</p>'
                    . '<p>Đây là email kiểm tra được gửi từ trang cài đặt quản trị.</p>'
                    . '<p>Thời gian gửi: <strong>' . e($this->sentAt) . '</strong></p>'
                    . '<p>Nếu bạn nhận được email này, cấu hình email đang hoạt động bình thường.</p>'
                    . '</div>'
            );
    }
}
