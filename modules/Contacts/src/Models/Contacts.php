<?php

namespace Modules\Contacts\src\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contacts extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const TYPE_CONTACT = 'contact';
    public const TYPE_FEEDBACK = 'feedback';
    public const TYPE_REPORT = 'report';

    public const STATUS_NEW = 'new';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_NEED_INFO = 'need_info';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_REJECTED = 'rejected';

    protected $table = 'contacts';
    protected $fillable = [
        'name',
        'phone',
        'email',
        'subject',
        'submission_type',
        'category',
        'message',
        'status',
        'workflow_status',
        'source',
        'page_url',
        'student_id',
        'teacher_id',
        'admin_note',
        'deleted_at',
    ];

    public static function submissionTypes(): array
    {
        return [
            self::TYPE_CONTACT,
            self::TYPE_FEEDBACK,
            self::TYPE_REPORT,
        ];
    }

    public static function workflowStatuses(): array
    {
        return [
            self::STATUS_NEW,
            self::STATUS_IN_PROGRESS,
            self::STATUS_NEED_INFO,
            self::STATUS_RESOLVED,
            self::STATUS_REJECTED,
        ];
    }

    public static function categories(): array
    {
        return [
            'general_contact',
            'feature_request',
            'ui_ux',
            'teacher_portal',
            'student_portal',
            'payment_package',
            'system_bug',
            'course_lesson',
            'comment_rating',
            'content_violation',
            'account',
            'other',
        ];
    }

    public function student()
    {
        return $this->belongsTo(\Modules\Students\src\Models\Student::class, 'student_id', 'id');
    }

    public function teacher()
    {
        return $this->belongsTo(\Modules\Teacher\src\Models\Teacher::class, 'teacher_id', 'id');
    }
}
