<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class NewLessonNotification extends Notification
{
    use Queueable;

    protected $lesson;
    protected $course;

    public function __construct($lesson, $course)
    {
        $this->lesson = $lesson;
        $this->course = $course;
    }

    public function via($notifiable)
    {
        return ['database']; // lưu DB để đổ chuông
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => '📢 Bài giảng mới',
            'message' => "Khóa học {$this->course->name} vừa có bài giảng mới: {$this->lesson->name}",
            'lesson_id' => $this->lesson->id,
            'course_id' => $this->course->id,
            'url' => route('courses.home'),
        ];
    }
}
