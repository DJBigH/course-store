<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Teacher\src\Models\Teacher;

class TeacherCancellationStatusMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Teacher $teacher,
        public string $status,
        public ?string $adminNote,
        public string $localeCode
    ) {
    }

    public function build()
    {
        app()->setLocale($this->localeCode);

        $subject = ($this->status === 'approved') 
            ? '[Hệ thống] Thông báo chấp nhận yêu cầu hủy hợp tác' 
            : '[Hệ thống] Thông báo từ chối yêu cầu hủy hợp tác';

        return $this->subject($subject)
            ->view('emails.teacher-cancellation-status', [
                'teacher' => $this->teacher,
                'status' => $this->status,
                'adminNote' => $this->adminNote,
            ]);
    }
}
