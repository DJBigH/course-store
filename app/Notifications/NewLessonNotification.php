<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

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
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $locale = method_exists($notifiable, 'preferredLocale')
            ? $notifiable->preferredLocale()
            : app()->getLocale();

        $titleTranslations = [
            'vi' => 'Bài giảng mới',
            'en' => 'New lesson',
            'ko' => '새 강의',
            'ja' => '新しいレッスン',
            'zh' => '新课程内容',
        ];

        $courseNames = [
            'vi' => localizedModelField($this->course, 'name', 'vi'),
            'en' => localizedModelField($this->course, 'name', 'en'),
            'ko' => localizedModelField($this->course, 'name', 'ko'),
            'ja' => localizedModelField($this->course, 'name', 'ja'),
            'zh' => localizedModelField($this->course, 'name', 'zh'),
        ];

        $lessonNames = [
            'vi' => localizedModelField($this->lesson, 'name', 'vi'),
            'en' => localizedModelField($this->lesson, 'name', 'en'),
            'ko' => localizedModelField($this->lesson, 'name', 'ko'),
            'ja' => localizedModelField($this->lesson, 'name', 'ja'),
            'zh' => localizedModelField($this->lesson, 'name', 'zh'),
        ];

        $messageTranslations = [
            'vi' => "Khóa học {$courseNames['vi']} vừa có bài giảng mới: {$lessonNames['vi']}",
            'en' => "Course {$courseNames['en']} has a new lesson: {$lessonNames['en']}",
            'ko' => "{$courseNames['ko']} 강의에 새 레슨이 추가되었습니다: {$lessonNames['ko']}",
            'ja' => "コース {$courseNames['ja']} に新しいレッスンが追加されました: {$lessonNames['ja']}",
            'zh' => "课程 {$courseNames['zh']} 新增了课程内容：{$lessonNames['zh']}",
        ];

        return [
            'title' => $titleTranslations['vi'],
            'title_translations' => $titleTranslations,
            'message' => $messageTranslations['vi'],
            'message_translations' => $messageTranslations,
            'lesson_id' => $this->lesson->id,
            'course_id' => $this->course->id,
            'url' => route('courses.home', ['locale' => $locale]),
        ];
    }
}
